<?php
require_once __DIR__ . '/common.php';
require_worker();

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    
    if ($_POST['action'] === 'process_order') {
        // Update status menjadi proses
        if (update_order_status($orderId, 'proses')) {
            set_flash('success', 'Order #' . htmlspecialchars($orderId) . ' berhasil diproses.');
        } else {
            set_flash('error', 'Gagal memproses order.');
        }
    } elseif ($_POST['action'] === 'complete_order') {
        // Update status menjadi selesai
        if (update_order_status($orderId, 'selesai')) {
            
            // ========== INSERT KE TABEL LAPORAN ==========
            // Ambil data order yang baru selesai
            $stmt = $db->prepare('SELECT id_user, tanggal_order, harga_snapshot FROM orders WHERE id_order = ?');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            if ($order) {
                // Cek apakah sudah ada di laporan (untuk menghindari duplikat)
                $stmt = $db->prepare('SELECT COUNT(*) FROM laporan WHERE id_order = ?');
                $stmt->execute([$orderId]);
                $exists = $stmt->fetchColumn();
                
                if (!$exists) {
                    // Insert ke tabel laporan
                    $stmt = $db->prepare('
                        INSERT INTO laporan (id_order, id_user, periode_bulan, periode_tahun, total_harga)
                        VALUES (?, ?, ?, ?, ?)
                    ');
                    $stmt->execute([
                        $orderId,
                        $order['id_user'],
                        date('n', strtotime($order['tanggal_order'])), // bulan (1-12)
                        date('Y', strtotime($order['tanggal_order'])), // tahun
                        $order['harga_snapshot']
                    ]);
                }
            }
            // =============================================
            
            set_flash('success', 'Order #' . htmlspecialchars($orderId) . ' selesai.');
        } else {
            set_flash('error', 'Gagal menyelesaikan order.');
        }
    }
    
    header('Location: worker.php');
    exit;
}

header('Location: worker.php');
exit;
?>