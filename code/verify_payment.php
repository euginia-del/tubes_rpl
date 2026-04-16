<?php
require_once 'common.php';
require_admin();

$db = get_db();

// Handle verify payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_order_id'])) {
    $orderId = $_POST['verify_order_id'];
    
    // Update payment status
    $stmt = $db->prepare('UPDATE pembayaran SET status_bayar = "lunas", tanggal_verifikasi = NOW() WHERE id_order = ?');
    $stmt->execute([$orderId]);
    
    // Update order status to proses
    $stmt = $db->prepare('UPDATE orders SET status_order = "proses" WHERE id_order = ?');
    $stmt->execute([$orderId]);
    
    set_flash('success', 'Pembayaran order #' . $orderId . ' berhasil diverifikasi!');
    header('Location: verify_payments.php');
    exit;
}

// Handle reject payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_order_id'])) {
    $orderId = $_POST['reject_order_id'];
    $alasan = $_POST['alasan_penolakan'] ?? 'Bukti tidak valid';
    
    $stmt = $db->prepare('UPDATE pembayaran SET status_bayar = "gagal", catatan_pembayaran = CONCAT(IFNULL(catatan_pembayaran, ""), " | Ditolak: ", ?) WHERE id_order = ?');
    $stmt->execute([$alasan, $orderId]);
    
    set_flash('error', 'Pembayaran order #' . $orderId . ' ditolak.');
    header('Location: verify_payments.php');
    exit;
}

// Get ALL pending payments (debug: tampilkan semua)
$stmt = $db->prepare('
    SELECT p.*, o.id_order, o.tanggal_order, o.harga_snapshot, o.berat_cucian, o.status_order,
           u.nama as customer_name, u.email, u.no_hp,
           l.nama_layanan as service_name
    FROM pembayaran p
    JOIN orders o ON p.id_order = o.id_order
    JOIN user u ON o.id_user = u.id_user
    JOIN layanan l ON o.id_layanan = l.id_layanan
    WHERE p.status_bayar = "pending"
    ORDER BY p.tanggal_pembayaran DESC
');
$stmt->execute();
$pendingPayments = $stmt->fetchAll();

// Debug: cek jumlah data
$debugCount = count($pendingPayments);

// Get statistics
$stats = $db->query('SELECT COUNT(*) as total, SUM(jumlah_bayar) as total_nominal FROM pembayaran WHERE status_bayar = "lunas"')->fetch();
$pendingCount = $db->query('SELECT COUNT(*) FROM pembayaran WHERE status_bayar = "pending"')->fetchColumn();
$totalPayments = $db->query('SELECT COUNT(*) FROM pembayaran')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Verifikasi Pembayaran - Admin</title>
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
<style>
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal.active { display: flex; }
.modal-content { max-width: 500px; width: 90%; }
</style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-900 dark:to-slate-800 min-h-screen pb-20 md:pb-0">

<!-- Desktop Sidebar -->
<div class="hidden md:flex md:fixed md:inset-y-0 md:left-0 md:w-72 bg-white dark:bg-slate-800 shadow-xl flex-col">
    <div class="flex items-center justify-center p-6 border-b dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">local_laundry_service</span>
            </div>
            <span class="text-xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Admin Panel</span>
        </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-2">
        <a href="admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <a href="verify_payments.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
            <span class="material-symbols-outlined">verified</span>
            <span>Verifikasi Pembayaran</span>
        </a>
        <a href="reports.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">assessment</span>
            <span>Reports</span>
        </a>
        <a href="history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">receipt_long</span>
            <span>All Orders</span>
        </a>
        <a href="price.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">price_check</span>
            <span>Services</span>
        </a>
        <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">group</span>
            <span>Users</span>
        </a>
        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">account_circle</span>
            <span>Profile</span>
        </a>
    </nav>
    
    <div class="p-4 border-t dark:border-slate-700">
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">admin_panel_settings</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 dark:text-white">Administrator</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Full Access</p>
            </div>
            <button id="themeToggle" class="text-xs px-2 py-1 rounded-full border dark:border-slate-600">🌙</button>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">Verifikasi Pembayaran</h1>

        <!-- Debug Info -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3 mb-4 text-sm">
            <p>📊 Total Pembayaran di database: <strong><?= $totalPayments ?></strong></p>
            <p>⏳ Menunggu Verifikasi: <strong><?= $pendingCount ?></strong></p>
            <p>📋 Data yang ditampilkan: <strong><?= $debugCount ?></strong></p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg">
                <p class="text-gray-500 text-sm">Total Terverifikasi</p>
                <p class="text-2xl font-bold text-emerald-600"><?= $stats['total'] ?? 0 ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg">
                <p class="text-gray-500 text-sm">Total Nominal</p>
                <p class="text-2xl font-bold text-primary">Rp <?= number_format($stats['total_nominal'] ?? 0, 0, ',', '.') ?></p>
            </div>
            <div class="bg-amber-100 dark:bg-amber-900/30 rounded-2xl p-5">
                <p class="text-amber-600 text-sm">Menunggu Verifikasi</p>
                <p class="text-2xl font-bold text-amber-700"><?= $pendingCount ?></p>
            </div>
        </div>

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

        <!-- Pending Payments List -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b dark:border-slate-700 bg-amber-50 dark:bg-amber-900/20">
                <h2 class="text-lg font-bold text-amber-800 dark:text-amber-300">Menunggu Verifikasi (<?= count($pendingPayments) ?>)</h2>
            </div>
            
            <div class="p-6">
                <?php if (empty($pendingPayments)): ?>
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-6xl text-gray-400">check_circle</span>
                    <p class="text-gray-500 dark:text-gray-400 mt-4">Tidak ada pembayaran yang menunggu verifikasi</p>
                    <p class="text-gray-400 text-sm mt-2">Customer harus melakukan pembayaran terlebih dahulu</p>
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg text-left text-sm">
                        <p class="font-semibold">📌 Cara menambah data pembayaran:</p>
                        <ol class="list-decimal list-inside mt-2 space-y-1">
                            <li>Login sebagai Customer (michel@gmail.com)</li>
                            <li>Buat order baru di New Order</li>
                            <li>Setelah order dibuat, buka detail order</li>
                            <li>Klik tombol "Bayar Sekarang"</li>
                            <li>Pilih metode pembayaran (GoPay/DANA/Bank)</li>
                            <li>Upload bukti pembayaran</li>
                            <li>Data akan muncul di halaman ini</li>
                        </ol>
                    </div>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pendingPayments as $payment): ?>
                    <div class="border dark:border-slate-700 rounded-xl p-4 hover:shadow-md transition">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 flex-wrap mb-2">
                                    <p class="font-bold text-gray-800 dark:text-white text-lg">#<?= $payment['id_order'] ?></p>
                                    <span class="badge badge-warning">pending</span>
                                    <span class="badge badge-info text-xs"><?= strtoupper($payment['metode']) ?></span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="material-symbols-outlined text-sm align-middle">person</span>
                                    <?= htmlspecialchars($payment['customer_name']) ?>
                                </p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">
                                    <?= $payment['service_name'] ?> • <?= $payment['berat_cucian'] ?> kg
                                </p>
                                <p class="text-primary font-bold text-xl mt-2">Rp <?= number_format($payment['jumlah_bayar'],0,',','.') ?></p>
                                
                                <?php if ($payment['nomor_transaksi']): ?>
                                <p class="text-xs text-gray-500 mt-1">No. Transaksi: <strong><?= $payment['nomor_transaksi'] ?></strong></p>
                                <?php endif; ?>
                                
                                <?php if ($payment['catatan_pembayaran']): ?>
                                <p class="text-xs text-gray-500 mt-1">📝 Catatan: <?= htmlspecialchars($payment['catatan_pembayaran']) ?></p>
                                <?php endif; ?>
                                
                                <?php if ($payment['bukti_pembayaran'] && file_exists($payment['bukti_pembayaran'])): ?>
                                <div class="mt-2">
                                    <a href="<?= $payment['bukti_pembayaran'] ?>" target="_blank" class="inline-flex items-center gap-1 text-primary text-sm hover:underline">
                                        <span class="material-symbols-outlined text-sm">receipt</span>
                                        Lihat Bukti Pembayaran
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="openVerifyModal(<?= $payment['id_order'] ?>)" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded-xl font-semibold transition">
                                    ✅ Verifikasi
                                </button>
                                <button onclick="openRejectModal(<?= $payment['id_order'] ?>)" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-xl font-semibold transition">
                                    ❌ Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Verifikasi -->
<div id="verifyModal" class="modal">
    <div class="modal-content bg-white dark:bg-slate-800 rounded-2xl p-6">
        <h3 class="text-xl font-bold mb-4">Konfirmasi Verifikasi</h3>
        <p class="mb-4">Apakah Anda yakin ingin memverifikasi pembayaran ini?</p>
        <form method="post">
            <input type="hidden" name="verify_order_id" id="verifyOrderId">
            <div class="flex gap-3">
                <button type="button" onclick="closeVerifyModal()" class="flex-1 bg-gray-200 dark:bg-slate-700 py-2 rounded-xl">Batal</button>
                <button type="submit" class="flex-1 bg-emerald-500 text-white py-2 rounded-xl">Ya, Verifikasi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tolak -->
<div id="rejectModal" class="modal">
    <div class="modal-content bg-white dark:bg-slate-800 rounded-2xl p-6">
        <h3 class="text-xl font-bold mb-4">Tolak Pembayaran</h3>
        <p class="mb-2">Alasan penolakan:</p>
        <form method="post">
            <input type="hidden" name="reject_order_id" id="rejectOrderId">
            <textarea name="alasan_penolakan" rows="3" class="w-full border rounded-xl p-3 mb-4" placeholder="Contoh: Bukti transfer tidak jelas" required></textarea>
            <div class="flex gap-3">
                <button type="button" onclick="closeRejectModal()" class="flex-1 bg-gray-200 dark:bg-slate-700 py-2 rounded-xl">Batal</button>
                <button type="submit" class="flex-1 bg-red-500 text-white py-2 rounded-xl">Ya, Tolak</button>
            </div>
        </form>
    </div>
</div>

<script>
function openVerifyModal(orderId) {
    document.getElementById('verifyOrderId').value = orderId;
    document.getElementById('verifyModal').classList.add('active');
}
function closeVerifyModal() {
    document.getElementById('verifyModal').classList.remove('active');
}
function openRejectModal(orderId) {
    document.getElementById('rejectOrderId').value = orderId;
    document.getElementById('rejectModal').classList.add('active');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('active');
}
</script>

<?= global_route_script() ?>
</body>
</html>