<?php
require_once 'common.php';
$user = currentUser();
if (!$user || $user['role'] !== 'customer') { 
    header('Location: login.php?role=' . ($user['role'] ?? 'customer')); 
    exit; 
}

$db = get_db_wrapper();
$stmt = $db->prepare('SELECT o.*, s.name as service_name FROM orders o LEFT JOIN services s ON o.service_id = s.id WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 3');
$stmt->execute([$user['id']]);
$recentOrders = $stmt->fetchAll();

$stmt = $db->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ? AND status != "Completed"');
$stmt->execute([$user['id']]);
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
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl overflow-x-hidden">
<div class="px-4 py-4 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
<h2 class="text-lg font-bold">Dashboard</h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border text-slate-500 dark:text-slate-200">Mode</button>
</div>

<!-- Flash -->
<div class="px-4">
<?php if ($msg = get_flash('success')): ?>
<div class="mb-4 bg-emerald-100 border border-emerald-200 text-emerald-800 p-3 rounded-lg"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
</div>

<!-- Recent Orders -->
<div class="px-4 space-y-3 py-4">
<h3 class="font-bold text-lg">Recent Orders (<?= count($recentOrders) ?>)</h3>
<?php foreach ($recentOrders as $order): ?>
<div class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-800">
<p class="font-bold">#<?= $order['id'] ?> - <?= $order['status'] ?></p>
<p class="text-sm"><?= $order['service_name'] ?? 'Service' ?> - <?= $order['weight'] ?>kg</p>
<p class="text-primary font-bold">Rp <?= number_format($order['total'],0,',','.') ?></p>
</div>
<?php endforeach; ?>
<a href="neworder.php" class="w-full bg-primary text-white py-4 rounded-xl text-center font-bold mt-4 block">+ Create New Order</a>
</div>

<!-- Bottom Nav -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto z-20 border-t bg-white dark:bg-slate-900 px-4 pb-6 pt-2">
<div class="flex gap-2">
<a href="dashboard.php" class="flex-1 text-center text-primary font-bold py-2">Home</a>
<a href="history.php" class="flex-1 text-center">History</a>
<a href="price.php" class="flex-1 text-center">Pricing</a>
<a href="profile.php" class="flex-1 text-center">Profile</a>
</div>
</div>

<?= global_route_script() ?>
</body>
</html>

