<?php
require_once 'common.php';
require_supervisor();

$db = get_db();
$pendingOrders = get_pending_orders();

// Get statistics
$totalOrders = get_order_count($db);
$completedOrders = get_completed_orders_count($db);
$pendingCount = get_pending_orders_count($db);

// Get today's verified count
$stmt = $db->prepare('SELECT COUNT(*) as today_verified FROM orders WHERE status_order = "proses" AND DATE(tanggal_order) = CURDATE()');
$stmt->execute();
$todayVerified = $stmt->fetch();

// Handle verify payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_order_id'])) {
    $orderId = $_POST['verify_order_id'];
    
    // Update payment status
    $stmt = $db->prepare('UPDATE pembayaran SET status_bayar = "lunas", tanggal_verifikasi = NOW() WHERE id_order = ?');
    $stmt->execute([$orderId]);
    
    // Update order status to proses
    if (verify_payment($orderId)) {
        set_flash('success', 'Pembayaran untuk order #' . $orderId . ' berhasil diverifikasi!');
    } else {
        set_flash('error', 'Gagal memverifikasi pembayaran.');
    }
    header('Location: supervisor.php');
    exit;
}

// Handle reject payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_order_id'])) {
    $orderId = $_POST['reject_order_id'];
    $alasan = $_POST['alasan_penolakan'] ?? 'Bukti tidak jelas';
    
    // Update payment status
    $stmt = $db->prepare('UPDATE pembayaran SET status_bayar = "gagal", catatan_pembayaran = CONCAT(catatan_pembayaran, " | Ditolak: ", ?) WHERE id_order = ?');
    $stmt->execute([$alasan, $orderId]);
    
    set_flash('error', 'Pembayaran order #' . $orderId . ' ditolak. Alasan: ' . $alasan);
    header('Location: supervisor.php');
    exit;
}

// Get all pending payments with details
$stmt = $db->prepare('
    SELECT p.*, o.id_order, o.tanggal_order, o.harga_snapshot, o.berat_cucian, 
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

// Get all verified payments history
$stmt = $db->prepare('
    SELECT p.*, o.id_order, o.tanggal_order, o.harga_snapshot,
           u.nama as customer_name, u.email
    FROM pembayaran p
    JOIN orders o ON p.id_order = o.id_order
    JOIN user u ON o.id_user = u.id_user
    WHERE p.status_bayar != "pending"
    ORDER BY p.tanggal_pembayaran DESC
    LIMIT 20
');
$stmt->execute();
$paymentHistory = $stmt->fetchAll();

// Get statistics by payment method
$stmt = $db->prepare('
    SELECT metode, COUNT(*) as count, SUM(jumlah_bayar) as total
    FROM pembayaran
    WHERE status_bayar = "lunas"
    GROUP BY metode
');
$stmt->execute();
$paymentStats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Supervisor Dashboard - LaundryApp</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: { "primary": "#06b6d4", "secondary": "#0891b2" },
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
.modal.active {
    display: flex;
}
.modal-content {
    max-width: 500px;
    width: 90%;
}
</style>
</head>
<body class="bg-gradient-to-br from-cyan-50 to-blue-50 dark:from-slate-900 dark:to-slate-800 min-h-screen pb-20 md:pb-0">

<!-- Desktop Sidebar -->
<div class="hidden md:flex md:fixed md:inset-y-0 md:left-0 md:w-72 bg-white dark:bg-slate-800 shadow-xl flex-col">
    <div class="flex items-center justify-center p-6 border-b dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">verified</span>
            </div>
            <span class="text-xl font-bold text-primary">Supervisor</span>
        </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-2">
        <a href="supervisor.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
            <span class="material-symbols-outlined">verified</span>
            <span>Verify Payments</span>
        </a>
        <a href="reports.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">assessment</span>
            <span>Reports</span>
        </a>
        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">account_circle</span>
            <span>Profile</span>
        </a>
    </nav>
    
    <div class="p-4 border-t dark:border-slate-700">
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">supervisor_account</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 dark:text-white">Supervisor</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Verification Access</p>
            </div>
            <button id="themeToggle" class="text-xs px-2 py-1 rounded-full border dark:border-slate-600">🌙</button>
        </div>
    </div>
</div>

<!-- Mobile Header -->
<div class="md:hidden bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-40">
    <div class="flex items-center justify-between px-4 py-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-lg">verified</span>
            </div>
            <span class="text-lg font-bold text-primary">Supervisor</span>
        </div>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">Payment Verification</h1>
            <a href="reports.php" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-dark transition">
                <span class="material-symbols-outlined text-sm">assessment</span>
                <span>Reports</span>
            </a>
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

        <!-- Stats Overview -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg card-animate">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $totalOrders ?></p>
                    </div>
                    <span class="material-symbols-outlined text-2xl text-primary">receipt_long</span>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg card-animate">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Completed</p>
                        <p class="text-2xl font-bold text-emerald-600"><?= $completedOrders ?></p>
                    </div>
                    <span class="material-symbols-outlined text-2xl text-emerald-500">task_alt</span>
                </div>
            </div>
            <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl p-5 text-white shadow-lg card-animate">
                <div>
                    <p class="text-amber-100 text-sm">Pending Verification</p>
                    <p class="text-3xl font-bold mt-1"><?= count($pendingPayments) ?></p>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg card-animate">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Today Verified</p>
                        <p class="text-2xl font-bold text-primary"><?= $todayVerified['today_verified'] ?? 0 ?></p>
                    </div>
                    <span class="material-symbols-outlined text-2xl text-primary">check_circle</span>
                </div>
            </div>
        </div>

        <!-- Payment Method Stats -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 mb-8">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Statistik Metode Pembayaran</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach($paymentStats as $stat): ?>
                <div class="text-center p-3 bg-gray-50 dark:bg-slate-700 rounded-xl">
                    <p class="text-2xl font-bold text-primary"><?= $stat['count'] ?></p>
                    <p class="text-xs text-gray-500 uppercase"><?= strtoupper($stat['metode']) ?></p>
                    <p class="text-sm font-semibold mt-1">Rp <?= number_format($stat['total'], 0, ',', '.') ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pending Payments List -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="px-6 py-4 border-b dark:border-slate-700">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Menunggu Verifikasi (<?= count($pendingPayments) ?>)</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Verifikasi pembayaran untuk memproses order</p>
            </div>
            
            <div class="p-6">
                <?php if (empty($pendingPayments)): ?>
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-6xl text-gray-400">check_circle</span>
                    <p class="text-gray-500 dark:text-gray-400 mt-4">Tidak ada pembayaran yang menunggu verifikasi</p>
                    <p class="text-gray-400 text-sm mt-1">Semua pembayaran sudah diverifikasi</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pendingPayments as $payment): ?>
                    <div class="border dark:border-slate-700 rounded-xl p-4 hover:shadow-md transition card-animate">
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
                                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                                    <?= $payment['service_name'] ?> • <?= $payment['berat_cucian'] ?> kg
                                </p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">
                                    <span class="material-symbols-outlined text-sm align-middle">schedule</span>
                                    Tgl Order: <?= date('d/m/Y H:i', strtotime($payment['tanggal_order'])) ?>
                                </p>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">
                                    <span class="material-symbols-outlined text-sm align-middle">payments</span>
                                    Tgl Bayar: <?= date('d/m/Y H:i', strtotime($payment['tanggal_pembayaran'])) ?>
                                </p>
                                <p class="text-primary font-bold text-xl mt-2">Rp <?= number_format($payment['jumlah_bayar'],0,',','.') ?></p>
                                
                                <!-- Nomor Transaksi -->
                                <?php if ($payment['nomor_transaksi']): ?>
                                <p class="text-xs text-gray-400 mt-1">No. Transaksi: <?= $payment['nomor_transaksi'] ?></p>
                                <?php endif; ?>
                                
                                <!-- Catatan Customer -->
                                <?php if ($payment['catatan_pembayaran']): ?>
                                <p class="text-xs text-gray-500 mt-1 bg-gray-50 dark:bg-slate-700 p-2 rounded-lg">
                                    📝 Catatan: <?= htmlspecialchars($payment['catatan_pembayaran']) ?>
                                </p>
                                <?php endif; ?>
                                
                                <!-- Link Bukti Pembayaran -->
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
                                <button onclick="openVerifyModal(<?= $payment['id_order'] ?>)" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded-xl font-semibold transition flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">verified</span>
                                    Verifikasi
                                </button>
                                <button onclick="openRejectModal(<?= $payment['id_order'] ?>)" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-xl font-semibold transition flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Riwayat Verifikasi -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b dark:border-slate-700">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Riwayat Verifikasi</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">20 verifikasi terakhir</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Order ID</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Customer</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Metode</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold">Nominal</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Status</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Tgl Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($paymentHistory as $payment): ?>
                        <tr class="border-b dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50">
                            <td class="px-4 py-3 font-medium">#<?= $payment['id_order'] ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($payment['customer_name']) ?></td>
                            <td class="px-4 py-3 text-center uppercase"><?= $payment['metode'] ?></td>
                            <td class="px-4 py-3 text-right text-primary font-semibold">Rp <?= number_format($payment['jumlah_bayar'],0,',','.') ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge <?= $payment['status_bayar'] == 'lunas' ? 'badge-success' : 'badge-info' ?>">
                                    <?= $payment['status_bayar'] == 'lunas' ? 'Terverifikasi' : 'Gagal' ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm"><?= date('d/m/Y', strtotime($payment['tanggal_pembayaran'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($paymentHistory)): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada riwayat</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Info Card -->
        <div class="mt-6 bg-gradient-to-r from-primary/10 to-secondary/10 rounded-2xl p-5">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary">info</span>
                <div>
                    <p class="font-semibold text-gray-800 dark:text-white">Cara Verifikasi Pembayaran</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        1. Cek bukti pembayaran yang diupload customer<br>
                        2. Pastikan nominal sesuai dengan total order<br>
                        3. Klik "Verifikasi" untuk mengkonfirmasi pembayaran<br>
                        4. Order akan otomatis diproses oleh worker
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Verifikasi -->
<div id="verifyModal" class="modal">
    <div class="modal-content bg-white dark:bg-slate-800 rounded-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Konfirmasi Verifikasi</h3>
            <button onclick="closeVerifyModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Apakah Anda yakin ingin memverifikasi pembayaran ini?</p>
        <p class="text-sm text-gray-500 mb-4">Order akan segera diproses oleh worker.</p>
        <form method="post">
            <input type="hidden" name="verify_order_id" id="verifyOrderId">
            <div class="flex gap-3">
                <button type="button" onclick="closeVerifyModal()" class="flex-1 bg-gray-200 dark:bg-slate-700 py-2 rounded-xl font-semibold">Batal</button>
                <button type="submit" class="flex-1 bg-emerald-500 text-white py-2 rounded-xl font-semibold hover:bg-emerald-600">Ya, Verifikasi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tolak -->
<div id="rejectModal" class="modal">
    <div class="modal-content bg-white dark:bg-slate-800 rounded-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Tolak Pembayaran</h3>
            <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Berikan alasan penolakan:</p>
        <form method="post">
            <input type="hidden" name="reject_order_id" id="rejectOrderId">
            <textarea name="alasan_penolakan" rows="3" class="w-full border rounded-xl p-3 mb-4 dark:bg-slate-700" placeholder="Contoh: Bukti transfer tidak jelas, nominal tidak sesuai, dll" required></textarea>
            <div class="flex gap-3">
                <button type="button" onclick="closeRejectModal()" class="flex-1 bg-gray-200 dark:bg-slate-700 py-2 rounded-xl font-semibold">Batal</button>
                <button type="submit" class="flex-1 bg-red-500 text-white py-2 rounded-xl font-semibold hover:bg-red-600">Ya, Tolak</button>
            </div>
        </form>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav md:hidden">
    <div class="flex justify-around">
        <a href="supervisor.php" class="flex flex-col items-center gap-1 text-primary">
            <span class="material-symbols-outlined">verified</span>
            <span class="text-xs">Verify</span>
        </a>
        <a href="reports.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">assessment</span>
            <span class="text-xs">Reports</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">person</span>
            <span class="text-xs">Profile</span>
        </a>
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

// Close modal when clicking outside
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});
</script>

<?= global_route_script() ?>
</body>
</html>