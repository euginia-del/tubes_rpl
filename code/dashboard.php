<?php
require_once 'common.php';
$user = currentUser();
if (!$user || $user['role'] !== 'customer') { 
    header('Location: login.php'); 
    exit; 
}

$db = get_db();
$stmt = $db->prepare('SELECT o.*, l.nama_layanan as service_name 
    FROM Orders o 
    LEFT JOIN Layanan l ON o.id_layanan = l.id_layanan 
    WHERE o.id_user = ? 
    ORDER BY o.tanggal_order DESC 
    LIMIT 5');
$stmt->execute([$user['id_user']]);
$recentOrders = $stmt->fetchAll();

$stmt = $db->prepare('SELECT COUNT(*) FROM Orders WHERE id_user = ? AND status_order != "selesai"');
$stmt->execute([$user['id_user']]);
$activeCount = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {darkMode: "class", theme: {extend: {colors: {"primary": "#2094f3","background-light": "#f5f7f8","background-dark": "#101a22"}, fontFamily: {"display": ["Inter"]}}}};
</script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl">
<div class="px-4 py-4 flex items-center justify-between border-b">
<h2 class="text-lg font-bold">Dashboard</h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border">Mode</button>
</div>

<?php if ($msg = get_flash('success')): ?>
<div class="mx-4 mt-3 bg-emerald-100 text-emerald-800 p-3 rounded-lg"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="grid grid-cols-2 gap-3 p-4">
<div class="bg-primary/10 rounded-xl p-3 text-center">
<p class="text-2xl font-bold text-primary"><?= $activeCount ?></p>
<p class="text-xs text-slate-500">Active Orders</p>
</div>
<div class="bg-slate-100 dark:bg-slate-800 rounded-xl p-3 text-center">
<p class="text-2xl font-bold"><?= count($recentOrders) ?></p>
<p class="text-xs text-slate-500">Total Orders</p>
</div>
</div>

<!-- Welcome -->
<div class="px-4">
    <p class="text-xl font-bold">Halo, <?= htmlspecialchars($user['nama']) ?>! 👋</p>
    <a href="neworder.php" class="w-full bg-primary text-white py-4 rounded-xl text-center font-bold block mt-4 shadow-lg">+ Buat Order Baru</a>
</div>

<!-- Recent Orders -->
<div class="px-4 py-4">
<h3 class="font-bold text-lg mb-3">Order Terbaru</h3>
<?php if (empty($recentOrders)): ?>
<p class="text-slate-500 text-center py-8">Belum ada order.</p>
<?php else: ?>
<div class="space-y-3">
<?php foreach ($recentOrders as $order): ?>
<div class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-800">
<div class="flex justify-between">
<div>
<p class="font-bold">#<?= $order['id_order'] ?></p>
<p class="text-sm"><?= $order['service_name'] ?? 'Layanan' ?> - <?= $order['berat_cucian'] ?> kg</p>
</div>
<span class="px-2 py-1 rounded-full text-xs font-bold <?= $order['status_order'] == 'selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-yellow-100 text-yellow-700' ?>">
<?= $order['status_order'] ?>
</span>
</div>
<p class="text-primary font-bold mt-2">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></p>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<!-- Bottom Nav -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto border-t bg-white dark:bg-slate-900 px-4 pb-6 pt-2">
<div class="flex gap-2">
<a href="dashboard.php" class="flex-1 text-center text-primary font-bold py-2">Home</a>
<a href="history.php" class="flex-1 text-center text-slate-500 py-2">History</a>
<a href="price.php" class="flex-1 text-center text-slate-500 py-2">Pricing</a>
<a href="profile.php" class="flex-1 text-center text-slate-500 py-2">Profile</a>
</div>
</div>

<?= global_route_script() ?>
</body>
</html>