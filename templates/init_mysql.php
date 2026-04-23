<?php
/**
 * One-click MySQL DB setup for laundry app
 */
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec('CREATE DATABASE IF NOT EXISTS laundry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE laundry');
    
    $schema = file_get_contents('database.sql');
    $pdo->exec($schema);
    
    echo '<h1>✅ Laundry DB created successfully!</h1>
    <p>All tables + seed data ready.</p>
    <a href="code/login.php" class="bg-blue-500 text-white px-6 py-2 rounded text-lg">Login → worker@laundry.com / workerpass</a>';
} catch (Exception $e) {
    echo '<h1>❌ Error: ' . htmlspecialchars($e->getMessage()) . '</h1>
    <p>Check MySQL running in XAMPP.</p>';
}
?>

