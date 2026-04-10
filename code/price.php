<?php
require_once 'common.php';
$user = currentUser();
if (!$user) header('Location: login.php');

$pricing = get_services();
if (empty($pricing)) {
    $pricing = [
        ['name' => 'Cuci Kering', 'description' => 'Cuci dan kering standar', 'price_per_weight' => 5000],
        ['name' => 'Setrika', 'description' => 'Setrika per pcs', 'price_per_unit' => 3000],
        ['name' => 'Dry Clean', 'description' => 'Cuci kering kimia', 'price_per_weight' => 15000],
        ['name' => 'Cuci Selimut', 'description' => 'Cuci selimut per kg', 'price_per_weight' => 25000],
        ['name' => 'Sepatu', 'description' => 'Cuci sepatu per pasang', 'price_per_unit' => 50000],
    ];
}

$selected = $_GET['plan'] ?? null;
if ($selected) {
    set_current_order(['service_type' => $selected]);
    set_flash('success', "Layanan \"$selected\" dipilih!");
    header('Location: neworder.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Pricing - Laundry</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet" />
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl pt-4 pb-20">
    <div class="px-4 mb-4 flex items-center gap-3 justify-between">
        <a href="dashboard.php" class="text-primary p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
        </a>
        <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex-1 text-center">Pricing</h1>
        <button id="themeToggle" class="text-slate-500 dark:text-slate-200 text-xs font-semibold px-3 py-1.5 rounded-full border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800">Mode</button>
    </div>
    <div class="px-4 mb-6">
        <?php if ($msg = get_flash('success')): ?>
            <div class="bg-emerald-100 border border-emerald-200 text-emerald-800 p-4 rounded-xl"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if ($err = get_flash('error')): ?>
            <div class="bg-red-100 border border-red-200 text-red-800 p-4 rounded-xl"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>
    </div>
    <div class="px-4 space-y-4">
        <?php foreach ($pricing as $service): ?>
            <div class="border rounded-2xl p-6 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-800/50 dark:to-slate-900/50 backdrop-blur-sm shadow-sm hover:shadow-md transition-all">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-xl text-slate-900 dark:text-slate-100"><?= htmlspecialchars($service['name']) ?></h3>
                        <p class="text-slate-500 dark:text-slate-400 mt-1"><?= htmlspecialchars($service['description']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-primary">Rp<?= number_format($service['price_per_weight'] ?? $service['price_per_unit'] ?? 0, 0, ',', '.') ?></p>
                        <p class="text-xs text-slate-500 uppercase tracking-wide">/<?= $service['price_per_weight'] ? 'kg' : 'pcs' ?></p>
                    </div>
                </div>
                <a href="?plan=<?= urlencode($service['name']) ?>" class="block w-full bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 text-white py-3 px-6 rounded-xl text-center font-bold shadow-lg hover:shadow-xl transition-all text-sm">Pilih Layanan</a>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white/90 dark:bg-slate-900/90 border-t border-slate-200 dark:border-slate-800 backdrop-blur-sm px-4 py-4">
        <div class="flex gap-3">
            <a class="flex-1 text-center py-3 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium hover:bg-slate-200 dark:hover:bg-slate-700">Home</a>
            <a class="flex-1 text-center py-3 px-4 rounded-xl bg-primary text-white font-bold shadow-lg">Pricing</a>
            <a class="flex-1 text-center py-3 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium hover:bg-slate-200 dark:hover:bg-slate-700">Orders</a>
            <a class="flex-1 text-center py-3 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium hover:bg-slate-200 dark:hover:bg-slate-700">Profile</a>
        </div>
    </div>
</div>
<?= global_route_script() ?>
</body>
</html>

