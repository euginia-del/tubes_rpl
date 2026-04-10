<?php
require_once 'common.php';
require_supervisor();

$db = get_db_wrapper();

// Verify payment POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_order_id'])) {
    $orderId = $_POST['verify_order_id'];
    $stmt = $db->prepare('UPDATE orders SET payment_status = "verified", status = "Pending Pickup" WHERE id = ?');
    if ($stmt->execute([$orderId])) {
        set_flash('success', 'Payment verified for order #' . $orderId);
    } else {
        set_flash('error', 'Failed to verify payment.');
    }
    header('Location: supervisor.php');
    exit;
}

// Get pending payments
$stmt = $db->prepare('SELECT o.*, u.name as customer_name, s.name as service_type FROM orders o LEFT JOIN users u ON o.user_id = u.id LEFT JOIN services s ON o.service_id = s.id WHERE o.payment_status = "paid" AND o.status = "Pending Pickup" ORDER BY o.created_at DESC');
$stmt->execute();
$pendingPayments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Supervisor - Pending Payments</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {darkMode: "class", theme: {extend: {colors: {"primary": "#2094f3","background-light": "#f5f7f8","background-dark": "#101a22"}, fontFamily: {"display": ["Inter"]}}}};
</script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl pt-4 pb-24">
<!-- Header -->
<div class="px-4 py-4 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
<h2 class="text-lg font-bold">Pending Payments</h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border text-slate-500 dark:text-slate-200">Mode</button>
</div>

<!-- Flash Messages -->
<div class="px-4 mt-3">
<?php if ($msg = get_flash('success')): ?>
<div class="mb-4 bg-emerald-100 border border-emerald-200 text-emerald-800 p-3 rounded-lg"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($err = get_flash('error')): ?>
<div class="mb-4 bg-red-100 border border-red-200 text-red-800 p-3 rounded-lg"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>
</div>

<!-- Pending List -->
<div class="px-4 space-y-3">
<h3 class="font-bold text-lg">Orders to Verify (<?= count($pendingPayments) ?>)</h3>
<?php if (empty($pendingPayments)): ?>
<p class="text-slate-500 text-center py-8">No pending payments.</p>
<?php else: ?>
<?php foreach ($pendingPayments as $order): ?>
<div class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-800">
<p class="font-bold">#<?= $order['id'] ?> <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
<p class="text-sm"><?= htmlspecialchars($order['service_type'] ?? 'N/A') ?> - <?= $order['weight'] ?>kg</p>
<p class="text-primary font-bold">Rp <?= number_format($order['total'],0,',','.') ?></p>
<form method="post" class="mt-3">
<input type="hidden" name="verify_order_id" value="<?= $order['id'] ?>">
<button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-bold">Verify Payment</button>
</form>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<!-- Bottom Nav -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto z-20 border-t bg-white dark:bg-slate-900 px-4 pb-6 pt-2">
<div class="flex gap-2">
<a href="supervisor.php" class="flex-1 text-center text-primary font-bold py-2">Payments</a>
<a href="history.php" class="flex-1 text-center">History</a>
<a href="price.php" class="flex-1 text-center">Pricing</a>
<a href="profile.php" class="flex-1 text-center">Profile</a>
</div>
</div>

<?= global_route_script() ?>
</body>
</html>
