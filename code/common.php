<?php
/**
 * Common functions for Laundry app
 */
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'laundry_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Fungsi koneksi database
function get_db() {
    static $db = null;
    if ($db === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $db = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $db;
}

// Role-based access control
function require_customer($db = null) {
    $db = $db ?: get_db();
    $user = currentUser($db);
    if (!$user || $user['role'] !== 'customer') {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function require_worker($db = null) {
    $db = $db ?: get_db();
    $user = currentUser($db);
    if (!$user || $user['role'] !== 'worker') {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function require_supervisor($db = null) {
    $db = $db ?: get_db();
    $user = currentUser($db);
    if (!$user || $user['role'] !== 'supervisor') {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function require_admin($db = null) {
    $db = $db ?: get_db();
    $user = currentUser($db);
    if (!$user || $user['role'] !== 'admin') {
        header('Location: login.php');
        exit;
    }
    return $user;
}

// User functions
function currentUser($db = null) {
    if (!$db) $db = get_db();
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $db->prepare('SELECT * FROM user WHERE id_user = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function loginUser($email, $password) {
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM user WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($user && $password === $user['password']) ? $user : false;
}

function logout_user() {
    session_destroy();
    header('Location: login.php');
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Order functions
function update_order_status($order_id, $new_status, $db = null) {
    $db = $db ?: get_db();
    $stmt = $db->prepare('UPDATE orders SET status_order = ? WHERE id_order = ?');
    return $stmt->execute([$new_status, $order_id]);
}

function get_all_orders($db = null) {
    $db = $db ?: get_db();
    $stmt = $db->prepare('SELECT o.*, u.nama as customer_name, u.no_hp, u.alamat, l.nama_layanan as service_name 
        FROM orders o 
        LEFT JOIN user u ON o.id_user = u.id_user 
        LEFT JOIN layanan l ON o.id_layanan = l.id_layanan 
        ORDER BY o.tanggal_order DESC');
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_orders($db = null, $user_id = null) {
    $db = $db ?: get_db();
    if ($user_id) {
        $stmt = $db->prepare('SELECT o.*, u.nama as customer_name, l.nama_layanan as service_name 
            FROM orders o 
            LEFT JOIN user u ON o.id_user = u.id_user 
            LEFT JOIN layanan l ON o.id_layanan = l.id_layanan 
            WHERE o.id_user = ? 
            ORDER BY o.tanggal_order DESC');
        $stmt->execute([$user_id]);
    } else {
        $stmt = $db->prepare('SELECT o.*, u.nama as customer_name, l.nama_layanan as service_name 
            FROM orders o 
            LEFT JOIN user u ON o.id_user = u.id_user 
            LEFT JOIN layanan l ON o.id_layanan = l.id_layanan 
            WHERE o.status_order = "proses" 
            ORDER BY o.tanggal_order DESC');
        $stmt->execute();
    }
    return $stmt->fetchAll();
}

function get_order($id, $db = null) {
    $db = $db ?: get_db();
    $stmt = $db->prepare('SELECT o.*, u.nama as customer_name, u.alamat, u.no_hp, l.nama_layanan as service_name, l.harga_per_kg
        FROM orders o 
        LEFT JOIN user u ON o.id_user = u.id_user 
        LEFT JOIN layanan l ON o.id_layanan = l.id_layanan 
        WHERE o.id_order = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function create_order($db = null) {
    $db = $db ?: get_db();
    $current = get_current_order();
    $user = currentUser($db);
    
    if (empty($current) || !$user) return false;
    
    $stmt = $db->prepare('INSERT INTO orders (id_user, id_layanan, tanggal_order, status_order, harga_snapshot, berat_cucian, catatan) 
        VALUES (?, ?, ?, ?, ?, ?, ?)');
    
    $id_layanan = $current['id_layanan'] ?? 1;
    $hargaSnapshot = $current['total_price'] ?? 50000;
    $beratCucian = $current['weight'] ?? 1;
    $catatan = $current['notes'] ?? '';
    
    $success = $stmt->execute([
        $user['id_user'],
        $id_layanan,
        date('Y-m-d'),
        'pending',
        $hargaSnapshot,
        $beratCucian,
        $catatan
    ]);
    
    if ($success) {
        $orderId = $db->lastInsertId();
        clear_current_order();
        return $orderId;
    }
    return false;
}

function get_pending_orders($db = null) {
    $db = $db ?: get_db();
    $stmt = $db->prepare('SELECT o.*, u.nama as customer_name, l.nama_layanan as service_name 
        FROM orders o 
        LEFT JOIN user u ON o.id_user = u.id_user 
        LEFT JOIN layanan l ON o.id_layanan = l.id_layanan 
        WHERE o.status_order = "pending" 
        ORDER BY o.tanggal_order DESC');
    $stmt->execute();
    return $stmt->fetchAll();
}

function verify_payment($order_id, $db = null) {
    $db = $db ?: get_db();
    $stmt = $db->prepare('UPDATE orders SET status_order = "proses" WHERE id_order = ?');
    return $stmt->execute([$order_id]);
}

// Session order functions
function set_current_order($data) {
    $_SESSION['current_order'] = array_merge($_SESSION['current_order'] ?? [], $data);
}

function get_current_order() {
    return $_SESSION['current_order'] ?? [];
}

function clear_current_order() {
    unset($_SESSION['current_order']);
}

// Service functions
function get_services($db = null) {
    $db = $db ?: get_db();
    $stmt = $db->query('SELECT * FROM layanan');
    return $stmt->fetchAll();
}

function get_service($id, $db = null) {
    $db = $db ?: get_db();
    $stmt = $db->prepare('SELECT * FROM layanan WHERE id_layanan = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Flash message functions
function set_flash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function get_flash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

// Count functions
function get_user_count($db = null) {
    $db = $db ?: get_db();
    $stmt = $db->query('SELECT COUNT(*) FROM user');
    return $stmt->fetchColumn();
}

function get_order_count($db = null) {
    $db = $db ?: get_db();
    $stmt = $db->query('SELECT COUNT(*) FROM orders');
    return $stmt->fetchColumn();
}

function get_completed_orders_count($db = null) {
    $db = $db ?: get_db();
    $stmt = $db->query('SELECT COUNT(*) FROM orders WHERE status_order = "selesai"');
    return $stmt->fetchColumn();
}

function get_pending_orders_count($db = null) {
    $db = $db ?: get_db();
    $stmt = $db->query('SELECT COUNT(*) FROM orders WHERE status_order = "pending"');
    return $stmt->fetchColumn();
}

function get_processed_orders_count($db = null) {
    $db = $db ?: get_db();
    $stmt = $db->query('SELECT COUNT(*) FROM orders WHERE status_order = "proses"');
    return $stmt->fetchColumn();
}

// UI Functions
// UI Functions  
function global_header() {
    echo '
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    ';
}

function global_route_script() {
    echo '
<script>
(function() {
  "use strict";
  
  // Dark mode toggle
  function initDarkMode() {
    const html = document.documentElement;
    const toggleBtns = document.querySelectorAll("#themeToggle");
    const isDark = localStorage.theme === "dark" || (!localStorage.theme && window.matchMedia("(prefers-color-scheme: dark)").matches);
    
    if (isDark) {
      html.classList.add("dark");
    }
    
    toggleBtns.forEach(btn => {
      btn.innerHTML = isDark ? \'<span class="material-symbols-outlined">light_mode</span> Light\' : \'<span class="material-symbols-outlined">dark_mode</span> Dark\';
      btn.onclick = function() {
        html.classList.toggle("dark");
        localStorage.theme = html.classList.contains("dark") ? "dark" : "light";
        document.querySelectorAll("#themeToggle").forEach(b => {
          b.innerHTML = localStorage.theme === "dark" ? \'<span class="material-symbols-outlined">light_mode</span> Light\' : \'<span class="material-symbols-outlined">dark_mode</span> Dark\';
        });
      };
    });
  }
  
  // Mobile menu
  function initMobileMenu() {
    const menuBtn = document.getElementById("mobileMenuBtn");
    const mobileMenu = document.getElementById("mobileMenu");
    const overlay = document.getElementById("mobileMenuOverlay");
    
    if (menuBtn && mobileMenu && overlay) {
      menuBtn.addEventListener("click", () => {
        mobileMenu.classList.add("active");
        overlay.classList.add("active");
        document.body.style.overflow = "hidden";
      });
      
      const closeMenu = () => {
        mobileMenu.classList.remove("active");
        overlay.classList.remove("active");
        document.body.style.overflow = "";
      };
      
      overlay.addEventListener("click", closeMenu);
      document.querySelectorAll("#mobileMenu .close-btn, #mobileMenu a").forEach(el => {
        el.addEventListener("click", closeMenu);
      });
    }
  }
  
  // Particles background
  function initParticles() {
    if (document.getElementById("particles-js")) {
      particlesJS("particles-js", {
        particles: {
          number: { value: 80, density: { enable: true, value_area: 800 } },
          color: { value: "#6366f1" },
          shape: { type: "circle" },
          opacity: { value: 0.5, random: false },
          size: { value: 3, random: true },
          line_linked: { enable: true, distance: 150, color: "#6366f1", opacity: 0.4, width: 1 },
          move: { enable: true, speed: 2, direction: "none", random: false, straight: false, out_mode: "out" }
        },
        interactivity: {
          detect_on: "canvas",
          events: { onhover: { enable: true, mode: "repulse" }, onclick: { enable: true, mode: "push" } }
        }
      });
    }
  }
  
  // Animate on scroll
  function initScrollAnimation() {
    const observerOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = "1";
          entry.target.style.transform = "translateY(0)";
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);
    
    document.querySelectorAll(".animate-on-scroll").forEach(el => {
      el.style.opacity = "0";
      el.style.transform = "translateY(30px)";
      el.style.transition = "all 0.6s ease";
      observer.observe(el);
    });
  }
  
  // Toast notification
  function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.className = `toast bg-${type === "success" ? "emerald" : type === "error" ? "red" : "blue"}-500 text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-2`;
    toast.innerHTML = `<span class="material-symbols-outlined">${type === "success" ? "check_circle" : "error"}</span> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  }
  
  // Form validation
  function initFormValidation() {
    document.querySelectorAll(".validate-form").forEach(form => {
      form.addEventListener("submit", (e) => {
        let isValid = true;
        form.querySelectorAll("[required]").forEach(field => {
          if (!field.value.trim()) {
            field.classList.add("border-red-500");
            isValid = false;
          } else {
            field.classList.remove("border-red-500");
          }
        });
        if (!isValid) {
          e.preventDefault();
          showToast("Please fill all required fields", "error");
        }
      });
    });
  }
  
  // Initialize all
  document.addEventListener("DOMContentLoaded", () => {
    initDarkMode();
    initMobileMenu();
    initParticles();
    initScrollAnimation();
    initFormValidation();
    
    // Add animation classes
    document.querySelectorAll(".card, .stat-card, .service-card").forEach((el, i) => {
      el.classList.add("animate-on-scroll");
      el.style.animationDelay = `${i * 0.1}s`;
    });
  });
})();
</script>

<style>
  /* Add this to your style.css or keep here for fallback */
  .animate-on-scroll {
    transition: all 0.6s ease !important;
  }
  
  .card, .stat-card, .service-card {
    transition: all 0.3s ease;
  }
  
  .card:hover, .stat-card:hover, .service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.15);
  }
  
  /* Mobile responsive fixes */
  @media (max-width: 640px) {
    .container-responsive {
      padding: 0 0.75rem;
    }
    
    h1 {
      font-size: 1.5rem;
    }
    
    .card-modern {
      border-radius: 1rem;
    }
  }
  
  /* Loading state */
  .loading {
    position: relative;
    pointer-events: none;
    opacity: 0.6;
  }
  
  .loading::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 24px;
    height: 24px;
    margin: -12px 0 0 -12px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: rotate 1s linear infinite;
  }
</style>';
}

?>