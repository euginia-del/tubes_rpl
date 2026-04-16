<?php
require_once 'common.php';
require_supervisor();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_order_id'])) {
    $orderId = $_POST['verify_order_id'];
    if (verify_payment($orderId)) {
        set_flash('success', 'Payment verified for order #' . $orderId);
    } else {
        set_flash('error', 'Failed to verify payment.');
    }
    header('Location: supervisor.php');
    exit;
}

$pendingOrders = get_pending_orders();
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
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl pb-24">
<div class="px-4 py-4 flex items-center justify-between border-b">
<h2 class="text-lg font-bold">Pending Payments</h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border">Mode</button>
</div>

<?php if ($msg = get_flash('success')): ?>
<div class="mx-4 mt-3 bg-emerald-100 text-emerald-800 p-3 rounded-lg"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="p-4">
<h3 class="font-bold text-lg">Orders to Verify (<?= count($pendingOrders) ?>)</h3>
<div class="space-y-3 mt-3">
<?php if (empty($pendingOrders)): ?>
<p class="text-slate-500 text-center py-8">No pending payments.</p>
<?php else: ?>
<?php foreach ($pendingOrders as $order): ?>
<div class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-800">
<p class="font-bold">#<?= $order['id_order'] ?> - <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
<p class="text-sm"><?= htmlspecialchars($order['service_name'] ?? 'N/A') ?> - <?= $order['berat_cucian'] ?> kg</p>
<p class="text-primary font-bold">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></p>
<form method="post" class="mt-3">
<input type="hidden" name="verify_order_id" value="<?= $order['id_order'] ?>">
<button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-xl font-bold">Verify Payment</button>
</form>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>

<!-- Bottom Nav -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto border-t bg-white dark:bg-slate-900 px-4 pb-6 pt-2">
<div class="flex gap-2">
<a href="supervisor.php" class="flex-1 text-center text-primary font-bold py-2">Payments</a>
<a href="profile.php" class="flex-1 text-center text-slate-500 py-2">Profile</a>
</div>
</div>

<?= global_route_script() ?>
</body>
</html>