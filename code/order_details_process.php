<?php
require_once 'common.php';
$user = currentUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['order'] ?? null;
$order = get_order($order_id);
if (!$order) {
    set_flash('error', 'Order tidak ditemukan');
    header('Location: history.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Order Detail</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {"primary": "#2094f3", "background-light": "#f5f7f8", "background-dark": "#101a22"},
            fontFamily: {"display": ["Inter"]}
        }
    }
}
</script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl">
<div class="flex items-center p-4 border-b">
<a href="history.php" class="text-primary p-2 rounded-full hover:bg-slate-100">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h2 class="text-lg font-bold flex-1 text-center">Order #<?= $order['id_order'] ?></h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border">Mode</button>
</div>

<div class="p-4 space-y-4">
<div class="bg-primary/5 rounded-xl p-4 text-center">
    <p class="text-sm text-slate-500">Status Order</p>
    <p class="text-2xl font-bold <?= $order['status_order'] == 'selesai' ? 'text-emerald-600' : ($order['status_order'] == 'proses' ? 'text-blue-600' : 'text-yellow-600') ?>">
        <?= strtoupper($order['status_order']) ?>
    </p>
</div>

<div class="space-y-3">
    <div class="flex justify-between py-2 border-b">
        <span class="text-slate-500">Customer</span>
        <span class="font-semibold"><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></span>
    </div>
    <div class="flex justify-between py-2 border-b">
        <span class="text-slate-500">Layanan</span>
        <span class="font-semibold"><?= htmlspecialchars($order['service_name'] ?? 'N/A') ?></span>
    </div>
    <div class="flex justify-between py-2 border-b">
        <span class="text-slate-500">Berat</span>
        <span class="font-semibold"><?= $order['berat_cucian'] ?> kg</span>
    </div>
    <div class="flex justify-between py-2 border-b">
        <span class="text-slate-500">Total Harga</span>
        <span class="font-bold text-primary">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></span>
    </div>
    <div class="flex justify-between py-2 border-b">
        <span class="text-slate-500">Tanggal Order</span>
        <span class="font-semibold"><?= date('d/m/Y', strtotime($order['tanggal_order'])) ?></span>
    </div>
</div>

<?php if ($order['catatan']): ?>
<div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-4">
    <p class="text-sm font-semibold">Catatan:</p>
    <p class="text-sm"><?= htmlspecialchars($order['catatan']) ?></p>
</div>
<?php endif; ?>

<?php if ($user['role'] === 'worker' && $order['status_order'] === 'pending'): ?>
<form method="post" action="order_process.php" class="mt-4">
    <input type="hidden" name="order_id" value="<?= $order['id_order'] ?>">
    <input type="hidden" name="action" value="process_order">
    <button type="submit" class="w-full bg-primary text-white font-bold py-3 rounded-xl">Proses Order</button>
</form>
<?php endif; ?>
</div>

<?= global_route_script() ?>
</body>
</html>