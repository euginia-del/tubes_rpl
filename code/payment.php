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

// Generate nomor transaksi unik
$nomor_transaksi = 'TRX' . date('YmdHis') . rand(100, 999);

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi'])) {
    $metode = $_POST['metode'];
    
    if ($metode === 'saldo') {
        // CEK SALDO
        if ($saldo >= $order['harga_snapshot']) {
            // KURANGI SALDO
            $stmt = $db->prepare('UPDATE user SET saldo = saldo - ? WHERE id_user = ?');
            $stmt->execute([$order['harga_snapshot'], $user['id_user']]);
            
            // CATAT TRANSAKSI SALDO
            $stmt = $db->prepare('INSERT INTO transaksi_saldo (id_user, id_order, nominal, jenis, status, keterangan, tanggal) VALUES (?, ?, ?, "pembayaran", "sukses", ?, NOW())');
            $stmt->execute([$user['id_user'], $order_id, $order['harga_snapshot'], 'Pembayaran order #' . $order_id . ' via Saldo']);
            
            // UPDATE STATUS ORDER LANGSUNG KE PROSES
            update_order_status($order_id, 'proses');
            
            // CATAT KE PEMBAYARAN
            $stmt = $db->prepare('INSERT INTO pembayaran (id_order, metode, nomor_transaksi, tanggal_pembayaran, jumlah_bayar, status_bayar) VALUES (?, ?, ?, NOW(), ?, "lunas")');
            $stmt->execute([$order_id, $metode, $nomor_transaksi, $order['harga_snapshot']]);
            
            set_flash('success', 'Pembayaran berhasil! Saldo berkurang Rp ' . number_format($order['harga_snapshot'], 0, ',', '.'));
            header('Location: order_details_process.php?order=' . $order_id);
            exit;
        } else {
            set_flash('error', 'Saldo tidak cukup! Saldo Anda: Rp ' . number_format($saldo, 0, ',', '.'));
            header('Location: payment.php?order_id=' . $order_id);
            exit;
        }
    } else {
        // Upload bukti untuk metode lain
        $bukti_pembayaran = null;
        if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/bukti_pembayaran/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            $ekstensi = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
            $bukti_pembayaran = $upload_dir . 'bukti_' . $order_id . '_' . time() . '.' . $ekstensi;
            move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $bukti_pembayaran);
        }
        
        $catatan = $_POST['catatan'] ?? '';
        
        // SIMPAN KE PEMBAYARAN
        $stmt = $db->prepare('INSERT INTO pembayaran (id_order, metode, nomor_transaksi, tanggal_pembayaran, jumlah_bayar, status_bayar, bukti_pembayaran, catatan_pembayaran) VALUES (?, ?, ?, NOW(), ?, "pending", ?, ?)');
        $stmt->execute([$order_id, $metode, $nomor_transaksi, $order['harga_snapshot'], $bukti_pembayaran, $catatan]);
        
        set_flash('success', 'Pembayaran via ' . strtoupper($metode) . ' berhasil! No. Transaksi: ' . $nomor_transaksi . '. Menunggu verifikasi admin.');
        header('Location: order_details_process.php?order=' . $order_id);
        exit;
    }
}

$methods = [
    'saldo' => ['name' => 'Bayar Pakai Saldo', 'icon' => 'account_balance_wallet', 'color' => 'from-primary to-secondary', 'need_upload' => false, 'desc' => 'Langsung dipotong dari saldo Anda', 'rekening' => ''],
    'gopay' => ['name' => 'GoPay', 'icon' => 'account_balance_wallet', 'color' => 'from-green-500 to-green-600', 'need_upload' => true, 'desc' => 'Scan QRIS atau transfer ke nomor GoPay', 'rekening' => '081234567890'],
    'dana' => ['name' => 'DANA', 'icon' => 'account_balance_wallet', 'color' => 'from-blue-500 to-blue-600', 'need_upload' => true, 'desc' => 'Scan QRIS atau transfer ke nomor DANA', 'rekening' => '081234567890'],
    'bca' => ['name' => 'BCA Transfer', 'icon' => 'account_balance', 'color' => 'from-red-500 to-red-600', 'need_upload' => true, 'desc' => 'Transfer ke rekening BCA', 'rekening' => '1234567890 a.n LaundryFresh'],
    'bri' => ['name' => 'BRI Transfer', 'icon' => 'account_balance', 'color' => 'from-blue-700 to-blue-800', 'need_upload' => true, 'desc' => 'Transfer ke rekening BRI', 'rekening' => '1234567890 a.n LaundryFresh'],
    'mandiri' => ['name' => 'Mandiri Transfer', 'icon' => 'account_balance', 'color' => 'from-yellow-600 to-yellow-700', 'need_upload' => true, 'desc' => 'Transfer ke rekening Mandiri', 'rekening' => '1234567890 a.n LaundryFresh'],
    'bni' => ['name' => 'BNI Transfer', 'icon' => 'account_balance', 'color' => 'from-blue-600 to-blue-700', 'need_upload' => true, 'desc' => 'Transfer ke rekening BNI', 'rekening' => '1234567890 a.n LaundryFresh'],
    'tunai' => ['name' => 'Tunai (Bayar di Tempat)', 'icon' => 'payments', 'color' => 'from-gray-500 to-gray-600', 'need_upload' => false, 'desc' => 'Bayar langsung saat mengambil laundry', 'rekening' => '']
];

$selected_method = $_GET['method'] ?? null;
$selected_method_data = $selected_method ? $methods[$selected_method] : null;
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
<script>
function selectMethod(method) {
    window.location.href = 'payment.php?order_id=<?= $order_id ?>&method=' + method;
}
function goBack() {
    window.location.href = 'payment.php?order_id=<?= $order_id ?>';
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
            <a href="topup.php" class="text-sm bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600 transition">Top Up Saldo</a>
        </div>
    </div>

    <?php if (!$selected_method): ?>
    <!-- Pilih Metode -->
    <div class="grid gap-4">
        <?php foreach($methods as $key => $method): ?>
        <button onclick="selectMethod('<?= $key ?>')" 
            class="w-full bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-md hover:shadow-lg transition-all hover-lift flex items-center gap-4 text-left"
            <?= ($key === 'saldo' && $saldo < $order['harga_snapshot']) ? 'disabled style="opacity:0.5"' : '' ?>>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-r <?= $method['color'] ?> flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-2xl"><?= $method['icon'] ?></span>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-gray-800 dark:text-white"><?= $method['name'] ?></h3>
                <p class="text-sm text-gray-500 dark:text-gray-400"><?= $method['desc'] ?></p>
            </div>
            <span class="material-symbols-outlined text-gray-400">chevron_right</span>
        </button>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- Detail Pembayaran & Konfirmasi -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r <?= $selected_method_data['color'] ?> p-4 text-white">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-2xl"><?= $selected_method_data['icon'] ?></span>
                <h2 class="text-xl font-bold"><?= $selected_method_data['name'] ?></h2>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Informasi Pembayaran -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-slate-700/50 rounded-xl">
                <h3 class="font-semibold text-gray-800 dark:text-white mb-3">Informasi Pembayaran</h3>
                
                <?php if ($selected_method === 'saldo'): ?>
                <div class="space-y-2">
                    <p class="text-gray-600 dark:text-gray-300">🔹 Anda akan membayar <strong>Rp <?= number_format($order['harga_snapshot'], 0, ',', '.') ?></strong> menggunakan saldo</p>
                    <p class="text-gray-600 dark:text-gray-300">🔹 Saldo Anda saat ini: <strong class="<?= $saldo >= $order['harga_snapshot'] ? 'text-emerald-600' : 'text-red-600' ?>">Rp <?= number_format($saldo, 0, ',', '.') ?></strong></p>
                    <?php if ($saldo >= $order['harga_snapshot']): ?>
                    <p class="text-emerald-600 text-sm">✅ Saldo cukup! Sisa saldo setelah pembayaran: Rp <?= number_format($saldo - $order['harga_snapshot'], 0, ',', '.') ?></p>
                    <?php else: ?>
                    <p class="text-red-600 text-sm">❌ Saldo tidak cukup! Butuh tambahan Rp <?= number_format($order['harga_snapshot'] - $saldo, 0, ',', '.') ?></p>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="space-y-3">
                    <p class="text-gray-600 dark:text-gray-300">🔹 Transfer ke rekening berikut:</p>
                    <div class="bg-white dark:bg-slate-800 p-3 rounded-lg border">
                        <p class="font-mono text-lg font-bold text-primary"><?= $selected_method_data['rekening'] ?></p>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 mt-3">🔹 Nominal: <strong class="text-primary text-lg">Rp <?= number_format($order['harga_snapshot'], 0, ',', '.') ?></strong></p>
                    <p class="text-gray-600 dark:text-gray-300">🔹 Nomor Transaksi: <strong class="text-primary"><?= $nomor_transaksi ?></strong></p>
                    <p class="text-sm text-gray-500 mt-2">*Simpan nomor transaksi ini untuk konfirmasi pembayaran</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Form Upload Bukti (untuk non-saldo) -->
            <?php if ($selected_method !== 'saldo'): ?>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="metode" value="<?= $selected_method ?>">
                <input type="hidden" name="konfirmasi" value="1">
                
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Upload Bukti Transfer</label>
                    <input type="file" name="bukti_pembayaran" accept="image/*,.pdf" class="w-full border rounded-xl p-2 mt-1 dark:bg-slate-700" required>
                    <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, PDF (Max 2MB)</p>
                </div>
                
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Catatan (Opsional)</label>
                    <textarea name="catatan" rows="2" class="w-full border rounded-xl p-3 mt-1 dark:bg-slate-700" placeholder="Contoh: Transfer dari BCA a.n Budi, No. Transaksi: ..."></textarea>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 rounded-xl hover-lift transition">
                    Konfirmasi Pembayaran
                </button>
            </form>
            <?php else: ?>
            <!-- Form untuk bayar pakai saldo -->
            <form method="post" class="space-y-4">
                <input type="hidden" name="metode" value="saldo">
                <input type="hidden" name="konfirmasi" value="1">
                
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-xl">
                    <p class="text-yellow-800 dark:text-yellow-300 text-sm">
                        ⚠️ Konfirmasi pembayaran akan memotong saldo Anda sebesar <strong>Rp <?= number_format($order['harga_snapshot'], 0, ',', '.') ?></strong>
                    </p>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white font-bold py-3 rounded-xl hover-lift transition" <?= $saldo < $order['harga_snapshot'] ? 'disabled' : '' ?>>
                    Konfirmasi Pembayaran dengan Saldo
                </button>
            </form>
            <?php endif; ?>
            
            <button onclick="goBack()" class="w-full mt-3 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 font-semibold py-3 rounded-xl transition">
                Kembali Pilih Metode
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Info -->
    <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-yellow-600">info</span>
            <div>
                <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">Cara Pembayaran</p>
                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                    1. Pilih metode pembayaran<br>
                    2. Ikuti instruksi pembayaran<br>
                    3. Upload bukti transfer (jika diperlukan)<br>
                    4. Klik Konfirmasi Pembayaran<br>
                    5. Order akan diproses setelah diverifikasi admin
                </p>
            </div>
        </div>
    </div>
</div>

<?= global_route_script() ?>
</body>
</html>