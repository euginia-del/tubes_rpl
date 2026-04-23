<?php
require_once 'common.php';
$user = require_customer();

$db = get_db();

// Get user current balance
$stmt = $db->prepare('SELECT saldo FROM user WHERE id_user = ?');
$stmt->execute([$user['id_user']]);
$saldo = $stmt->fetchColumn();

// Handle top up langsung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nominal'], $_POST['metode'])) {
    $nominal = floatval($_POST['nominal']);
    $metode = $_POST['metode'];
    
    if ($nominal >= 10000) {
        // Update saldo user langsung
        $stmt = $db->prepare('UPDATE user SET saldo = saldo + ? WHERE id_user = ?');
        $stmt->execute([$nominal, $user['id_user']]);
        
        // Catat ke tabel topup
        $stmt = $db->prepare('INSERT INTO topup (id_user, nominal, metode, tanggal_topup) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$user['id_user'], $nominal, $metode]);
        
        // Catat ke transaksi saldo
        $stmt = $db->prepare('INSERT INTO transaksi_saldo (id_user, nominal, jenis, status, keterangan, tanggal) VALUES (?, ?, "topup", "sukses", ?, NOW())');
        $stmt->execute([$user['id_user'], $nominal, 'Top up via ' . strtoupper($metode)]);
        
        set_flash('success', 'Top up Rp ' . number_format($nominal, 0, ',', '.') . ' berhasil! Saldo Anda bertambah.');
    } else {
        set_flash('error', 'Minimal top up Rp 10.000');
    }
    header('Location: topup.php');
    exit;
}

// Get top up history
$stmt = $db->prepare('SELECT * FROM topup WHERE id_user = ? ORDER BY tanggal_topup DESC');
$stmt->execute([$user['id_user']]);
$topupHistory = $stmt->fetchAll();

// Get transaction history
$stmt = $db->prepare('SELECT * FROM transaksi_saldo WHERE id_user = ? ORDER BY tanggal DESC LIMIT 20');
$stmt->execute([$user['id_user']]);
$transactions = $stmt->fetchAll();

$methods = [
    'gopay' => ['name' => 'GoPay', 'icon' => 'account_balance_wallet', 'color' => 'from-green-500 to-green-600'],
    'dana' => ['name' => 'DANA', 'icon' => 'account_balance_wallet', 'color' => 'from-blue-500 to-blue-600'],
    'bca' => ['name' => 'BCA Transfer', 'icon' => 'account_balance', 'color' => 'from-red-500 to-red-600'],
    'bri' => ['name' => 'BRI Transfer', 'icon' => 'account_balance', 'color' => 'from-blue-700 to-blue-800'],
    'mandiri' => ['name' => 'Mandiri Transfer', 'icon' => 'account_balance', 'color' => 'from-yellow-600 to-yellow-700'],
    'bni' => ['name' => 'BNI Transfer', 'icon' => 'account_balance', 'color' => 'from-blue-600 to-blue-700'],
    'tunai' => ['name' => 'Tunai', 'icon' => 'payments', 'color' => 'from-gray-500 to-gray-600']
];

$quickNominals = [20000, 50000, 100000, 200000, 500000];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Top Up Saldo - LaundryApp</title>
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

<!-- Desktop Sidebar -->
<div class="hidden md:flex md:fixed md:inset-y-0 md:left-0 md:w-72 bg-white dark:bg-slate-800 shadow-xl flex-col">
    <div class="flex items-center justify-center p-6 border-b dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">local_laundry_service</span>
            </div>
            <span class="text-xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">LaundryFresh</span>
        </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-2">
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <a href="neworder.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">add_shopping_cart</span>
            <span>New Order</span>
        </a>
        <a href="history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">receipt_long</span>
            <span>History</span>
        </a>
        <a href="topup.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
            <span class="material-symbols-outlined">wallet</span>
            <span>Top Up</span>
        </a>
        <a href="price.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">price_check</span>
            <span>Pricing</span>
        </a>
        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">account_circle</span>
            <span>Profile</span>
        </a>
    </nav>
    
    <div class="p-4 border-t dark:border-slate-700">
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">person</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($user['nama']) ?></p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Customer</p>
            </div>
            <button id="themeToggle" class="text-xs px-2 py-1 rounded-full border dark:border-slate-600">🌙</button>
        </div>
    </div>
</div>

<!-- Mobile Header -->
<div class="md:hidden bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-40">
    <div class="flex items-center justify-between px-4 py-3">
        <span class="text-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Top Up Saldo</span>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6 max-w-4xl mx-auto">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">Top Up Saldo</h1>

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

        <!-- Saldo Card -->
        <div class="bg-gradient-to-r from-primary to-secondary rounded-2xl p-6 text-white shadow-lg mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm">Saldo Anda</p>
                    <p class="text-4xl font-bold mt-1">Rp <?= number_format($saldo, 0, ',', '.') ?></p>
                </div>
                <span class="material-symbols-outlined text-5xl text-white/30">account_balance_wallet</span>
            </div>
        </div>

        <!-- Quick Nominal -->
        <div class="mb-4">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pilih Nominal Cepat:</p>
            <div class="flex flex-wrap gap-2">
                <?php foreach($quickNominals as $nom): ?>
                <button type="button" onclick="setNominal(<?= $nom ?>)" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 rounded-xl text-sm font-semibold hover:bg-primary hover:text-white transition">
                    Rp <?= number_format($nom, 0, ',', '.') ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Up Form -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">add_circle</span>
                Isi Saldo
            </h2>
            <form method="post" class="space-y-4" id="topupForm">
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Nominal Top Up</label>
                    <div class="relative mt-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">Rp</span>
                        <input type="number" name="nominal" id="nominal" min="10000" step="10000" class="w-full border rounded-xl pl-12 pr-4 py-3 dark:bg-slate-700" placeholder="10.000" required>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Minimal Rp 10.000</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Metode Pembayaran</label>
                    <select name="metode" class="w-full border rounded-xl p-3 mt-1 dark:bg-slate-700" required>
                        <option value="">Pilih Metode</option>
                        <?php foreach($methods as $key => $method): ?>
                        <option value="<?= $key ?>"><?= $method['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 rounded-xl hover-lift transition">
                    Top Up Sekarang
                </button>
            </form>
        </div>

        <!-- Top Up History -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">history</span>
                Riwayat Top Up
            </h2>
            <div class="space-y-3">
                <?php if (empty($topupHistory)): ?>
                <p class="text-center text-gray-500 py-4">Belum ada riwayat top up</p>
                <?php else: ?>
                    <?php foreach($topupHistory as $topup): ?>
                    <div class="flex items-center justify-between border-b pb-3">
                        <div>
                            <p class="font-semibold text-emerald-600">+ Rp <?= number_format($topup['nominal'], 0, ',', '.') ?></p>
                            <p class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($topup['tanggal_topup'])) ?></p>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-success">Sukses</span>
                            <p class="text-xs text-gray-500 mt-1"><?= strtoupper($topup['metode']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">receipt</span>
                Riwayat Transaksi Saldo
            </h2>
            <div class="space-y-3">
                <?php if (empty($transactions)): ?>
                <p class="text-center text-gray-500 py-4">Belum ada transaksi</p>
                <?php else: ?>
                    <?php foreach($transactions as $trans): ?>
                    <div class="flex items-center justify-between border-b pb-3">
                        <div>
                            <p class="font-semibold <?= $trans['jenis'] == 'topup' ? 'text-emerald-600' : 'text-red-600' ?>">
                                <?= $trans['jenis'] == 'topup' ? '+' : '-' ?> 
                                Rp <?= number_format($trans['nominal'], 0, ',', '.') ?>
                            </p>
                            <p class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($trans['tanggal'])) ?></p>
                            <?php if($trans['keterangan']): ?>
                            <p class="text-xs text-gray-400"><?= htmlspecialchars($trans['keterangan']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="text-xs capitalize px-2 py-1 rounded-full bg-gray-100 dark:bg-slate-700">
                                <?= $trans['jenis'] == 'topup' ? 'Top Up' : 'Pembayaran' ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav md:hidden">
    <div class="flex justify-around">
        <a href="dashboard.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="text-xs">Home</span>
        </a>
        <a href="neworder.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">add_shopping_cart</span>
            <span class="text-xs">New</span>
        </a>
        <a href="history.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">receipt_long</span>
            <span class="text-xs">History</span>
        </a>
        <a href="topup.php" class="flex flex-col items-center gap-1 text-primary">
            <span class="material-symbols-outlined">wallet</span>
            <span class="text-xs">Top Up</span>
        </a>
    </div>
</div>

<script>
function setNominal(nominal) {
    document.getElementById('nominal').value = nominal;
}
</script>

<?= global_route_script() ?>
</body>
</html>