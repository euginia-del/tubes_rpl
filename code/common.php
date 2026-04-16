<?php
/**
 * Common functions for Laundry app
 */
session_start();
require_once __DIR__ . '/koneksi.php';

function get_db() {
    static $db = null;
    if ($db === null) {
        $dsn = 'mysql:host=localhost;dbname=laundry_db;charset=utf8mb4';
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
        $db = new PDO($dsn, 'root', '', $options);
    }
    return $db;
}

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

function loginUser($email, $password) {
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM user WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // Password comparison (plain text as per your current implementation)
    return ($user && $password === $user['password']) ? $user : false;
}

function logout_user() {
    session_destroy();
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function set_current_order($data) {
    $_SESSION['current_order'] = array_merge($_SESSION['current_order'] ?? [], $data);
}

function get_current_order() {
    return $_SESSION['current_order'] ?? [];
}

function clear_current_order() {
    unset($_SESSION['current_order']);
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
<script>
(function() {
  "use strict";
  document.addEventListener("DOMContentLoaded", function() {
    const html = document.documentElement;
    const toggleBtns = document.querySelectorAll("#themeToggle");
    const isDark = localStorage.theme === "dark" || (!localStorage.theme && window.matchMedia("(prefers-color-scheme: dark)").matches);
    
    if (isDark) {
      html.classList.add("dark");
    }
    
    toggleBtns.forEach(btn => {
      btn.textContent = isDark ? "Light" : "Dark";
      btn.onclick = function() {
        html.classList.toggle("dark");
        localStorage.theme = html.classList.contains("dark") ? "dark" : "light";
        document.querySelectorAll("#themeToggle").forEach(b => {
          b.textContent = localStorage.theme === "dark" ? "Light" : "Dark";
        });
      };
    });
  });
})();
</script>';
}
?>