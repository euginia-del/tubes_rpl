<?php
/**
 * Common functions for Laundry app
 */
session_start();
require_once __DIR__ . '/koneksi.php';

function get_db() {
    static $db = null;
    if ($db === null) {
        $dsn = 'mysql:host=localhost;dbname=laundry;charset=utf8mb4';
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
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function update_order_status($order_id, $new_status, $db = null) {
    $db = $db ?: get_db();
    $stmt = $db->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?');
    $success = $stmt->execute([$new_status, $order_id]);
    
    $user = currentUser($db);
    $stmt_log = $db->prepare('INSERT INTO order_status_log (order_id, status, changed_by) VALUES (?, ?, ?)');
    $stmt_log->execute([$order_id, $new_status, $user['id'] ?? null]);
    
    return $success;
}

function get_orders($db = null) {
    $db = $db ?: get_db();
    $stmt = $db->prepare('SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.status != "Completed" ORDER BY created_at DESC');
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_order($id, $db = null) {
    $db = $db ?: get_db();
    $stmt = $db->prepare('SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_services($db = null) {
    $db = $db ?: get_db();
    $stmt = $db->query('SELECT * FROM services WHERE is_active = 1');
    return $stmt->fetchAll();
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
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user && $password === $user['password'] ? $user : false;
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

