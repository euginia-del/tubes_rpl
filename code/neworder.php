<?php
require_once 'common.php';
$user = currentUser();
if (!$user || $user['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$services = get_services();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>New Order</title>
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
<a href="dashboard.php" class="text-primary p-2 rounded-full hover:bg-slate-100">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h2 class="text-lg font-bold flex-1 text-center">New Order</h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border">Mode</button>
</div>

<div class="p-4">
<h3 class="font-bold text-lg mb-4">Pilih Layanan</h3>
<div class="space-y-3">
<?php foreach ($services as $service): ?>
<a href="order_detail.php?service_id=<?= $service['id_layanan'] ?>" class="flex items-center gap-4 bg-white dark:bg-slate-800 p-4 rounded-xl border hover:shadow-md transition-all">
<div class="bg-primary/10 rounded-lg p-3">
<span class="material-symbols-outlined text-primary">local_laundry_service</span>
</div>
<div class="flex-1">
<p class="font-bold"><?= htmlspecialchars($service['nama_layanan']) ?></p>
<p class="text-sm text-slate-500"><?= htmlspecialchars($service['deskripsi']) ?></p>
</div>
<div class="text-right">
<p class="text-primary font-bold">Rp <?= number_format($service['harga_per_kg'],0,',','.') ?></p>
<p class="text-xs text-slate-500">/kg</p>
</div>
<span class="material-symbols-outlined text-slate-400">chevron_right</span>
</a>
<?php endforeach; ?>
</div>
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

<?= global_route_script() ?>
</body>
</html>