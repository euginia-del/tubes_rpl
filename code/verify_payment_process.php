<?php
require_once 'common.php';
require_supervisor();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $db = get_db();
    
    // Update status pembayaran
    $stmt = $db->prepare('UPDATE pembayaran SET status_bayar = "lunas" WHERE id_order = ?');
    $stmt->execute([$orderId]);
    
    // Update status order menjadi proses
    update_order_status($orderId, 'proses');
    
    set_flash('success', 'Pembayaran order #' . $orderId . ' berhasil diverifikasi!');
    header('Location: supervisor.php');
    exit;
}

header('Location: supervisor.php');
exit;
?>