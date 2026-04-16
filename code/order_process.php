<?php
require_once __DIR__ . '/common.php';
require_worker();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    if ($_POST['action'] === 'process_order') {
        $orderId = $_POST['order_id'];
        if (update_order_status($orderId, 'proses')) {
            set_flash('success', 'Order ' . htmlspecialchars($orderId) . ' berhasil diproses.');
        } else {
            set_flash('error', 'Gagal memproses order.');
        }
        header('Location: worker.php');
        exit;
    }
}

header('Location: worker.php');
exit;
?>