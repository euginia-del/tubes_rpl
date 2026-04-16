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
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Order History - LaundryApp</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="style.css">
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: { "primary": "#6366f1", "secondary": "#8b5cf6" },
            fontFamily: { "display": ["Inter", "sans-serif"] }
        }
    }
}
</script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-900 dark:to-slate-800 min-h-screen pb-20 md:pb-0">

<!-- Desktop Sidebar -->
<div class="hidden md:flex md:fixed md:inset-y-0 md:left-0 md:w-72 bg-white dark:bg-slate-800 shadow-xl flex-col">
    <div class="flex items-center justify-center p-6 border-b dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">local_laundry_service</span>
            </div>
            <span class="text-xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">LaundryFresh</span>
        </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-2">
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <a href="neworder.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">add_shopping_cart</span>
            <span>New Order</span>
        </a>
        <a href="history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
            <span class="material-symbols-outlined">receipt_long</span>
            <span>History</span>
        </a>
        <a href="price.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">price_check</span>
            <span>Pricing</span>
        </a>
        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">account_circle</span>
            <span>Profile</span>
        </a>
    </nav>
    
    <div class="p-4 border-t dark:border-slate-700">
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">person</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($user['nama'] ?? 'User') ?></p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Customer</p>
            </div>
            <button id="themeToggle" class="text-xs px-2 py-1 rounded-full border dark:border-slate-600">🌙</button>
        </div>
    </div>
</div>

<!-- Mobile Header -->
<div class="md:hidden bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-40">
    <div class="flex items-center justify-between px-4 py-3">
        <a href="dashboard.php" class="text-gray-600 dark:text-gray-300">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <span class="text-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Order History</span>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6 hidden md:block">Order History</h1>

        <!-- Filter Tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="?filter=all" class="px-4 py-2 rounded-full text-sm font-semibold transition <?= $filter === 'all' ? 'bg-primary text-white shadow-md' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700' ?>">
                All Orders
            </a>
            <a href="?filter=pending" class="px-4 py-2 rounded-full text-sm font-semibold transition <?= $filter === 'pending' ? 'bg-amber-500 text-white shadow-md' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700' ?>">
                Pending
            </a>
            <a href="?filter=proses" class="px-4 py-2 rounded-full text-sm font-semibold transition <?= $filter === 'proses' ? 'bg-blue-500 text-white shadow-md' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700' ?>">
                Process
            </a>
            <a href="?filter=selesai" class="px-4 py-2 rounded-full text-sm font-semibold transition <?= $filter === 'selesai' ? 'bg-emerald-500 text-white shadow-md' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700' ?>">
                Completed
            </a>
        </div>

        <!-- Orders List -->
        <div class="space-y-4">
            <?php if (empty($filteredOrders)): ?>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-12 text-center">
                <span class="material-symbols-outlined text-6xl text-gray-400">inbox</span>
                <p class="text-gray-500 dark:text-gray-400 mt-4">No orders found</p>
                <a href="neworder.php" class="inline-block mt-4 text-primary font-semibold hover:underline">Create your first order →</a>
            </div>
            <?php else: ?>
                <?php foreach ($filteredOrders as $order): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition card-animate">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 flex-wrap">
                                <p class="font-bold text-gray-800 dark:text-white text-lg">#<?= $order['id_order'] ?></p>
                                <span class="badge <?= $order['status_order'] == 'selesai' ? 'badge-success' : ($order['status_order'] == 'proses' ? 'badge-info' : 'badge-warning') ?>">
                                    <?= $order['status_order'] ?>
                                </span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mt-2"><?= $order['service_name'] ?? 'Layanan' ?> • <?= $order['berat_cucian'] ?> kg</p>
                            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1"><?= date('d/m/Y H:i', strtotime($order['tanggal_order'])) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-primary font-bold text-xl">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></p>
                            <a href="order_details_process.php?order=<?= $order['id_order'] ?>" class="inline-flex items-center gap-1 text-primary text-sm font-semibold hover:underline mt-2">
                                View Details
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav md:hidden">
    <div class="flex justify-around">
        <a href="dashboard.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="text-xs">Home</span>
        </a>
        <a href="neworder.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">add_shopping_cart</span>
            <span class="text-xs">New</span>
        </a>
        <a href="history.php" class="flex flex-col items-center gap-1 text-primary">
            <span class="material-symbols-outlined">receipt_long</span>
            <span class="text-xs">History</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">person</span>
            <span class="text-xs">Profile</span>
        </a>
    </div>
</div>

<?= global_route_script() ?>
</body>
</html>