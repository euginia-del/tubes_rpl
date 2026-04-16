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
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Worker Dashboard - LaundryApp</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="style.css">
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: { "primary": "#f97316", "secondary": "#ef4444" },
            fontFamily: { "display": ["Inter", "sans-serif"] }
        }
    }
}
</script>
</head>
<body class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-slate-900 dark:to-slate-800 min-h-screen pb-20 md:pb-0">

<!-- Desktop Sidebar -->
<div class="hidden md:flex md:fixed md:inset-y-0 md:left-0 md:w-72 bg-white dark:bg-slate-800 shadow-xl flex-col">
    <div class="flex items-center justify-center p-6 border-b dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">handyman</span>
            </div>
            <span class="text-xl font-bold text-primary">Worker Panel</span>
        </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-2">
        <a href="worker.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">account_circle</span>
            <span>Profile</span>
        </a>
    </nav>
    
    <div class="p-4 border-t dark:border-slate-700">
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">engineering</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 dark:text-white">Worker</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Staff Access</p>
            </div>
            <button id="themeToggle" class="text-xs px-2 py-1 rounded-full border dark:border-slate-600">🌙</button>
        </div>
    </div>
</div>

<!-- Mobile Header -->
<div class="md:hidden bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-40">
    <div class="flex items-center justify-between px-4 py-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-lg">handyman</span>
            </div>
            <span class="text-lg font-bold text-primary">Worker Panel</span>
        </div>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">Worker Dashboard</h1>

        <?php if ($msg = get_flash('success')): ?>
        <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 rounded-xl p-4">
            <p class="text-emerald-700 dark:text-emerald-300"><?= htmlspecialchars($msg) ?></p>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-5 text-white shadow-lg card-animate">
                <div>
                    <p class="text-amber-100 text-sm">Pending Orders</p>
                    <p class="text-3xl font-bold mt-1"><?= count($pendingOrders) ?></p>
                </div>
            </div>
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg card-animate">
                <div>
                    <p class="text-emerald-100 text-sm">In Progress</p>
                    <p class="text-3xl font-bold mt-1"><?= count(array_filter($pendingOrders, fn($o) => $o['status_order'] === 'proses')) ?></p>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Pending Orders</h2>
            <div class="space-y-3">
                <?php foreach ($pendingOrders as $order): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm card-animate">
                    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                        <p class="font-bold text-gray-800 dark:text-white">#<?= $order['id_order'] ?></p>
                        <span class="badge badge-warning">pending</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Customer: <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-3"><?= $order['service_name'] ?? 'Layanan' ?> • <?= $order['berat_cucian'] ?> kg</p>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?= $order['id_order'] ?>">
                        <input type="hidden" name="action" value="process_order">
                        <button class="w-full bg-gradient-to-r from-primary to-secondary text-white py-3 rounded-xl font-semibold hover-lift transition">Process Order</button>
                    </form>
                </div>
                <?php endforeach; ?>
                <?php if (empty($pendingOrders)): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 text-center">
                    <span class="material-symbols-outlined text-5xl text-gray-400">check_circle</span>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">No pending orders</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Orders In Progress -->
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Orders In Progress</h2>
            <div class="space-y-3">
                <?php 
                $inProgress = array_filter($pendingOrders, fn($o) => $o['status_order'] === 'proses');
                foreach ($inProgress as $order): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm card-animate">
                    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                        <p class="font-bold text-gray-800 dark:text-white">#<?= $order['id_order'] ?></p>
                        <span class="badge badge-info">proses</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-3">Customer: <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?= $order['id_order'] ?>">
                        <input type="hidden" name="action" value="complete_order">
                        <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl font-semibold transition">Complete Order</button>
                    </form>
                </div>
                <?php endforeach; ?>
                <?php if (empty($inProgress)): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 text-center">
                    <span class="material-symbols-outlined text-5xl text-gray-400">pending</span>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">No orders in progress</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav md:hidden">
    <div class="flex justify-around">
        <a href="worker.php" class="flex flex-col items-center gap-1 text-primary">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="text-xs">Home</span>
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