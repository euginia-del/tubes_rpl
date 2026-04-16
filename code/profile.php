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
$role = $user['role'];
$userId = $user['id_user'];

// ========== MY ORDER STATISTICS (HANYA UNTUK CUSTOMER) ==========
$myTotalOrders = 0;
$myPendingOrders = 0;
$myProcessOrders = 0;
$myCompletedOrders = 0;
$myTotalSpent = 0;
$myActiveOrders = 0;

if ($role === 'customer') {
    $stmt = $db->prepare('SELECT * FROM orders WHERE id_user = ? ORDER BY tanggal_order DESC');
    $stmt->execute([$userId]);
    $myOrders = $stmt->fetchAll();
    
    $myTotalOrders = count($myOrders);
    foreach ($myOrders as $order) {
        if ($order['status_order'] === 'pending') {
            $myPendingOrders++;
        } elseif ($order['status_order'] === 'proses') {
            $myProcessOrders++;
        } elseif ($order['status_order'] === 'selesai') {
            $myCompletedOrders++;
            $myTotalSpent += $order['harga_snapshot'];
        }
    }
    $myActiveOrders = $myPendingOrders + $myProcessOrders;
}

// ========== SYSTEM OVERVIEW (UNTUK ADMIN/SUPERVISOR/WORKER) ==========
$allTotalOrders = 0;
$allPendingOrders = 0;
$allProcessOrders = 0;
$allCompletedOrders = 0;
$allTotalRevenue = 0;

if ($role !== 'customer') {
    $stmt = $db->query('SELECT * FROM orders ORDER BY tanggal_order DESC');
    $allOrders = $stmt->fetchAll();
    
    $allTotalOrders = count($allOrders);
    foreach ($allOrders as $order) {
        if ($order['status_order'] === 'pending') $allPendingOrders++;
        elseif ($order['status_order'] === 'proses') $allProcessOrders++;
        elseif ($order['status_order'] === 'selesai') {
            $allCompletedOrders++;
            $allTotalRevenue += $order['harga_snapshot'];
        }
    }
}
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
<style>
.stat-card-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
.stat-card-amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.stat-card-purple { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
.stat-card-emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.stat-card-sky { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
.stat-card-rose { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); }
</style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-900 dark:to-slate-800 min-h-screen pb-20 md:pb-0">

<!-- Desktop Sidebar -->
<div class="hidden md:flex md:fixed md:inset-y-0 md:left-0 md:w-72 bg-white dark:bg-slate-800 shadow-xl flex-col">
    <div class="flex items-center justify-center p-6 border-b dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">local_laundry_service</span>
            </div>
            <span class="text-xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                <?= $role === 'admin' ? 'Admin Panel' : ($role === 'supervisor' ? 'Supervisor Panel' : ($role === 'worker' ? 'Worker Panel' : 'LaundryFresh')) ?>
            </span>
        </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-2">
        <?php if ($role === 'customer'): ?>
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
        <?php elseif ($role === 'worker'): ?>
            <a href="worker.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
        <?php elseif ($role === 'supervisor'): ?>
            <a href="supervisor.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                <span class="material-symbols-outlined">verified</span>
                <span>Verify Payments</span>
            </a>
            <a href="reports.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                <span class="material-symbols-outlined">assessment</span>
                <span>Reports</span>
            </a>
        <?php elseif ($role === 'admin'): ?>
            <a href="admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a href="reports.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                <span class="material-symbols-outlined">assessment</span>
                <span>Reports</span>
            </a>
            <a href="history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                <span class="material-symbols-outlined">receipt_long</span>
                <span>All Orders</span>
            </a>
            <a href="price.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                <span class="material-symbols-outlined">price_check</span>
                <span>Services</span>
            </a>
            <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                <span class="material-symbols-outlined">group</span>
                <span>Users</span>
            </a>
        <?php endif; ?>
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
                <p class="text-xs text-gray-500 dark:text-gray-400 capitalize"><?= $role ?></p>
            </div>
            <button id="themeToggle" class="text-xs px-2 py-1 rounded-full border dark:border-slate-600">🌙</button>
        </div>
    </div>
</div>

<!-- Mobile Header -->
<div class="md:hidden bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-40">
    <div class="flex items-center justify-between px-4 py-3">
        <span class="text-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">My Profile</span>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6 max-w-5xl mx-auto">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">My Profile</h1>

        <!-- Profile Info -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-4xl">account_circle</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($user['nama']) ?></h2>
                    <p class="text-gray-500 dark:text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="badge <?= $role == 'admin' ? 'badge-success' : ($role == 'supervisor' ? 'badge-info' : 'badge-secondary') ?> mt-1">
                        <?= strtoupper($role) ?>
                    </span>
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-4 mt-4 pt-4 border-t dark:border-slate-700">
                <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="font-semibold"><?= htmlspecialchars($user['no_hp'] ?? 'Not set') ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Address</p>
                    <p class="font-semibold"><?= htmlspecialchars($user['alamat'] ?? 'Not set') ?></p>
                </div>
            </div>
        </div>

        <!-- ========== UNTUK CUSTOMER: MY ORDER STATISTICS ========== -->
        <?php if ($role === 'customer'): ?>
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">My Order Statistics</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-card-blue rounded-2xl p-5 text-white shadow-lg transform transition hover:scale-105">
                <p class="text-blue-100 text-sm">Total Orders</p>
                <p class="text-3xl font-bold"><?= $myTotalOrders ?></p>
            </div>
            <div class="stat-card-amber rounded-2xl p-5 text-white shadow-lg transform transition hover:scale-105">
                <p class="text-amber-100 text-sm">Pending</p>
                <p class="text-3xl font-bold"><?= $myPendingOrders ?></p>
            </div>
            <div class="stat-card-purple rounded-2xl p-5 text-white shadow-lg transform transition hover:scale-105">
                <p class="text-purple-100 text-sm">In Process</p>
                <p class="text-3xl font-bold"><?= $myProcessOrders ?></p>
            </div>
            <div class="stat-card-emerald rounded-2xl p-5 text-white shadow-lg transform transition hover:scale-105">
                <p class="text-emerald-100 text-sm">Completed</p>
                <p class="text-3xl font-bold"><?= $myCompletedOrders ?></p>
            </div>
        </div>

        <!-- Additional Stats for Customer -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="stat-card-sky rounded-2xl p-5 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sky-100 text-sm">Total Spent</p>
                        <p class="text-2xl font-bold">Rp <?= number_format($myTotalSpent, 0, ',', '.') ?></p>
                    </div>
                    <span class="material-symbols-outlined text-4xl text-white/30">payments</span>
                </div>
            </div>
            <div class="stat-card-rose rounded-2xl p-5 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-rose-100 text-sm">Active Orders</p>
                        <p class="text-2xl font-bold"><?= $myActiveOrders ?></p>
                    </div>
                    <span class="material-symbols-outlined text-4xl text-white/30">local_shipping</span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ========== UNTUK ADMIN/SUPERVISOR/WORKER: SYSTEM OVERVIEW ========== -->
        <?php if ($role !== 'customer'): ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">insights</span>
                System Overview (All Orders)
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl text-white">
                    <p class="text-3xl font-bold"><?= $allTotalOrders ?></p>
                    <p class="text-sm text-blue-100">Total Orders</p>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl text-white">
                    <p class="text-3xl font-bold"><?= $allPendingOrders ?></p>
                    <p class="text-sm text-amber-100">Pending</p>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl text-white">
                    <p class="text-3xl font-bold"><?= $allProcessOrders ?></p>
                    <p class="text-sm text-purple-100">In Process</p>
                </div>
                <div class="text-center p-4 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl text-white">
                    <p class="text-3xl font-bold"><?= $allCompletedOrders ?></p>
                    <p class="text-sm text-emerald-100">Completed</p>
                </div>
            </div>
            
            <?php if ($role === 'admin' || $role === 'supervisor'): ?>
            <div class="mt-4 p-4 bg-gradient-to-r from-primary to-secondary rounded-xl text-white">
                <div class="flex justify-between items-center">
                    <span class="font-semibold">Total Revenue (All Orders)</span>
                    <span class="text-2xl font-bold">Rp <?= number_format($allTotalRevenue, 0, ',', '.') ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Logout Button -->
        <form method="post">
            <button type="submit" name="logout" class="w-full flex items-center justify-center gap-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl">
                <span class="material-symbols-outlined">logout</span>
                <span>Logout</span>
            </button>
        </form>
    </div>
</div>

<?= global_route_script() ?>
</body>
</html>