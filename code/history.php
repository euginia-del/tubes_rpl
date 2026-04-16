<?php
require_once 'common.php';
$user = currentUser();
if (!$user) header('Location: login.php');

$filter = $_GET['filter'] ?? 'all';
$orders = get_orders(get_db(), $user['id_user']);

$filteredOrders = $orders;
if ($filter === 'pending') {
    $filteredOrders = array_filter($orders, fn($o) => $o['status_order'] === 'pending');
} elseif ($filter === 'proses') {
    $filteredOrders = array_filter($orders, fn($o) => $o['status_order'] === 'proses');
} elseif ($filter === 'selesai') {
    $filteredOrders = array_filter($orders, fn($o) => $o['status_order'] === 'selesai');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Order History</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {"primary": "#3398db", "background-light": "#f6f7f8", "background-dark": "#121a20"},
            fontFamily: {"display": ["Inter"]}
        }
    }
}
</script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl">
<div class="sticky top-0 flex items-center p-4 border-b bg-white dark:bg-slate-900">
<a href="dashboard.php" class="text-primary p-2 rounded-full hover:bg-slate-100">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h2 class="text-lg font-bold flex-1 text-center">Order History</h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border">Mode</button>
</div>

<!-- Tabs -->
<div class="flex border-b px-4 gap-2">
<a href="?filter=all" class="flex-1 text-center py-3 <?= $filter === 'all' ? 'text-primary border-b-2 border-primary font-bold' : 'text-slate-500' ?>">All</a>
<a href="?filter=pending" class="flex-1 text-center py-3 <?= $filter === 'pending' ? 'text-primary border-b-2 border-primary font-bold' : 'text-slate-500' ?>">Pending</a>
<a href="?filter=proses" class="flex-1 text-center py-3 <?= $filter === 'proses' ? 'text-primary border-b-2 border-primary font-bold' : 'text-slate-500' ?>">Proses</a>
<a href="?filter=selesai" class="flex-1 text-center py-3 <?= $filter === 'selesai' ? 'text-primary border-b-2 border-primary font-bold' : 'text-slate-500' ?>">Selesai</a>
</div>

<div class="p-4 space-y-3 pb-24">
<?php if (empty($filteredOrders)): ?>
<p class="text-center text-slate-500 py-8">Tidak ada order.</p>
<?php endif; ?>

<?php foreach ($filteredOrders as $order): ?>
<div class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-800">
<div class="flex justify-between items-start">
<div>
<p class="font-bold">#<?= $order['id_order'] ?></p>
<p class="text-sm"><?= $order['service_name'] ?? 'Layanan' ?> - <?= $order['berat_cucian'] ?> kg</p>
<p class="text-xs text-slate-500"><?= date('d/m/Y', strtotime($order['tanggal_order'])) ?></p>
</div>
<span class="px-2 py-1 rounded-full text-xs font-bold <?= $order['status_order'] == 'selesai' ? 'bg-emerald-100 text-emerald-700' : ($order['status_order'] == 'proses' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') ?>">
<?= $order['status_order'] ?>
</span>
</div>
<p class="text-primary font-bold mt-2">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></p>
<a href="order_detail_process.php?order=<?= $order['id_order'] ?>" class="block text-center bg-primary text-white py-2 rounded-lg mt-3 text-sm">Detail</a>
</div>
<?php endforeach; ?>
</div>

<!-- Bottom Nav -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto border-t bg-white dark:bg-slate-900 px-4 pb-6 pt-2">
<div class="flex gap-2">
<a href="dashboard.php" class="flex-1 text-center text-slate-500 py-2">Home</a>
<a href="neworder.php" class="flex-1 text-center text-slate-500 py-2">New Order</a>
<a href="history.php" class="flex-1 text-center text-primary font-bold py-2">History</a>
<a href="profile.php" class="flex-1 text-center text-slate-500 py-2">Profile</a>
</div>
</div>

<?= global_route_script() ?>
</body>
</html>