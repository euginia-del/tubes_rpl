<?php
require_once 'common.php';
$user = require_customer();

$db = get_db();
$currentOrder = get_current_order();

if (empty($currentOrder)) {
    echo json_encode(['success' => false, 'message' => 'No order data']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Simpan ke session untuk pickup info
$_SESSION['pickup_info'] = [
    'pickup_date' => $input['pickup_date'] ?? date('Y-m-d'),
    'pickup_time' => $input['pickup_time'] ?? '08:00-10:00',
    'pickup_address' => $input['pickup_address'] ?? $user['alamat']
];

// Buat order
$orderId = create_order();

if ($orderId) {
    // Update dengan info pickup
    $stmt = $db->prepare('UPDATE orders SET 
        pickup_date = ?, 
        pickup_time = ?, 
        pickup_address = ? 
        WHERE id_order = ?');
    $stmt->execute([
        $_SESSION['pickup_info']['pickup_date'],
        $_SESSION['pickup_info']['pickup_time'],
        $_SESSION['pickup_info']['pickup_address'],
        $orderId
    ]);
    
    echo json_encode(['success' => true, 'order_id' => $orderId]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create order']);
}
?>