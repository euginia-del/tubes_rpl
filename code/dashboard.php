<?php
require_once 'common.php';
$user = currentUser();
if (!$user || $user['role'] !== 'customer') { 
    header('Location: login.php'); 
    exit; 
}

$db = get_db();

// Get user saldo
$stmt = $db->prepare('SELECT saldo FROM user WHERE id_user = ?');
$stmt->execute([$user['id_user']]);
$saldo = $stmt->fetchColumn();

$stmt = $db->prepare('SELECT o.*, l.nama_layanan as service_name 
    FROM orders o 
    LEFT JOIN layanan l ON o.id_layanan = l.id_layanan 
    WHERE o.id_user = ? 
    ORDER BY o.tanggal_order DESC 
    LIMIT 5');
$stmt->execute([$user['id_user']]);
$recentOrders = $stmt->fetchAll();

$stmt = $db->prepare('SELECT COUNT(*) FROM orders WHERE id_user = ? AND status_order != "selesai"');
$stmt->execute([$user['id_user']]);
$activeCount = $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM orders WHERE id_user = ?');
$stmt->execute([$user['id_user']]);
$totalOrders = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Dashboard - LaundryApp</title>
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
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <a href="neworder.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">add_shopping_cart</span>
            <span>New Order</span>
        </a>
        <a href="history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">receipt_long</span>
            <span>History</span>
        </a>
        <a href="topup.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">wallet</span>
            <span>Top Up</span>
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
                <p class="text-sm font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($user['nama']) ?></p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Customer</p>
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
                <span class="material-symbols-outlined text-white text-lg">local_laundry_service</span>
            </div>
            <span class="text-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">LaundryFresh</span>
        </div>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">Welcome back, <?= htmlspecialchars($user['nama']) ?>! 👋</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Ready to do your laundry today?</p>
        </div>

        <!-- Saldo Card -->
        <div class="bg-gradient-to-r from-primary to-secondary rounded-2xl p-5 text-white shadow-lg mb-8 card-animate">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm">Saldo Anda</p>
                    <p class="text-3xl font-bold mt-1">Rp <?= number_format($saldo, 0, ',', '.') ?></p>
                </div>
                <div class="flex gap-2">
                    <a href="topup.php" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl text-sm font-semibold transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">add</span>
                        Top Up
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 text-white shadow-lg card-animate">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm">Active Orders</p>
                        <p class="text-3xl font-bold mt-1"><?= $activeCount ?></p>
                    </div>
                    <span class="material-symbols-outlined text-4xl text-white/30">local_shipping</span>
                </div>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg card-animate">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm">Total Orders</p>
                        <p class="text-3xl font-bold mt-1"><?= $totalOrders ?></p>
                    </div>
                    <span class="material-symbols-outlined text-4xl text-white/30">receipt_long</span>
                </div>
            </div>
        </div>

        <!-- New Order Button -->
        <a href="neworder.php" class="block mb-8 bg-gradient-to-r from-primary to-secondary hover:from-primary-dark hover:to-secondary-dark text-white p-5 rounded-2xl shadow-lg transition-all text-center card-animate">
            <div class="flex items-center justify-center gap-3">
                <span class="material-symbols-outlined text-2xl">add</span>
                <span class="font-bold text-lg">Create New Order</span>
            </div>
        </a>

        <!-- Recent Orders -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Recent Orders</h2>
                <a href="history.php" class="text-primary text-sm hover:underline">View All →</a>
            </div>
            
            <div class="space-y-3">
                <?php if (empty($recentOrders)): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 text-center">
                    <span class="material-symbols-outlined text-5xl text-gray-400">inbox</span>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">No orders yet</p>
                    <a href="neworder.php" class="text-primary mt-2 inline-block">Start your first order →</a>
                </div>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm hover:shadow-md transition card-animate">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-bold text-gray-800 dark:text-white">#<?= $order['id_order'] ?></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400"><?= $order['service_name'] ?? 'Layanan' ?> • <?= $order['berat_cucian'] ?> kg</p>
                            </div>
                            <span class="badge <?= $order['status_order'] == 'selesai' ? 'badge-success' : ($order['status_order'] == 'proses' ? 'badge-info' : 'badge-warning') ?>">
                                <?= $order['status_order'] ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <p class="text-primary font-bold">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></p>
                            <a href="order_details_process.php?order=<?= $order['id_order'] ?>" class="text-sm text-primary hover:underline">Detail →</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav md:hidden">
    <div class="flex justify-around">
        <a href="dashboard.php" class="flex flex-col items-center gap-1 text-primary">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="text-xs">Home</span>
        </a>
        <a href="neworder.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">add_shopping_cart</span>
            <span class="text-xs">New</span>
        </a>
        <a href="history.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">receipt_long</span>
            <span class="text-xs">History</span>
        </a>
        <a href="topup.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">wallet</span>
            <span class="text-xs">Top Up</span>
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