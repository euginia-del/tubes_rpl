<?php
require_once 'common.php';
require_admin();

$db = get_db_wrapper();
$totalOrders = $db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$completed = $db->query('SELECT COUNT(*) FROM orders WHERE status = "Completed"')->fetchColumn();
$pending = $db->query('SELECT COUNT(*) FROM orders WHERE status = "Pending Pickup"')->fetchColumn();
$totalUsers = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$orders = get_orders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Admin Dashboard - Laundry App</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "primary": "#2094f3",
                "background-light": "#f5f7f8",
                "background-dark": "#101a22",
            },
            fontFamily: {
                "display": ["Inter", "sans-serif"]
            }
        }
    }
}
</script>
<style>body { min-height: max(884px, 100dvh); }</style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100">
<div class="relative flex min-h-screen w-full flex-col max-w-md mx-auto bg-white dark:bg-slate-900 shadow-xl overflow-x-hidden">
<!-- Header -->
<div class="flex items-center bg-white dark:bg-slate-900 p-4 pb-2 justify-between sticky top-0 z-10 border-b border-slate-100 dark:border-slate-800">
<div class="text-primary flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10">
<span class="material-symbols-outlined">admin_panel_settings</span>
</div>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-tight flex-1 text-center">Admin Panel</h2>
<button id="themeToggle" class="text-slate-500 dark:text-slate-200 text-xs font-semibold px-2 py-1 rounded-full border border-slate-200 dark:border-slate-700">Mode</button>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-2 gap-4 px-4 py-4">
<div class="bg-primary/5 dark:bg-primary/10 border border-primary/10 rounded-xl p-4">
<span class="material-symbols-outlined text-primary mb-2">local_laundry_service</span>
<p class="text-slate-500 dark:text-slate-400 text-xs font-medium uppercase tracking-wider">Total Orders</p>
<p class="text-slate-900 dark:text-slate-100 text-2xl font-bold"><?= $totalOrders ?></p>
</div>
<div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4">
<span class="material-symbols-outlined text-emerald-600 mb-2">task_alt</span>
<p class="text-slate-500 dark:text-slate-400 text-xs font-medium uppercase tracking-wider">Completed</p>
<p class="text-slate-900 dark:text-slate-100 text-2xl font-bold"><?= $completed ?></p>
</div>
<div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-xl p-4">
<span class="material-symbols-outlined text-orange-600 mb-2">schedule</span>
<p class="text-slate-500 dark:text-slate-400 text-xs font-medium uppercase tracking-wider">Pending</p>
<p class="text-slate-900 dark:text-slate-100 text-2xl font-bold"><?= $pending ?></p>
</div>
<div class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4">
<span class="material-symbols-outlined text-slate-500 mb-2">group</span>
<p class="text-slate-500 dark:text-slate-400 text-xs font-medium uppercase tracking-wider">Users</p>
<p class="text-slate-900 dark:text-slate-100 text-2xl font-bold"><?= $totalUsers ?></p>
</div>
</div>

<!-- Management Cards -->
<div class="px-4 py-4 space-y-4">
<a href="#" class="block p-4 bg-gradient-to-r from-primary to-blue-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all">
<span class="material-symbols-outlined float-left mr-3 text-xl opacity-90">manage_accounts</span>
<h3 class="font-bold text-lg mb-1">User Management</h3>
<p class="opacity-90 text-sm">View & edit users, roles, permissions</p>
</a>
<a href="price.php" class="block p-4 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all">
<span class="material-symbols-outlined float-left mr-3 text-xl opacity-90">payment</span>
<h3 class="font-bold text-lg mb-1">Pricing & Services</h3>
<p class="opacity-90 text-sm">Manage services, update pricing tiers</p>
</a>
<a href="history.php" class="block p-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all">
<span class="material-symbols-outlined float-left mr-3 text-xl opacity-90">receipt_long</span>
<h3 class="font-bold text-lg mb-1">All Orders</h3>
<p class="opacity-90 text-sm">View complete order history & analytics</p>
</a>
<a href="#" class="block p-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all">
<span class="material-symbols-outlined float-left mr-3 text-xl opacity-90">analytics</span>
<h3 class="font-bold text-lg mb-1">Reports</h3>
<p class="opacity-90 text-sm">Revenue, performance, worker stats</p>
</a>
</div>

<!-- Bottom Navigation -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto z-20">
<div class="flex gap-2 border-t border-slate-100 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md px-4 pb-6 pt-2">
<a class="flex flex-1 flex-col items-center justify-center gap-1 text-primary" href="admin.php">
<span class="material-symbols-outlined text-lg">dashboard</span>
<p class="text-xs font-bold">Admin</p>
</a>
<a class="flex flex-1 flex-col items-center justify-center gap-1 text-slate-400 dark:text-slate-500" href="history.php">
<span class="material-symbols-outlined">receipt_long</span>
<p class="text-xs font-medium">Orders</p>
</a>
<a class="flex flex-1 flex-col items-center justify-center gap-1 text-slate-400 dark:text-slate-500" href="price.php">
<span class="material-symbols-outlined">tune</span>
<p class="text-xs font-medium">Settings</p>
</a>
<a class="flex flex-1 flex-col items-center justify-center gap-1 text-slate-400 dark:text-slate-500" href="profile.php">
<span class="material-symbols-outlined">person</span>
<p class="text-xs font-medium">Profile</p>
</a>
</div>
</div>
</div>
<?php echo global_route_script(); ?>
</body>
</html>
