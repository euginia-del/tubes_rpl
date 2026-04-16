<?php
require_once 'common.php';
require_supervisor();

$db = get_db();
$pendingOrders = get_pending_orders();

// Get statistics
$totalOrders = get_order_count($db);
$completedOrders = get_completed_orders_count($db);
$pendingCount = get_pending_orders_count($db);

// Get today's verified count
$stmt = $db->prepare('SELECT COUNT(*) as today_verified FROM orders WHERE status_order = "proses" AND DATE(tanggal_order) = CURDATE()');
$stmt->execute();
$todayVerified = $stmt->fetch();

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Supervisor Dashboard - LaundryApp</title>
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
            <span class="material-symbols-outlined">verified</span>
            <span>Verify Payments</span>
        </a>
        <a href="reports.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">assessment</span>
            <span>Reports</span>
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
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">Payment Verification</h1>
            <a href="reports.php" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-dark transition">
                <span class="material-symbols-outlined text-sm">assessment</span>
                <span>View Reports</span>
            </a>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg card-animate">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $totalOrders ?></p>
                    </div>
                    <span class="material-symbols-outlined text-2xl text-primary">receipt_long</span>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg card-animate">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Completed</p>
                        <p class="text-2xl font-bold text-emerald-600"><?= $completedOrders ?></p>
                    </div>
                    <span class="material-symbols-outlined text-2xl text-emerald-500">task_alt</span>
                </div>
            </div>
            <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl p-5 text-white shadow-lg card-animate">
                <div>
                    <p class="text-amber-100 text-sm">Pending Verification</p>
                    <p class="text-3xl font-bold mt-1"><?= count($pendingOrders) ?></p>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg card-animate">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Today Verified</p>
                        <p class="text-2xl font-bold text-primary"><?= $todayVerified['today_verified'] ?? 0 ?></p>
                    </div>
                    <span class="material-symbols-outlined text-2xl text-primary">check_circle</span>
                </div>
            </div>
        </div>

        <!-- Pending Orders List -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b dark:border-slate-700">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Orders Waiting for Verification</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Verify payments to start processing orders</p>
            </div>
            
            <div class="p-6">
                <?php if ($msg = get_flash('success')): ?>
                <div class="mb-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 rounded-xl p-3">
                    <p class="text-emerald-700 dark:text-emerald-300"><?= htmlspecialchars($msg) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($error = get_flash('error')): ?>
                <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 rounded-xl p-3">
                    <p class="text-red-700 dark:text-red-300"><?= htmlspecialchars($error) ?></p>
                </div>
                <?php endif; ?>

                <?php if (empty($pendingOrders)): ?>
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-6xl text-gray-400">check_circle</span>
                    <p class="text-gray-500 dark:text-gray-400 mt-4">No pending payments to verify</p>
                    <p class="text-gray-400 text-sm mt-1">All orders have been verified</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pendingOrders as $order): ?>
                    <div class="border dark:border-slate-700 rounded-xl p-4 hover:shadow-md transition card-animate">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 flex-wrap mb-2">
                                    <p class="font-bold text-gray-800 dark:text-white text-lg">#<?= $order['id_order'] ?></p>
                                    <span class="badge badge-warning">pending</span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="material-symbols-outlined text-sm align-middle">person</span>
                                    <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?>
                                </p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                                    <?= $order['service_name'] ?? 'Layanan' ?> • <?= $order['berat_cucian'] ?> kg
                                </p>
                                <p class="text-primary font-bold text-xl mt-2">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></p>
                            </div>
                            <form method="post" class="md:text-right">
                                <input type="hidden" name="verify_order_id" value="<?= $order['id_order'] ?>">
                                <button class="w-full md:w-auto bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-semibold transition flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-sm">verified</span>
                                    <span>Verify Payment</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Card -->
        <div class="mt-6 bg-gradient-to-r from-primary/10 to-secondary/10 rounded-2xl p-5">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary">info</span>
                <div>
                    <p class="font-semibold text-gray-800 dark:text-white">How to Verify Payments?</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        1. Check customer's payment proof<br>
                        2. Confirm payment amount matches order total<br>
                        3. Click "Verify Payment" to approve and forward to worker
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav md:hidden">
    <div class="flex justify-around">
        <a href="supervisor.php" class="flex flex-col items-center gap-1 text-primary">
            <span class="material-symbols-outlined">verified</span>
            <span class="text-xs">Verify</span>
        </a>
        <a href="reports.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">assessment</span>
            <span class="text-xs">Reports</span>
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