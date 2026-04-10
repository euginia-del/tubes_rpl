<?php
require_once 'common.php';
$user = currentUser();
if (!$user || $user['role'] !== 'customer') header('Location: login.php');

$success = get_flash('success');
$error = get_flash('error');
$filter = $_GET['filter'] ?? 'all';
$orders = get_orders();
$filteredOrders = $orders;
if ($filter === 'in_progress') {
    $filteredOrders = array_filter($orders, fn($o) => in_array($o['status'], ['In Progress', 'Pending Pickup']));
} elseif ($filter === 'completed') {
    $filteredOrders = array_filter($orders, fn($o) => $o['status'] === 'Completed');
}
$displayOrders = $filteredOrders;
?>

<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#3398db",
                        "background-light": "#f6f7f8",
                        "background-dark": "#121a20",
                    },
                    fontFamily: {
                        "display": ["Inter"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
<title>Order History - Laundry Services</title>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 antialiased">
<div class="relative flex min-h-screen w-full flex-col max-w-md mx-auto bg-background-light dark:bg-background-dark shadow-xl">
<!-- Header Section -->
<div class="sticky top-0 z-10 flex items-center bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md p-4 justify-between border-b border-slate-200 dark:border-slate-800">
<div class="text-slate-900 dark:text-slate-100 flex size-10 items-center justify-center rounded-full hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors cursor-pointer">
<span class="material-symbols-outlined">back</span>
</div>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-tight flex-1 ml-2">Order History</h2>
<div class="flex items-center justify-end gap-2">
<button id="themeToggle" class="text-slate-500 dark:text-slate-200 text-xs font-semibold px-2 py-1 rounded-full border border-slate-200 dark:border-slate-700">Mode</button>
<button class="flex size-10 items-center justify-center rounded-full hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
<span class="material-symbols-outlined text-slate-900 dark:text-slate-100">search</span>
</button>
</div>
</div>
<!-- Tabs Section -->
<div class="bg-background-light dark:bg-background-dark sticky top-[73px] z-10">
<div class="flex border-b border-slate-200 dark:border-slate-800 px-4 gap-6">
<a class="flex flex-col items-center justify-center border-b-2 <?= $filter === 'all' ? 'border-primary text-primary' : 'border-transparent text-slate-500 dark:text-slate-400' ?> pb-3 pt-4" href="?filter=all">
<p class="text-sm font-bold">All Orders</p>
</a>
<a class="flex flex-col items-center justify-center border-b-2 <?= $filter === 'in_progress' ? 'border-primary text-primary' : 'border-transparent text-slate-500 dark:text-slate-400' ?> pb-3 pt-4" href="?filter=in_progress">
<p class="text-sm font-bold">In Progress</p>
</a>
<a class="flex flex-col items-center justify-center border-b-2 <?= $filter === 'completed' ? 'border-primary text-primary' : 'border-transparent text-slate-500 dark:text-slate-400' ?> pb-3 pt-4" href="?filter=completed">
<p class="text-sm font-bold">Completed</p>
</a>
</div>
</div>
<!-- Orders List -->
<div class="p-4">
    <?php if (!empty($success)): ?>
        <div class="mb-4 rounded-lg bg-emerald-100 text-emerald-800 p-3 border border-emerald-200">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="mb-4 rounded-lg bg-red-100 text-red-800 p-3 border border-red-200">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($displayOrders)): ?>
        <div class="rounded-xl bg-white dark:bg-slate-900 p-4 border border-slate-200 dark:border-slate-800 text-center text-sm text-slate-500">Tidak ada order saat ini untuk filter ini.</div>
    <?php endif; ?>
</div>
<div class="flex flex-col gap-4 p-4 pb-24">
    <?php foreach ($displayOrders as $orderId => $order): ?>
        <div class="flex flex-col gap-4 rounded-xl bg-white dark:bg-slate-900 p-4 shadow-sm border border-slate-100 dark:border-slate-800">
            <div class="flex justify-between items-start">
                <div class="flex flex-col gap-1">
                    <p class="text-slate-500 dark:text-slate-400 text-xs font-medium"><?= htmlspecialchars($order['created_at'] ?? 'Unknown') ?></p>
                    <p class="text-slate-900 dark:text-slate-100 text-base font-bold">Order <?= htmlspecialchars($order['id'] ?? $orderId) ?></p>
                    <p class="text-slate-600 dark:text-slate-300 text-sm">Kategori: <?= htmlspecialchars($order['category'] ?? 'N/A') ?>, Slot: <?= htmlspecialchars($order['pickup_time'] ?? 'N/A') ?></p>
                </div>
                <div class="flex h-7 items-center justify-center gap-1.5 rounded-full bg-blue-100 dark:bg-blue-900/30 px-3 text-blue-600 dark:text-blue-400">
                    <span class="material-symbols-outlined text-[16px]">cycle</span>
                    <p class="text-xs font-bold uppercase tracking-wider"><?= htmlspecialchars($order['status'] ?? 'Pending') ?></p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="h-24 w-24 bg-center bg-no-repeat bg-cover rounded-lg flex-shrink-0" style="background-image: url('https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&w=400&q=60');"></div>
                <div class="flex flex-col justify-between flex-1 py-1">
                    <div class="flex flex-col gap-0.5">
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Alamat Pickup</p>
                        <p class="text-sm text-slate-900 dark:text-slate-100 font-semibold"><?= htmlspecialchars($order['address'] ?? 'Tidak tersedia') ?></p>
                    </div>
                    <a href="order_detail_process.php?order=<?= urlencode($order['id'] ?? $orderId) ?>" class="inline-flex items-center justify-center rounded-lg h-9 px-4 bg-primary text-white text-sm font-bold hover:bg-primary/90">Track Order</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="flex flex-col gap-4 rounded-xl bg-white dark:bg-slate-900 p-4 shadow-sm border border-slate-100 dark:border-slate-800 opacity-90">
<div class="flex justify-between items-start">
<div class="flex flex-col gap-1">
<p class="text-slate-500 dark:text-slate-400 text-xs font-medium">Oct 24, 10:30 AM</p>
<p class="text-slate-900 dark:text-slate-100 text-base font-bold">Order #LAU-9755</p>
<p class="text-slate-600 dark:text-slate-300 text-sm">1x Delicate Dry Cleaning</p>
</div>
<div class="flex h-7 items-center justify-center gap-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-3 text-emerald-600 dark:text-emerald-400">
<span class="material-symbols-outlined text-[16px]">check_circle</span>
<p class="text-xs font-bold uppercase tracking-wider">Completed</p>
</div>
</div>
<div class="flex gap-4">
<div class="h-24 w-24 bg-center bg-no-repeat bg-cover rounded-lg flex-shrink-0" data-alt="Clothes hanging in a neat dry cleaner rack" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDPc8R-3q3BAK6hx8EDOTk6aBtaundmT9dXAJ1hQKiPPcaef0uTlVaytutsflv9PeObJpBSwYFnT-oazXXBLQYLaWE-S0qHdEXS0eqhmEAEXeyD20TonDJ7UHnngqVVuMamSO-kPiBO9oe1ZEwgDmJOjYxXCs8SyM3u7VQNSmorpkU8WSi6TEOc2FO9rsfodxV4Fl5XLAWBDQrzrDRESROswIZ5Jkd6JKW2-g9TJH8YOB1xFrCDEy6tdTJ2NYI7oODvuaBBwXwvqHM");'></div>
<div class="flex flex-col justify-between flex-1 py-1">
<div class="flex flex-col gap-0.5">
<p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Paid</p>
<p class="text-sm text-slate-900 dark:text-slate-100 font-semibold">$34.50</p>
</div>
<button class="flex items-center justify-center rounded-lg h-9 px-4 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm font-bold transition-all hover:bg-slate-200 dark:hover:bg-slate-700 w-full sm:w-fit">
                            Reorder
                        </button>
</div>
</div>
</div>
<!-- Order Card 3: Pending -->
<div class="flex flex-col gap-4 rounded-xl bg-white dark:bg-slate-900 p-4 shadow-sm border border-slate-100 dark:border-slate-800">
<div class="flex justify-between items-start">
<div class="flex flex-col gap-1">
<p class="text-slate-500 dark:text-slate-400 text-xs font-medium">Oct 20, 02:45 PM</p>
<p class="text-slate-900 dark:text-slate-100 text-base font-bold">Order #LAU-9702</p>
<p class="text-slate-600 dark:text-slate-300 text-sm">3x Regular Wash &amp; Iron</p>
</div>
<div class="flex h-7 items-center justify-center gap-1.5 rounded-full bg-slate-100 dark:bg-slate-800 px-3 text-slate-600 dark:text-slate-400">
<span class="material-symbols-outlined text-[16px]">schedule</span>
<p class="text-xs font-bold uppercase tracking-wider">Pending</p>
</div>
</div>
<div class="flex gap-4">
<div class="h-24 w-24 bg-center bg-no-repeat bg-cover rounded-lg flex-shrink-0" data-alt="Laundry detergent and clean fabrics" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBw564k7PLhaKD-3Sz4KID9jI7cB89v6gjYsfWQMdhB4RypZYcYp_dVH3EtR2dIUICVrlQ1Wdz6srw67tK9OLrySh1tBCF7THxIE2-YkmeDkWxvR1AAWKB5BRxzuiS8ma4kUdqk5exQ80Vse_dVQYkNUmnkLHdVVdZIBcMSJPjNuCjMo9hVWCGJ1RK_TXXgXl3u_tLtC-ZbEpVSe6355z9PL9geOKBJfQ215qS0Hk7Y4rJgBCmA1MOJFOhiFossNPjRAmqhH8X4njw");'></div>
<div class="flex flex-col justify-between flex-1 py-1">
<div class="flex flex-col gap-0.5">
<p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Status</p>
<p class="text-sm text-slate-900 dark:text-slate-100 font-semibold">Awaiting Pickup</p>
</div>
<button class="flex items-center justify-center rounded-lg h-9 px-4 bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-sm font-bold transition-all hover:bg-slate-200 dark:hover:bg-slate-700 w-full sm:w-fit">
                            View Details
                        </button>
</div>
</div>
</div>
</div>
<!-- Bottom Navigation Bar -->
<div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 px-4 pb-4 pt-2 flex justify-around items-center z-50">
<a class="flex flex-col items-center gap-1 text-slate-500 dark:text-slate-400 transition-colors hover:text-primary" href="dashboard.php">
<span class="material-symbols-outlined">home</span>
<span class="text-[10px] font-bold uppercase tracking-widest">Home</span>
</a>
<a class="flex flex-col items-center gap-1 text-primary transition-colors" href="history.php">
<span class="material-symbols-outlined fill-1">receipt_long</span>
<span class="text-[10px] font-bold uppercase tracking-widest">Orders</span>
</a>
<a class="flex flex-col items-center gap-1 text-slate-500 dark:text-slate-400 transition-colors hover:text-primary" href="price.php">
<span class="material-symbols-outlined">payments</span>
<span class="text-[10px] font-bold uppercase tracking-widest">Pricing</span>
</a>
<a class="flex flex-col items-center gap-1 text-slate-500 dark:text-slate-400 transition-colors hover:text-primary" href="profile.php">
<span class="material-symbols-outlined">person</span>
<span class="text-[10px] font-bold uppercase tracking-widest">Profile</span>
</a>
</div>
</div>
<?php echo global_route_script(); ?>
</body></html>

