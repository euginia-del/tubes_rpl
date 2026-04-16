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

// ========== FUNGSI HASH PASSWORD ==========
function hash_password($password) {
    return hash('sha256', $password);
}

// ========== ROLE-BASED ACCESS CONTROL ==========
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

// ========== LOGIN DENGAN HASH PASSWORD ==========
function loginUser($email, $password) {
    $db = get_db();
    $hashed_password = hash_password($password);
    
    $stmt = $db->prepare('SELECT * FROM user WHERE email = ? AND password = ?');
    $stmt->execute([$email, $hashed_password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $user ? $user : false;
}

function logout_user() {
    session_destroy();
    header('Location: login.php');
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// ========== ORDER FUNCTIONS ==========
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

// ========== SESSION ORDER FUNCTIONS ==========
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

// ========== SERVICE FUNCTIONS ==========
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

// ========== FLASH MESSAGE FUNCTIONS ==========
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

// ========== COUNT FUNCTIONS ==========
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

// ========== REGISTER FUNCTION ==========
function registerUser($nama, $email, $password, $no_hp, $alamat) {
    $db = get_db();
    
    // Cek email sudah terdaftar
    $stmt = $db->prepare('SELECT COUNT(*) FROM user WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        return false;
    }
    
    // Hash password sebelum disimpan
    $hashed_password = hash_password($password);
    
    $stmt = $db->prepare('INSERT INTO user (nama, email, password, no_hp, alamat, role, saldo) 
        VALUES (?, ?, ?, ?, ?, "customer", 0)');
    
    return $stmt->execute([$nama, $email, $hashed_password, $no_hp, $alamat]);
}

// ========== UI FUNCTIONS ==========
function global_route_script() {
    echo '
<!-- Bubbles Container -->
<div class="bubbles-container" id="bubblesContainer"></div>
<div class="soap-icon" id="soapIcon">
    <span class="material-symbols-outlined">soap</span>
</div>

<script>
// ========== CREATE BUBBLES ANIMATION ==========
function createBubbles() {
    const container = document.getElementById("bubblesContainer");
    if (!container) return;
    
    const bubbleSizes = ["bubble-small", "bubble-medium", "bubble-large", "bubble-xl"];
    const bubbleCount = 50;
    
    for (let i = 0; i < bubbleCount; i++) {
        const bubble = document.createElement("div");
        const sizeClass = bubbleSizes[Math.floor(Math.random() * bubbleSizes.length)];
        bubble.classList.add("bubble", sizeClass);
        
        bubble.style.left = Math.random() * 100 + "%";
        const duration = Math.random() * 15 + 8;
        bubble.style.animationDuration = duration + "s";
        bubble.style.animationDelay = Math.random() * 20 + "s";
        bubble.style.opacity = Math.random() * 0.4 + 0.1;
        
        container.appendChild(bubble);
    }
}

// ========== SOAP ICON CLICK EFFECT ==========
function initSoapIcon() {
    const soapIcon = document.getElementById("soapIcon");
    if (soapIcon) {
        soapIcon.addEventListener("click", function() {
            // Create ripple effect
            const ripple = document.createElement("div");
            ripple.style.position = "fixed";
            ripple.style.bottom = "70px";
            ripple.style.right = "70px";
            ripple.style.width = "10px";
            ripple.style.height = "10px";
            ripple.style.borderRadius = "50%";
            ripple.style.background = "radial-gradient(circle, rgba(99,102,241,0.5) 0%, rgba(139,92,246,0) 70%)";
            ripple.style.pointerEvents = "none";
            ripple.style.zIndex = "99";
            ripple.style.animation = "scaleIn 0.5s ease-out forwards";
            document.body.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 500);
            
            // Create temporary bubble
            for(let i = 0; i < 5; i++) {
                const tempBubble = document.createElement("div");
                tempBubble.classList.add("bubble", "bubble-small");
                tempBubble.style.left = (Math.random() * 100) + "%";
                tempBubble.style.bottom = "20px";
                tempBubble.style.position = "fixed";
                tempBubble.style.animation = "rise 2s ease-in-out";
                document.body.appendChild(tempBubble);
                setTimeout(() => tempBubble.remove(), 2000);
            }
        });
    }
}

// ========== DARK MODE TOGGLE ==========
function initDarkMode() {
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
}

// ========== SCROLL ANIMATION ==========
function initScrollAnimation() {
    const observerOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("fade-in-up");
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll(".card-animate, .stat-card, .service-card").forEach(el => {
        observer.observe(el);
    });
}

// ========== TOAST NOTIFICATION ==========
function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.className = `toast ${type === "success" ? "bg-emerald-500" : "bg-red-500"} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-2`;
    toast.innerHTML = `<span class="material-symbols-outlined">${type === "success" ? "check_circle" : "error"}</span> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ========== INITIALIZE ALL ==========
document.addEventListener("DOMContentLoaded", function() {
    createBubbles();
    initSoapIcon();
    initDarkMode();
    initScrollAnimation();
});
</script>

<style>
/* Additional styles for animations */
@keyframes scaleIn {
    from { transform: scale(1); opacity: 1; }
    to { transform: scale(20); opacity: 0; }
}

.fade-in-up {
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.card-animate, .stat-card, .service-card {
    opacity: 0;
}

/* Button hover effects */
.btn-hover-effect {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-hover-effect::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    transform: translate(-50%, -50%);
    transition: width 0.5s, height 0.5s;
}

.btn-hover-effect:active::after {
    width: 200px;
    height: 200px;
}

/* Loading overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(5px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 3px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
</style>';
}
?>