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
        // Simpan order_id ke session untuk digunakan di payment
        $_SESSION['last_order_id'] = $orderId;
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

function set_current_order($data) {
    $_SESSION['current_order'] = array_merge($_SESSION['current_order'] ?? [], $data);
}

function get_current_order() {
    return $_SESSION['current_order'] ?? [];
}

function clear_current_order() {
    unset($_SESSION['current_order']);
    unset($_SESSION['last_order_id']);
}

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

function global_route_script() {
    echo '
<!-- Background Bubbles -->
<div class="bubbles" id="bubbles"></div>

<script>
(function() {
  // Create bubbles
  function createBubbles() {
    const bubblesContainer = document.getElementById("bubbles");
    if (!bubblesContainer) return;
    
    for (let i = 0; i < 30; i++) {
      const bubble = document.createElement("div");
      bubble.classList.add("bubble");
      const size = Math.random() * 60 + 20;
      bubble.style.width = size + "px";
      bubble.style.height = size + "px";
      bubble.style.left = Math.random() * 100 + "%";
      bubble.style.animationDelay = Math.random() * 15 + "s";
      bubble.style.animationDuration = Math.random() * 10 + 10 + "s";
      bubble.style.opacity = Math.random() * 0.3;
      bubblesContainer.appendChild(bubble);
    }
  }
  
  createBubbles();
  
  // Dark mode toggle
  const html = document.documentElement;
  const toggleBtns = document.querySelectorAll("#themeToggle");
  const isDark = localStorage.theme === "dark" || (!localStorage.theme && window.matchMedia("(prefers-color-scheme: dark)").matches);
  
  if (isDark) {
    html.classList.add("dark");
  }
  
  toggleBtns.forEach(btn => {
    btn.innerHTML = isDark ? "☀️ Light Mode" : "🌙 Dark Mode";
    btn.classList.add("px-3", "py-1.5", "rounded-full", "text-sm", "font-semibold", "transition-all", "duration-300", "shadow-md");
    
    if (isDark) {
      btn.classList.add("bg-gray-700", "text-white", "hover:bg-gray-600");
    } else {
      btn.classList.add("bg-gray-200", "text-gray-800", "hover:bg-gray-300");
    }
    
    btn.onclick = function() {
      html.classList.toggle("dark");
      const newIsDark = html.classList.contains("dark");
      localStorage.theme = newIsDark ? "dark" : "light";
      
      document.querySelectorAll("#themeToggle").forEach(b => {
        b.innerHTML = newIsDark ? "☀️ Light Mode" : "🌙 Dark Mode";
        if (newIsDark) {
          b.classList.remove("bg-gray-200", "text-gray-800", "hover:bg-gray-300");
          b.classList.add("bg-gray-700", "text-white", "hover:bg-gray-600");
        } else {
          b.classList.remove("bg-gray-700", "text-white", "hover:bg-gray-600");
          b.classList.add("bg-gray-200", "text-gray-800", "hover:bg-gray-300");
        }
      });
    };
  });

  // Add animation to cards
  const observerOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("fade-in-up");
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);
  
  document.querySelectorAll(".card-animate").forEach(el => {
    observer.observe(el);
  });
})();
</script>

<style>
.fade-in-up {
  animation: fadeInUp 0.5s ease forwards;
  opacity: 0;
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.card-animate {
  opacity: 0;
}

/* Bubbles animation */
.bubbles {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 0;
  overflow: hidden;
}

.bubble {
  position: absolute;
  bottom: -50px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 50%;
  animation: rise 15s infinite ease-in;
}

@keyframes rise {
  0% {
    bottom: -50px;
    transform: translateX(0);
  }
  50% {
    transform: translateX(20px);
  }
  100% {
    bottom: 100%;
    transform: translateX(-20px);
  }
}

/* Dark mode improvements */
.dark,
.dark * {
  color-scheme: dark;
}

.dark .bg-white {
  background-color: #1e1e2f !important;
}

.dark .text-gray-800,
.dark .text-gray-900 {
  color: #e2e8f0 !important;
}

.dark .text-gray-500,
.dark .text-gray-600 {
  color: #94a3b8 !important;
}

.dark .border-gray-200 {
  border-color: #334155 !important;
}

.dark .bg-gray-50 {
  background-color: #0f0f1a !important;
}

.dark .badge-warning {
  background: #78350f;
  color: #fbbf24;
}

.dark .badge-info {
  background: #1e3a5f;
  color: #60a5fa;
}

.dark .badge-success {
  background: #064e3b;
  color: #34d399;
}
</style>';
}
?>