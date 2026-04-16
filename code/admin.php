<?php
require_once 'common.php';
require_admin();

$db = get_db();
$totalOrders = get_order_count($db);
$completed = get_completed_orders_count($db);
$pending = get_pending_orders_count($db);
$totalUsers = get_user_count($db);
$allOrders = get_all_orders($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Dashboard</title>
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
<div class="bg-primary/10 rounded-lg p-2">
<span class="material-symbols-outlined text-primary">admin_panel_settings</span>
</div>
<h2 class="text-lg font-bold flex-1 text-center">Admin Panel</h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border">Mode</button>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 gap-4 p-4">
<div class="bg-primary/5 rounded-xl p-4">
<span class="material-symbols-outlined text-primary">receipt_long</span>
<p class="text-xs text-slate-500 mt-1">Total Orders</p>
<p class="text-2xl font-bold"><?= $totalOrders ?></p>
</div>
<div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4">
<span class="material-symbols-outlined text-emerald-600">task_alt</span>
<p class="text-xs text-slate-500 mt-1">Completed</p>
<p class="text-2xl font-bold"><?= $completed ?></p>
</div>
<div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-4">
<span class="material-symbols-outlined text-yellow-600">schedule</span>
<p class="text-xs text-slate-500 mt-1">Pending</p>
<p class="text-2xl font-bold"><?= $pending ?></p>
</div>
<div class="bg-slate-100 dark:bg-slate-800 rounded-xl p-4">
<span class="material-symbols-outlined text-slate-500">group</span>
<p class="text-xs text-slate-500 mt-1">Users</p>
<p class="text-2xl font-bold"><?= $totalUsers ?></p>
</div>
</div>

<!-- Management Cards -->
<div class="px-4 space-y-3">
<a href="price.php" class="block p-4 bg-gradient-to-r from-primary to-blue-600 text-white rounded-xl shadow-lg">
<span class="material-symbols-outlined float-left mr-3">payment</span>
<h3 class="font-bold">Pricing & Services</h3>
<p class="text-sm opacity-90">Manage services & pricing</p>
</a>
<a href="history.php" class="block p-4 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl shadow-lg">
<span class="material-symbols-outlined float-left mr-3">receipt_long</span>
<h3 class="font-bold">All Orders</h3>
<p class="text-sm opacity-90">View complete order history</p>
</a>
</div>

<!-- Recent Orders -->
<div class="p-4">
<h3 class="font-bold text-lg mb-3">Recent Orders</h3>
<div class="space-y-2">
<?php foreach (array_slice($allOrders, 0, 5) as $order): ?>
<div class="border rounded-xl p-3 text-sm">
<div class="flex justify-between">
<span class="font-bold">#<?= $order['id_order'] ?></span>
<span class="px-2 py-0.5 rounded-full text-xs <?= $order['status_order'] == 'selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-yellow-100 text-yellow-700' ?>"><?= $order['status_order'] ?></span>
</div>
<p class="text-slate-500"><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Bottom Nav -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto border-t bg-white dark:bg-slate-900 px-4 pb-6 pt-2">
<div class="flex gap-2">
<a href="admin.php" class="flex-1 text-center text-primary font-bold py-2">Admin</a>
<a href="history.php" class="flex-1 text-center text-slate-500 py-2">Orders</a>
<a href="price.php" class="flex-1 text-center text-slate-500 py-2">Settings</a>
<a href="profile.php" class="flex-1 text-center text-slate-500 py-2">Profile</a>
</div>
</div>

<?= global_route_script() ?>
</body>
</html>