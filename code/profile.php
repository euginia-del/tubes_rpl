<?php
require_once 'common.php';

$user = currentUser();
if (!$user) { 
    header('Location: login.php'); 
    exit; 
}

// Handle logout
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
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "primary": "#6366f1",
                "primary-dark": "#4f46e5",
                "secondary": "#8b5cf6",
                "background-light": "#f9fafb",
                "background-dark": "#0f172a",
            },
            fontFamily: {
                "display": ["Inter", "sans-serif"]
            },
            animation: {
                'gradient': 'gradient 3s ease infinite',
            },
            keyframes: {
                gradient: {
                    '0%, 100%': { 'background-size': '200% 200%', 'background-position': 'left center' },
                    '50%': { 'background-size': '200% 200%', 'background-position': 'right center' },
                }
            }
        }
    }
}
</script>
<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    .float-animation {
        animation: float 3s ease-in-out infinite;
    }
    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        background-size: 200% 200%;
        animation: gradient 3s ease infinite;
    }
</style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-900 dark:to-slate-800 font-display min-h-screen">

<!-- Desktop Container -->
<div class="container-custom mx-auto px-4 py-6 md:py-10 max-w-7xl">
    
    <!-- Header -->
    <div class="text-center mb-8 md:mb-12">
        <div class="inline-flex items-center justify-center w-20 h-20 md:w-24 md:h-24 rounded-full gradient-bg shadow-xl float-animation mb-4">
            <span class="material-symbols-outlined text-white text-4xl md:text-5xl">local_laundry_service</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
            LaundryFresh
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2">Manage your account</p>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto">
        
        <!-- Flash Messages -->
        <?php if ($msg = get_flash('success')): ?>
        <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 animate-card">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">check_circle</span>
                <p class="text-emerald-800 dark:text-emerald-200 font-medium"><?= htmlspecialchars($msg) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error = get_flash('error')): ?>
        <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 animate-card">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400">error</span>
                <p class="text-red-800 dark:text-red-200 font-medium"><?= htmlspecialchars($error) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Profile Grid -->
        <div class="grid md:grid-cols-3 gap-6">
            
            <!-- Profile Card -->
            <div class="md:col-span-1">
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden hover-lift transition-all duration-300">
                    <div class="gradient-bg p-6 text-center">
                        <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-white/20 backdrop-blur border-4 border-white/30">
                            <span class="material-symbols-outlined text-white text-6xl">account_circle</span>
                        </div>
                        <h3 class="text-white text-xl font-bold mt-4"><?= htmlspecialchars($user['nama'] ?? 'User') ?></h3>
                        <p class="text-white/80 text-sm">Member since <?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></p>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <span class="material-symbols-outlined text-primary">mail</span>
                            <span class="text-sm"><?= htmlspecialchars($user['email']) ?></span>
                        </div>
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
                            <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-semibold"><?= strtoupper(htmlspecialchars($user['role'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="md:col-span-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Total Orders -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl hover-lift transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm">Total Orders</p>
                                <p class="text-4xl font-bold mt-2"><?= $totalOrders ?></p>
                            </div>
                            <span class="material-symbols-outlined text-5xl text-white/30">receipt_long</span>
                        </div>
                    </div>

                    <!-- Total Spent -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-xl hover-lift transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm">Total Spent</p>
                                <p class="text-4xl font-bold mt-2">Rp <?= number_format($totalSpent, 0, ',', '.') ?></p>
                            </div>
                            <span class="material-symbols-outlined text-5xl text-white/30">payments</span>
                        </div>
                    </div>

                    <!-- Active Orders -->
                    <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white shadow-xl hover-lift transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-emerald-100 text-sm">Active Orders</p>
                                <p class="text-4xl font-bold mt-2">
                                    <?php 
                                        $stmt = $db->prepare('SELECT COUNT(*) FROM orders WHERE id_user = ? AND status_order IN ("pending", "proses")');
                                        $stmt->execute([$user['id_user']]);
                                        echo $stmt->fetchColumn();
                                    ?>
                                </p>
                            </div>
                            <span class="material-symbols-outlined text-5xl text-white/30">local_shipping</span>
                        </div>
                    </div>

                    <!-- Completed Orders -->
                    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-xl hover-lift transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-amber-100 text-sm">Completed</p>
                                <p class="text-4xl font-bold mt-2">
                                    <?php 
                                        $stmt = $db->prepare('SELECT COUNT(*) FROM orders WHERE id_user = ? AND status_order = "selesai"');
                                        $stmt->execute([$user['id_user']]);
                                        echo $stmt->fetchColumn();
                                    ?>
                                </p>
                            </div>
                            <span class="material-symbols-outlined text-5xl text-white/30">celebration</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <a href="dashboard.php" class="flex items-center justify-center gap-2 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-md hover:shadow-lg transition-all hover-lift">
                        <span class="material-symbols-outlined text-primary">dashboard</span>
                        <span class="font-semibold text-gray-700 dark:text-gray-200">Dashboard</span>
                    </a>
                    <a href="neworder.php" class="flex items-center justify-center gap-2 bg-primary text-white p-4 rounded-xl shadow-md hover:shadow-lg transition-all hover-lift">
                        <span class="material-symbols-outlined">add_shopping_cart</span>
                        <span class="font-semibold">New Order</span>
                    </a>
                </div>

                <!-- Logout Button -->
                <form method="post" class="mt-6">
                    <button type="submit" name="logout" class="w-full flex items-center justify-center gap-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl">
                        <span class="material-symbols-outlined">logout</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= global_route_script() ?>
</body>
</html>