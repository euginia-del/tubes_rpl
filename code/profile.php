<?php
require_once 'common.php';

$user = currentUser();
if (!$user) { 
    header('Location: login.php'); 
    exit; 
}

if (isset($_POST['logout'])) {
    logout_user();
}

$db = get_db();
$stmt = $db->prepare('SELECT COUNT(*) FROM orders WHERE id_user = ?');
$stmt->execute([$user['id_user']]);
$totalOrders = $stmt->fetchColumn();

$stmt = $db->prepare('SELECT SUM(harga_snapshot) as total_spent FROM orders WHERE id_user = ? AND status_order = "selesai"');
$stmt->execute([$user['id_user']]);
$totalSpent = $stmt->fetchColumn() ?? 0;

$stmt = $db->prepare('SELECT COUNT(*) FROM orders WHERE id_user = ? AND status_order IN ("pending", "proses")');
$stmt->execute([$user['id_user']]);
$activeOrders = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>My Profile - LaundryApp</title>
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
        <a href="history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">receipt_long</span>
            <span>History</span>
        </a>
        <a href="price.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">price_check</span>
            <span>Pricing</span>
        </a>
        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
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
                <p class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($user['role']) ?></p>
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
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">My Profile</h1>

        <?php if ($msg = get_flash('success')): ?>
        <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 rounded-xl p-4">
            <p class="text-emerald-700 dark:text-emerald-300"><?= htmlspecialchars($msg) ?></p>
        </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Profile Info Card -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden card-animate">
                    <div class="bg-gradient-to-r from-primary to-secondary p-6 text-center">
                        <div class="w-28 h-28 mx-auto rounded-full bg-white/20 backdrop-blur flex items-center justify-center border-4 border-white/30">
                            <span class="material-symbols-outlined text-white text-6xl">account_circle</span>
                        </div>
                        <h3 class="text-white text-xl font-bold mt-4"><?= htmlspecialchars($user['nama']) ?></h3>
                        <p class="text-white/80 text-sm"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <span class="material-symbols-outlined text-primary">call</span>
                            <span class="text-sm"><?= htmlspecialchars($user['no_hp'] ?? 'Not set') ?></span>
                        </div>
                        <div class="flex items-start gap-3 text-gray-600 dark:text-gray-300">
                            <span class="material-symbols-outlined text-primary">home</span>
                            <span class="text-sm flex-1"><?= htmlspecialchars($user['alamat'] ?? 'Not set') ?></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary">badge</span>
                            <span class="badge badge-info"><?= strtoupper($user['role']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="lg:col-span-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 text-white shadow-lg card-animate">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Total Orders</p>
                                <p class="text-3xl font-bold mt-1"><?= $totalOrders ?></p>
                            </div>
                            <span class="material-symbols-outlined text-4xl text-white/30">receipt_long</span>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg card-animate">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm">Total Spent</p>
                                <p class="text-2xl font-bold mt-1">Rp <?= number_format($totalSpent, 0, ',', '.') ?></p>
                            </div>
                            <span class="material-symbols-outlined text-4xl text-white/30">payments</span>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg card-animate">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-emerald-100 text-sm">Active Orders</p>
                                <p class="text-3xl font-bold mt-1"><?= $activeOrders ?></p>
                            </div>
                            <span class="material-symbols-outlined text-4xl text-white/30">local_shipping</span>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-5 text-white shadow-lg card-animate">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-amber-100 text-sm">Completed</p>
                                <p class="text-3xl font-bold mt-1"><?= $totalOrders - $activeOrders ?></p>
                            </div>
                            <span class="material-symbols-outlined text-4xl text-white/30">celebration</span>
                        </div>
                    </div>
                </div>

                <!-- Logout Button -->
                <form method="post" class="card-animate">
                    <button type="submit" name="logout" class="w-full flex items-center justify-center gap-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl">
                        <span class="material-symbols-outlined">logout</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
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
        <a href="history.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">receipt_long</span>
            <span class="text-xs">History</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center gap-1 text-primary">
            <span class="material-symbols-outlined">person</span>
            <span class="text-xs">Profile</span>
        </a>
    </div>
</div>

<?= global_route_script() ?>
</body>
</html>