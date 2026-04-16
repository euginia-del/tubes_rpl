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
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Schedule Pickup</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {"primary": "#2094f3", "background-light": "#f5f7f8", "background-dark": "#101a22"},
            fontFamily: {"display": ["Inter"]}
        }
    }
}
</script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl">
<div class="flex items-center p-4 border-b">
<a href="order_detail.php?service_id=<?= $currentOrder['id_layanan'] ?>" class="text-primary p-2 rounded-full hover:bg-slate-100">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h2 class="text-lg font-bold flex-1 text-center">Jadwal Pickup</h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border">Mode</button>
</div>

<div class="p-4 space-y-4">
<div class="bg-primary/5 rounded-xl p-4">
    <p class="text-sm text-slate-500">Ringkasan Order</p>
    <p class="font-bold"><?= htmlspecialchars($currentOrder['service_name'] ?? 'Layanan') ?></p>
    <p><?= $currentOrder['weight'] ?? 0 ?> kg</p>
    <p class="text-primary font-bold">Rp <?= number_format($currentOrder['total_price'] ?? 0,0,',','.') ?></p>
</div>

<div>
    <label class="text-sm font-semibold">Tanggal Pickup</label>
    <input type="date" id="pickup_date" class="w-full border rounded-lg p-3 mt-1" value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
</div>

<div>
    <label class="text-sm font-semibold">Waktu Pickup</label>
    <select id="pickup_time" class="w-full border rounded-lg p-3 mt-1">
        <option value="08:00-10:00">08:00 - 10:00</option>
        <option value="10:00-12:00">10:00 - 12:00</option>
        <option value="13:00-15:00">13:00 - 15:00</option>
        <option value="15:00-17:00">15:00 - 17:00</option>
    </select>
</div>

<div>
    <label class="text-sm font-semibold">Alamat Pickup</label>
    <textarea id="address" class="w-full border rounded-lg p-3 mt-1" rows="2" placeholder="Masukkan alamat lengkap"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
</div>

<form method="post">
    <input type="hidden" name="pickup_date" id="form_pickup_date">
    <input type="hidden" name="pickup_time" id="form_pickup_time">
    <input type="hidden" name="address" id="form_address">
    <button type="submit" class="w-full bg-primary text-white font-bold py-4 rounded-xl shadow-lg mt-4">Konfirmasi Order</button>
</form>
</div>

<!-- Bottom Nav -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto border-t bg-white dark:bg-slate-900 px-4 pb-6 pt-2">
<div class="flex gap-2">
<a href="dashboard.php" class="flex-1 text-center text-slate-500 py-2">Home</a>
<a href="neworder.php" class="flex-1 text-center text-primary font-bold py-2">New Order</a>
<a href="history.php" class="flex-1 text-center text-slate-500 py-2">History</a>
<a href="profile.php" class="flex-1 text-center text-slate-500 py-2">Profile</a>
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