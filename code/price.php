<?php
require_once 'common.php';
$user = currentUser();
if (!$user) header('Location: login.php');

$services = get_services();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Pricing - LaundryApp</title>
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
        <a href="topup.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">wallet</span>
            <span>Top Up</span>
        </a>
        <a href="price.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
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
        <span class="text-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Harga Layanan</span>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">Daftar Harga Layanan</h1>

        <div class="grid md:grid-cols-2 gap-6">
            <?php foreach ($services as $service): ?>
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden hover-lift transition">
                <div class="bg-gradient-to-r from-primary to-secondary p-4 text-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold"><?= htmlspecialchars($service['nama_layanan']) ?></h3>
                        <span class="material-symbols-outlined text-3xl">local_laundry_service</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="text-center mb-4">
                        <p class="text-4xl font-bold text-primary">Rp <?= number_format($service['harga_per_kg'],0,',','.') ?></p>
                        <p class="text-gray-500 dark:text-gray-400">per kilogram</p>
                    </div>
                    <div class="space-y-2 mb-6">
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                            <span class="material-symbols-outlined text-primary text-sm">schedule</span>
                            <span class="text-sm">Estimasi: <?= $service['estimasi_hari'] ?> hari</span>
                        </div>
                        <?php if ($service['deskripsi']): ?>
                        <div class="flex items-start gap-2 text-gray-600 dark:text-gray-300">
                            <span class="material-symbols-outlined text-primary text-sm">description</span>
                            <span class="text-sm"><?= htmlspecialchars($service['deskripsi']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <a href="order_detail.php?service_id=<?= $service['id_layanan'] ?>" class="block w-full bg-gradient-to-r from-primary to-secondary text-white text-center py-3 rounded-xl font-semibold hover-lift transition">
                        Pilih Layanan Ini
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
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