<?php
require_once 'common.php';

$user = currentUser();
if (!$user) { 
    header('Location: login.php'); 
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'logout') {
        logout_user();
        header('Location: login.php');
        exit;
    }
}

$orders = get_orders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Profile</title>
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
<h2 class="text-lg font-bold">Profile</h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border text-slate-500 dark:text-slate-200">Mode</button>
</div>

<!-- Profile Card -->
<div class="px-4 space-y-4 py-4">
<div class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-800">
<p class="text-sm text-slate-500">Email</p>
<p class="font-bold"><?= htmlspecialchars($user['email']) ?></p>
<p class="text-sm text-slate-500 mt-2">Name</p>
<p class="font-bold"><?= htmlspecialchars($user['name']) ?></p>
<p class="text-sm text-slate-500 mt-2">Role</p>
<p class="font-bold px-3 py-1 bg-primary/10 text-primary rounded-full text-xs"><?= htmlspecialchars($user['role']) ?></p>
</div>

<div class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-800">
<p class="text-sm text-slate-500">Total Orders</p>
<p class="font-bold text-2xl"><?= $user['role'] === 'customer' ? count(get_orders(get_db_wrapper(), $user['id'])) : 'Admin view' ?></p>
<p class="text-sm text-slate-500 mt-1">Draft Order</p>
<p class="font-bold"><?= empty(get_current_order()) ? 'None' : 'Active' ?></p>
</div>

<form method="post">
<input type="hidden" name="action" value="logout" />
<button class="w-full bg-red-500 hover:bg-red-600 text-white py-4 rounded-xl font-bold mt-4">Logout</button>
</form>
</div>

<!-- Bottom Nav -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto z-20 border-t bg-white dark:bg-slate-900 px-4 pb-6 pt-2">
<div class="flex gap-2">
<a href="dashboard.php" class="flex-1 text-center text-slate-500">Home</a>
<a href="history.php" class="flex-1 text-center text-slate-500">Orders</a>
<a href="price.php" class="flex-1 text-center text-slate-500">Pricing</a>
<a class="flex-1 text-center text-primary font-bold" href="profile.php">Profile</a>
</div>
</div>

<?= global_route_script() ?>
</body>
</html>

