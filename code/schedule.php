<?php
require_once 'common.php';
$user = require_customer();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = create_order();
    if ($orderId) {
        set_flash('success', 'Order #' . $orderId . ' berhasil dibuat!');
        header('Location: history.php');
        exit;
    }
    set_flash('error', 'Gagal membuat order.');
    header('Location: neworder.php');
    exit;
}

$currentOrder = get_current_order();
if (empty($currentOrder)) {
    header('Location: neworder.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Schedule Pickup - LaundryApp</title>
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
        <a href="order_detail.php?service_id=<?= $currentOrder['id_layanan'] ?>" class="text-gray-600 dark:text-gray-300">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <span class="text-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Schedule Pickup</span>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<!-- Main Content -->
<div class="container-responsive py-6 max-w-3xl mx-auto">
    <!-- Desktop Header -->
    <div class="hidden md:flex items-center justify-between mb-6">
        <a href="order_detail.php?service_id=<?= $currentOrder['id_layanan'] ?>" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-primary transition">
            <span class="material-symbols-outlined">arrow_back</span>
            <span>Back to Order Detail</span>
        </a>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>

    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">Schedule Pickup</h1>

    <div class="grid md:grid-cols-2 gap-6">
        <!-- Order Summary -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 card-animate">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">receipt</span>
                Order Summary
            </h2>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b dark:border-slate-700">
                    <span class="text-gray-500 dark:text-gray-400">Service</span>
                    <span class="font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($currentOrder['service_name'] ?? 'Layanan') ?></span>
                </div>
                <div class="flex justify-between py-2 border-b dark:border-slate-700">
                    <span class="text-gray-500 dark:text-gray-400">Weight</span>
                    <span class="font-semibold text-gray-800 dark:text-white"><?= $currentOrder['weight'] ?? 0 ?> kg</span>
                </div>
                <?php if (($currentOrder['discount'] ?? 0) > 0): ?>
                <div class="flex justify-between py-2 border-b dark:border-slate-700">
                    <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                    <span class="font-semibold text-gray-800 dark:text-white">Rp <?= number_format($currentOrder['subtotal'] ?? 0,0,',','.') ?></span>
                </div>
                <div class="flex justify-between py-2 border-b dark:border-slate-700 bg-emerald-50 dark:bg-emerald-900/20 -mx-2 px-2 rounded-lg">
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Diskon (<?= $currentOrder['discount_percent'] ?? 0 ?>%)</span>
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">- Rp <?= number_format($currentOrder['discount'] ?? 0,0,',','.') ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between py-2 pt-3">
                    <span class="text-gray-500 dark:text-gray-400 font-semibold">Total Price</span>
                    <span class="font-bold text-primary text-xl">Rp <?= number_format($currentOrder['total_price'] ?? 0,0,',','.') ?></span>
                </div>
            </div>
            <?php if (($currentOrder['discount'] ?? 0) > 0): ?>
            <div class="mt-4 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400">
                    <span class="material-symbols-outlined text-sm">savings</span>
                    <span class="text-sm font-semibold">Anda hemat Rp <?= number_format($currentOrder['discount'] ?? 0,0,',','.') ?>!</span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Schedule Form -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 card-animate">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">calendar_today</span>
                Pickup Schedule
            </h2>
            
            <form method="post" class="space-y-5">
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-2">Pickup Date</label>
                    <input type="date" id="pickup_date" 
                           class="w-full border border-gray-200 dark:border-slate-600 rounded-xl p-3 bg-gray-50 dark:bg-slate-700 focus:ring-2 focus:ring-primary"
                           value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-2">Pickup Time</label>
                    <select id="pickup_time" 
                            class="w-full border border-gray-200 dark:border-slate-600 rounded-xl p-3 bg-gray-50 dark:bg-slate-700 focus:ring-2 focus:ring-primary">
                        <option value="08:00-10:00">🌅 08:00 - 10:00 (Morning)</option>
                        <option value="10:00-12:00">☀️ 10:00 - 12:00 (Late Morning)</option>
                        <option value="13:00-15:00">🌤️ 13:00 - 15:00 (Afternoon)</option>
                        <option value="15:00-17:00">🌆 15:00 - 17:00 (Late Afternoon)</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-2">Pickup Address</label>
                    <textarea id="address" rows="3" 
                              class="w-full border border-gray-200 dark:border-slate-600 rounded-xl p-3 bg-gray-50 dark:bg-slate-700 focus:ring-2 focus:ring-primary" 
                              placeholder="Enter your complete address" required><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                </div>

                <input type="hidden" name="pickup_date" id="form_pickup_date">
                <input type="hidden" name="pickup_time" id="form_pickup_time">
                <input type="hidden" name="address" id="form_address">

                <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary hover:from-primary-dark hover:to-secondary-dark text-white font-bold py-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span>Confirm Order</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    document.getElementById('form_pickup_date').value = document.getElementById('pickup_date').value;
    document.getElementById('form_pickup_time').value = document.getElementById('pickup_time').value;
    document.getElementById('form_address').value = document.getElementById('address').value;
});
</script>

<?= global_route_script() ?>
</body>
</html>