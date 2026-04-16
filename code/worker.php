<?php
require_once 'common.php';
require_worker();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    if ($_POST['action'] === 'process_order') {
        update_order_status($_POST['order_id'], 'proses');
        set_flash('success', 'Order ' . $_POST['order_id'] . ' sedang diproses.');
        header('Location: worker.php');
        exit;
    }
    if ($_POST['action'] === 'complete_order') {
        update_order_status($_POST['order_id'], 'selesai');
        set_flash('success', 'Order ' . $_POST['order_id'] . ' selesai.');
        header('Location: worker.php');
        exit;
    }
}

$pendingOrders = get_pending_orders();
$processOrders = get_orders();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Worker Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {"primary": "#ec5b13", "background-light": "#f8f6f6", "background-dark": "#221610"},
            fontFamily: {"display": ["Inter"]}
        }
    }
}
</script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl pb-24">
<div class="px-4 py-4 flex items-center justify-between border-b">
<h1 class="text-xl font-bold">Worker Dashboard</h1>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border">Mode</button>
</div>

<?php if ($msg = get_flash('success')): ?>
<div class="mx-4 mt-3 bg-emerald-100 text-emerald-800 p-3 rounded-lg"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- Pending Orders -->
<div class="p-4">
<h3 class="font-bold text-lg mb-3">Pending Orders (<?= count($pendingOrders) ?>)</h3>
<div class="space-y-3">
<?php foreach ($pendingOrders as $order): ?>
<div class="border rounded-xl p-4">
<div class="flex justify-between">
<p class="font-bold">#<?= $order['id_order'] ?></p>
<span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full text-xs">pending</span>
</div>
<p class="text-sm">Customer: <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
<p class="text-sm"><?= $order['service_name'] ?? 'Layanan' ?> - <?= $order['berat_cucian'] ?> kg</p>
<form method="post" class="mt-2">
<input type="hidden" name="order_id" value="<?= $order['id_order'] ?>">
<input type="hidden" name="action" value="process_order">
<button class="w-full bg-primary text-white py-2 rounded-lg">Proses Order</button>
</form>
</div>
<?php endforeach; ?>
<?php if (empty($pendingOrders)): ?>
<p class="text-slate-500 text-center py-4">Tidak ada pending order</p>
<?php endif; ?>
</div>
</div>

<!-- Orders In Progress -->
<div class="p-4 pt-0">
<h3 class="font-bold text-lg mb-3">Orders In Progress</h3>
<div class="space-y-3">
<?php 
$inProgress = array_filter($pendingOrders, fn($o) => $o['status_order'] === 'proses');
foreach ($inProgress as $order): ?>
<div class="border rounded-xl p-4">
<div class="flex justify-between">
<p class="font-bold">#<?= $order['id_order'] ?></p>
<span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">proses</span>
</div>
<p class="text-sm">Customer: <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
<form method="post" class="mt-2">
<input type="hidden" name="order_id" value="<?= $order['id_order'] ?>">
<input type="hidden" name="action" value="complete_order">
<button class="w-full bg-emerald-600 text-white py-2 rounded-lg">Selesaikan Order</button>
</form>
</div>
<?php endforeach; ?>
<?php if (empty($inProgress)): ?>
<p class="text-slate-500 text-center py-4">Tidak ada order dalam proses</p>
<?php endif; ?>
</div>
</div>

<!-- Bottom Nav -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto border-t bg-white dark:bg-slate-900 px-4 pb-6 pt-2">
<div class="flex gap-2">
<a href="worker.php" class="flex-1 text-center text-primary font-bold py-2">Orders</a>
<a href="profile.php" class="flex-1 text-center text-slate-500 py-2">Profile</a>
</div>
</div>

<?= global_route_script() ?>
</body>
</html>