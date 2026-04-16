<?php
require_once 'common.php';
$user = require_customer();

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    header('Location: history.php');
    exit;
}

$db = get_db();

// Get order details
$stmt = $db->prepare('SELECT o.*, l.nama_layanan as service_name 
    FROM orders o 
    JOIN layanan l ON o.id_layanan = l.id_layanan 
    WHERE o.id_order = ? AND o.id_user = ?');
$stmt->execute([$order_id, $user['id_user']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: history.php');
    exit;
}

// Get user saldo
$stmt = $db->prepare('SELECT saldo FROM user WHERE id_user = ?');
$stmt->execute([$user['id_user']]);
$saldo = $stmt->fetchColumn();

// Check if payment already exists
$stmt = $db->prepare('SELECT * FROM pembayaran WHERE id_order = ?');
$stmt->execute([$order_id]);
$payment = $stmt->fetch();

$methods = [
    'saldo' => ['name' => 'Bayar Pakai Saldo', 'icon' => 'account_balance_wallet', 'color' => 'from-primary to-secondary', 'saldo' => true],
    'gopay' => ['name' => 'GoPay', 'icon' => 'account_balance_wallet', 'color' => 'from-green-500 to-green-600', 'saldo' => false],
    'dana' => ['name' => 'DANA', 'icon' => 'account_balance_wallet', 'color' => 'from-blue-500 to-blue-600', 'saldo' => false],
    'bca' => ['name' => 'BCA Transfer', 'icon' => 'account_balance', 'color' => 'from-red-500 to-red-600', 'saldo' => false],
    'bri' => ['name' => 'BRI Transfer', 'icon' => 'account_balance', 'color' => 'from-blue-700 to-blue-800', 'saldo' => false],
    'mandiri' => ['name' => 'Mandiri Transfer', 'icon' => 'account_balance', 'color' => 'from-yellow-600 to-yellow-700', 'saldo' => false],
    'bni' => ['name' => 'BNI Transfer', 'icon' => 'account_balance', 'color' => 'from-blue-600 to-blue-700', 'saldo' => false],
    'tunai' => ['name' => 'Tunai (Bayar di Tempat)', 'icon' => 'payments', 'color' => 'from-gray-500 to-gray-600', 'saldo' => false]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['metode'])) {
    $metode = $_POST['metode'];
    
    if ($metode === 'saldo') {
        // Cek saldo cukup
        if ($saldo >= $order['harga_snapshot']) {
            // Kurangi saldo
            $stmt = $db->prepare('UPDATE user SET saldo = saldo - ? WHERE id_user = ?');
            $stmt->execute([$order['harga_snapshot'], $user['id_user']]);
            
            // Catat transaksi saldo
            $stmt = $db->prepare('INSERT INTO transaksi_saldo (id_user, id_order, nominal, jenis, status, keterangan, tanggal) VALUES (?, ?, ?, "pembayaran", "sukses", ?, NOW())');
            $stmt->execute([$user['id_user'], $order_id, $order['harga_snapshot'], 'Pembayaran order #' . $order_id]);
            
            // Update status order langsung ke proses
            update_order_status($order_id, 'proses');
            
            set_flash('success', 'Pembayaran berhasil menggunakan saldo! Order sedang diproses.');
            header('Location: order_details_process.php?order=' . $order_id);
            exit;
        } else {
            set_flash('error', 'Saldo tidak cukup! Silakan top up terlebih dahulu. (Saldo Anda: Rp ' . number_format($saldo, 0, ',', '.') . ')');
            header('Location: payment.php?order_id=' . $order_id);
            exit;
        }
    } else {
        // Pembayaran via metode lain (perlu verifikasi supervisor)
        $nomor_transaksi = 'TRX' . date('YmdHis') . rand(100, 999);
        
        if ($payment) {
            $stmt = $db->prepare('UPDATE pembayaran SET metode = ?, nomor_transaksi = ?, tanggal_pembayaran = NOW(), status_bayar = "pending" WHERE id_order = ?');
            $stmt->execute([$metode, $nomor_transaksi, $order_id]);
        } else {
            $stmt = $db->prepare('INSERT INTO pembayaran (id_order, metode, nomor_transaksi, tanggal_pembayaran, jumlah_bayar, status_bayar) VALUES (?, ?, ?, NOW(), ?, "pending")');
            $stmt->execute([$order_id, $metode, $nomor_transaksi, $order['harga_snapshot']]);
        }
        
        set_flash('success', 'Pembayaran via ' . strtoupper($metode) . ' berhasil diproses! Menunggu verifikasi supervisor.');
        header('Location: order_details_process.php?order=' . $order_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Pilih Metode Pembayaran - LaundryApp</title>
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
        <a href="order_details_process.php?order=<?= $order_id ?>" class="text-gray-600 dark:text-gray-300">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <span class="text-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Pembayaran</span>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<div class="container-responsive py-6 max-w-2xl mx-auto">
    <div class="hidden md:flex items-center justify-between mb-6">
        <a href="order_details_process.php?order=<?= $order_id ?>" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-primary transition">
            <span class="material-symbols-outlined">arrow_back</span>
            <span>Kembali ke Order</span>
        </a>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>

    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">Pilih Metode Pembayaran</h1>

    <!-- Order Summary -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Ringkasan Order</h2>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-500">Order #</span>
                <span class="font-semibold"><?= $order['id_order'] ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Layanan</span>
                <span class="font-semibold"><?= htmlspecialchars($order['service_name']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Berat</span>
                <span class="font-semibold"><?= $order['berat_cucian'] ?> kg</span>
            </div>
            <div class="border-t pt-3 mt-2">
                <div class="flex justify-between">
                    <span class="text-lg font-bold">Total Pembayaran</span>
                    <span class="text-2xl font-bold text-primary">Rp <?= number_format($order['harga_snapshot'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Saldo Info -->
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 mb-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-blue-600 dark:text-blue-400">Saldo Anda</p>
                <p class="text-xl font-bold text-blue-700 dark:text-blue-300">Rp <?= number_format($saldo, 0, ',', '.') ?></p>
            </div>
            <a href="topup.php" class="text-sm bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600 transition">Top Up</a>
        </div>
        <?php if ($saldo < $order['harga_snapshot']): ?>
        <p class="text-xs text-red-500 mt-2">⚠️ Saldo tidak cukup untuk membayar order ini. Silakan top up atau pilih metode lain.</p>
        <?php endif; ?>
    </div>

    <!-- Payment Methods -->
    <div class="grid gap-4">
        <?php foreach($methods as $key => $method): ?>
        <form method="post" class="card-animate">
            <input type="hidden" name="metode" value="<?= $key ?>">
            <button type="submit" class="w-full bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-md hover:shadow-lg transition-all hover-lift flex items-center gap-4 text-left <?= ($key === 'saldo' && $saldo < $order['harga_snapshot']) ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= ($key === 'saldo' && $saldo < $order['harga_snapshot']) ? 'disabled' : '' ?>>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-r <?= $method['color'] ?> flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-2xl"><?= $method['icon'] ?></span>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800 dark:text-white"><?= $method['name'] ?></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <?php if ($key === 'saldo'): ?>
                            <?= $saldo >= $order['harga_snapshot'] ? 'Saldo cukup untuk membayar' : 'Saldo tidak cukup (Rp ' . number_format($saldo, 0, ',', '.') . ')' ?>
                        <?php else: ?>
                            <?= $key === 'tunai' ? 'Bayar langsung saat mengambil laundry' : 'Transfer via ' . $method['name'] . ', menunggu verifikasi' ?>
                        <?php endif; ?>
                    </p>
                </div>
                <span class="material-symbols-outlined text-gray-400">chevron_right</span>
            </button>
        </form>
        <?php endforeach; ?>
    </div>

    <!-- Info -->
    <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-yellow-600">info</span>
            <div>
                <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">Informasi Pembayaran</p>
                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                    ✅ <strong>Bayar Pakai Saldo</strong> → Langsung diproses tanpa verifikasi<br>
                    🕐 <strong>GoPay/DANA/Transfer Bank</strong> → Menunggu verifikasi Supervisor<br>
                    💰 <strong>Tunai</strong> → Bayar saat mengambil laundry
                </p>
            </div>
        </div>
    </div>
</div>

<?= global_route_script() ?>
</body>
</html>