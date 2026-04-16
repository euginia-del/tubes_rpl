<?php
require_once 'common.php';
require_supervisor();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_order_id'])) {
    $orderId = $_POST['verify_order_id'];
    if (verify_payment($orderId)) {
        set_flash('success', 'Payment verified for order #' . $orderId);
    } else {
        set_flash('error', 'Failed to verify payment.');
    }
    header('Location: supervisor.php');
    exit;
}

$pendingOrders = get_pending_orders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Supervisor - LaundryApp</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="style.css">
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: { "primary": "#06b6d4", "secondary": "#0891b2" },
            fontFamily: { "display": ["Inter", "sans-serif"] }
        }
    }
}
</script>
</head>
<body class="bg-gradient-to-br from-cyan-50 to-blue-50 dark:from-slate-900 dark:to-slate-800 min-h-screen pb-20 md:pb-0">

<!-- Desktop Sidebar -->
<div class="hidden md:flex md:fixed md:inset-y-0 md:left-0 md:w-72 bg-white dark:bg-slate-800 shadow-xl flex-col">
    <div class="flex items-center justify-center p-6 border-b dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">verified</span>
            </div>
            <span class="text-xl font-bold text-primary">Supervisor</span>
        </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-2">
        <a href="supervisor.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
            <span class="material-symbols-outlined">payments</span>
            <span>Pending Payments</span>
        </a>
        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">account_circle</span>
            <span>Profile</span>
        </a>
    </nav>
    
    <div class="p-4 border-t dark:border-slate-700">
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">supervisor_account</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 dark:text-white">Supervisor</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Verification Access</p>
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
                <span class="material-symbols-outlined text-white text-lg">verified</span>
            </div>
            <span class="text-lg font-bold text-primary">Supervisor</span>
        </div>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">Payment Verification</h1>

        <?php if ($msg = get_flash('success')): ?>
        <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 rounded-xl p-4">
            <p class="text-emerald-700 dark:text-emerald-300"><?= htmlspecialchars($msg) ?></p>
        </div>
        <?php endif; ?>

        <?php if ($error = get_flash('error')): ?>
        <div class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 rounded-xl p-4">
            <p class="text-red-700 dark:text-red-300"><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="bg-gradient-to-r from-primary to-secondary rounded-2xl p-6 text-white shadow-lg mb-8 card-animate">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm">Pending Verification</p>
                    <p class="text-4xl font-bold mt-1"><?= count($pendingOrders) ?></p>
                </div>
                <span class="material-symbols-outlined text-5xl text-white/30">payment</span>
            </div>
        </div>

        <!-- Pending Orders List -->
        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Orders to Verify</h2>
        <div class="space-y-3">
            <?php if (empty($pendingOrders)): ?>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 text-center">
                <span class="material-symbols-outlined text-5xl text-gray-400">check_circle</span>
                <p class="text-gray-500 dark:text-gray-400 mt-2">No pending payments to verify</p>
            </div>
            <?php else: ?>
                <?php foreach ($pendingOrders as $order): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm card-animate">
                    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                        <p class="font-bold text-gray-800 dark:text-white">#<?= $order['id_order'] ?></p>
                        <span class="badge badge-warning">pending</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300">Customer: <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm"><?= $order['service_name'] ?? 'Layanan' ?> • <?= $order['berat_cucian'] ?> kg</p>
                    <p class="text-primary font-bold text-lg mt-2">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></p>
                    <form method="post" class="mt-3">
                        <input type="hidden" name="verify_order_id" value="<?= $order['id_order'] ?>">
                        <button class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white py-3 rounded-xl font-semibold transition">Verify Payment</button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav md:hidden">
    <div class="flex justify-around">
        <a href="supervisor.php" class="flex flex-col items-center gap-1 text-primary">
            <span class="material-symbols-outlined">payments</span>
            <span class="text-xs">Verify</span>
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