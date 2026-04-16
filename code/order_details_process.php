<?php
require_once 'common.php';
$user = currentUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

$order_id = $_GET['order'] ?? null;
$order = get_order($order_id);
if (!$order) {
    set_flash('error', 'Order tidak ditemukan');
    header('Location: history.php');
    exit;
}

// Get payment info
$db = get_db();
$stmt = $db->prepare('SELECT * FROM pembayaran WHERE id_order = ?');
$stmt->execute([$order_id]);
$payment = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Order #<?= $order['id_order'] ?> - LaundryApp</title>
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

<!-- Mobile Header -->
<div class="md:hidden bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-40">
    <div class="flex items-center justify-between px-4 py-3">
        <a href="history.php" class="text-gray-600 dark:text-gray-300">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <span class="text-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Order #<?= $order['id_order'] ?></span>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<!-- Main Content -->
<div class="container-responsive py-6 max-w-3xl mx-auto">
    <!-- Desktop Header -->
    <div class="hidden md:flex items-center justify-between mb-6">
        <a href="history.php" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-primary transition">
            <span class="material-symbols-outlined">arrow_back</span>
            <span>Back to History</span>
        </a>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <!-- Order Status Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 card-animate">
            <div class="text-center mb-4">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full <?= 
                    $order['status_order'] == 'selesai' ? 'bg-emerald-100 dark:bg-emerald-900/30' : 
                    ($order['status_order'] == 'proses' ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-amber-100 dark:bg-amber-900/30') 
                ?> mb-3">
                    <span class="material-symbols-outlined text-3xl <?= 
                        $order['status_order'] == 'selesai' ? 'text-emerald-600' : 
                        ($order['status_order'] == 'proses' ? 'text-blue-600' : 'text-amber-600') 
                    ?>">
                        <?= $order['status_order'] == 'selesai' ? 'check_circle' : ($order['status_order'] == 'proses' ? 'hourglass_empty' : 'schedule') ?>
                    </span>
                </div>
                <h2 class="text-2xl font-bold <?= 
                    $order['status_order'] == 'selesai' ? 'text-emerald-600' : 
                    ($order['status_order'] == 'proses' ? 'text-blue-600' : 'text-amber-600') 
                ?>">
                    <?= strtoupper($order['status_order']) ?>
                </h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                    <?= date('l, d F Y', strtotime($order['tanggal_order'])) ?>
                </p>
            </div>

            <!-- Progress Steps -->
            <div class="mt-6">
                <div class="flex items-center justify-between">
                    <div class="text-center flex-1">
                        <div class="w-8 h-8 mx-auto rounded-full <?= in_array($order['status_order'], ['pending', 'proses', 'selesai']) ? 'bg-primary text-white' : 'bg-gray-200 dark:bg-slate-700' ?> flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm">receipt</span>
                        </div>
                        <p class="text-xs mt-1 <?= in_array($order['status_order'], ['pending', 'proses', 'selesai']) ? 'text-primary' : 'text-gray-400' ?>">Order</p>
                    </div>
                    <div class="flex-1 h-1 <?= in_array($order['status_order'], ['proses', 'selesai']) ? 'bg-primary' : 'bg-gray-200 dark:bg-slate-700' ?>"></div>
                    <div class="text-center flex-1">
                        <div class="w-8 h-8 mx-auto rounded-full <?= in_array($order['status_order'], ['proses', 'selesai']) ? 'bg-primary text-white' : 'bg-gray-200 dark:bg-slate-700' ?> flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm">local_laundry_service</span>
                        </div>
                        <p class="text-xs mt-1 <?= in_array($order['status_order'], ['proses', 'selesai']) ? 'text-primary' : 'text-gray-400' ?>">Process</p>
                    </div>
                    <div class="flex-1 h-1 <?= $order['status_order'] == 'selesai' ? 'bg-primary' : 'bg-gray-200 dark:bg-slate-700' ?>"></div>
                    <div class="text-center flex-1">
                        <div class="w-8 h-8 mx-auto rounded-full <?= $order['status_order'] == 'selesai' ? 'bg-primary text-white' : 'bg-gray-200 dark:bg-slate-700' ?> flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm">done_all</span>
                        </div>
                        <p class="text-xs mt-1 <?= $order['status_order'] == 'selesai' ? 'text-primary' : 'text-gray-400' ?>">Done</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 card-animate">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">receipt_long</span>
                Order Details
            </h2>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b dark:border-slate-700">
                    <span class="text-gray-500 dark:text-gray-400">Order ID</span>
                    <span class="font-semibold text-gray-800 dark:text-white">#<?= $order['id_order'] ?></span>
                </div>
                <div class="flex justify-between py-2 border-b dark:border-slate-700">
                    <span class="text-gray-500 dark:text-gray-400">Customer</span>
                    <span class="font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></span>
                </div>
                <div class="flex justify-between py-2 border-b dark:border-slate-700">
                    <span class="text-gray-500 dark:text-gray-400">Service</span>
                    <span class="font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($order['service_name'] ?? 'N/A') ?></span>
                </div>
                <div class="flex justify-between py-2 border-b dark:border-slate-700">
                    <span class="text-gray-500 dark:text-gray-400">Weight</span>
                    <span class="font-semibold text-gray-800 dark:text-white"><?= $order['berat_cucian'] ?> kg</span>
                </div>
                <div class="flex justify-between py-2 border-b dark:border-slate-700">
                    <span class="text-gray-500 dark:text-gray-400">Total Price</span>
                    <span class="font-bold text-primary text-lg">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-500 dark:text-gray-400">Order Date</span>
                    <span class="font-semibold text-gray-800 dark:text-white"><?= date('d/m/Y H:i', strtotime($order['tanggal_order'])) ?></span>
                </div>
            </div>

            <?php if ($order['catatan']): ?>
            <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-yellow-600 text-sm">note</span>
                    <p class="text-sm text-yellow-800 dark:text-yellow-200"><?= htmlspecialchars($order['catatan']) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Action Button for Worker -->
    <?php if ($user['role'] === 'worker' && $order['status_order'] === 'pending'): ?>
    <div class="mt-6">
        <form method="post" action="order_process.php">
            <input type="hidden" name="order_id" value="<?= $order['id_order'] ?>">
            <input type="hidden" name="action" value="process_order">
            <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary hover:from-primary-dark hover:to-secondary-dark text-white font-bold py-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">play_arrow</span>
                <span>Process This Order</span>
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Back Button -->
    <div class="mt-4">
        <a href="history.php" class="w-full flex items-center justify-center gap-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 font-semibold py-3 rounded-xl transition hover:bg-gray-200 dark:hover:bg-slate-600">
            <span class="material-symbols-outlined">arrow_back</span>
            <span>Back to History</span>
        </a>
    </div>
</div>

<?= global_route_script() ?>
</body>
</html>