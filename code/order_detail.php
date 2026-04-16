<?php
require_once 'common.php';
$user = require_customer();

$service_id = $_GET['service_id'] ?? 1;
$service = get_service($service_id);
if (!$service) {
    header('Location: neworder.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weight = floatval($_POST['weight'] ?? 1);
    $notes = $_POST['notes'] ?? '';
    $total_price = $weight * $service['harga_per_kg'];
    
    set_current_order([
        'id_layanan' => $service['id_layanan'],
        'service_name' => $service['nama_layanan'],
        'weight' => $weight,
        'notes' => $notes,
        'total_price' => $total_price
    ]);
    
    header('Location: schedule.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Order Detail</title>
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
<a href="neworder.php" class="text-primary p-2 rounded-full hover:bg-slate-100">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h2 class="text-lg font-bold flex-1 text-center">Order Detail</h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border">Mode</button>
</div>

<form method="post" class="p-4 space-y-6">
<div class="bg-primary/5 rounded-xl p-4">
    <p class="text-sm text-slate-500">Layanan yang dipilih</p>
    <p class="text-xl font-bold"><?= htmlspecialchars($service['nama_layanan']) ?></p>
    <p class="text-primary font-bold mt-1">Rp <?= number_format($service['harga_per_kg'],0,',','.') ?> / kg</p>
    <p class="text-sm text-slate-500 mt-2">Estimasi: <?= $service['estimasi_hari'] ?> hari</p>
</div>

<div>
    <label class="text-sm font-semibold">Berat Cucian (kg)</label>
    <div class="relative mt-1">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">fitness_center</span>
        <input type="number" name="weight" step="0.5" min="0.5" class="w-full border rounded-lg pl-10 pr-3 py-3" value="1" required onchange="updateTotal()">
    </div>
</div>

<div>
    <label class="text-sm font-semibold">Catatan (Opsional)</label>
    <textarea name="notes" class="w-full border rounded-lg p-3 mt-1" rows="3" placeholder="Contoh: Pakai deterjen mild, jangan dijemur terlalu lama..."></textarea>
</div>

<div class="bg-slate-100 dark:bg-slate-800 rounded-xl p-4">
    <div class="flex justify-between">
        <span>Total Harga</span>
        <span class="text-xl font-bold text-primary" id="totalDisplay">Rp <?= number_format($service['harga_per_kg'],0,',','.') ?></span>
    </div>
</div>

<button type="submit" class="w-full bg-primary text-white font-bold py-4 rounded-xl shadow-lg mt-4">Lanjut ke Jadwal</button>
</form>

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
function updateTotal() {
    let weight = document.querySelector('input[name="weight"]').value;
    let pricePerKg = <?= $service['harga_per_kg'] ?>;
    let total = weight * pricePerKg;
    document.getElementById('totalDisplay').innerHTML = 'Rp ' + total.toLocaleString('id-ID');
}
</script>
<?= global_route_script() ?>
</body>
</html>