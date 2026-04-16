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
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Pricing - Laundry</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {"primary": "#2094f3","background-light": "#f5f7f8","background-dark": "#101a22"},
            fontFamily: {"display": ["Inter"]}
        }
    }
}
</script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl pb-24">
<div class="px-4 py-4 flex items-center justify-between border-b">
<a href="dashboard.php" class="text-primary p-2 rounded-full hover:bg-slate-100">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h1 class="text-lg font-bold flex-1 text-center">Harga Layanan</h1>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border">Mode</button>
</div>

<div class="p-4 space-y-4">
<?php foreach ($services as $service): ?>
<div class="border rounded-2xl p-5 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800/50 shadow-sm">
<div class="flex justify-between items-start mb-3">
<div>
<h3 class="font-bold text-xl"><?= htmlspecialchars($service['nama_layanan']) ?></h3>
<p class="text-slate-500 text-sm mt-1"><?= htmlspecialchars($service['deskripsi']) ?></p>
<p class="text-slate-400 text-xs mt-1">Estimasi: <?= $service['estimasi_hari'] ?> hari</p>
</div>
<div class="text-right">
<p class="text-2xl font-bold text-primary">Rp <?= number_format($service['harga_per_kg'],0,',','.') ?></p>
<p class="text-xs text-slate-500">/kg</p>
</div>
</div>
<a href="order_detail.php?service_id=<?= $service['id_layanan'] ?>" class="block w-full bg-primary hover:bg-primary/90 text-white py-3 rounded-xl text-center font-bold transition-all">Pilih Layanan</a>
</div>
<?php endforeach; ?>
</div>

<!-- Bottom Nav -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto border-t bg-white dark:bg-slate-900 px-4 pb-6 pt-2">
<div class="flex gap-2">
<a href="dashboard.php" class="flex-1 text-center text-slate-500 py-2">Home</a>
<a href="neworder.php" class="flex-1 text-center text-slate-500 py-2">New Order</a>
<a href="history.php" class="flex-1 text-center text-slate-500 py-2">History</a>
<a href="profile.php" class="flex-1 text-center text-primary font-bold py-2">Profile</a>
</div>
</div>

<?= global_route_script() ?>
</body>
</html>