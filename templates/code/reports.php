<?php
require_once 'common.php';

// Allow both admin and supervisor
$user = currentUser();
if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'supervisor')) {
    header('Location: login.php');
    exit;
}

$db = get_db();
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$role = $user['role'];

// Get data from laporan table
$stmt = $db->prepare('
    SELECT 
        periode_tahun,
        periode_bulan,
        COUNT(*) as total_orders,
        SUM(total_harga) as total_revenue
    FROM laporan
    WHERE periode_tahun = ?
    GROUP BY periode_tahun, periode_bulan
    ORDER BY periode_bulan ASC
');
$stmt->execute([$year]);
$monthlyReports = $stmt->fetchAll();

// Get daily report for selected month from laporan
$stmt = $db->prepare('
    SELECT 
        DATE(tanggal_order) as date,
        COUNT(o.id_order) as orders,
        SUM(o.harga_snapshot) as revenue
    FROM orders o
    JOIN laporan l ON o.id_order = l.id_order
    WHERE YEAR(o.tanggal_order) = ? AND MONTH(o.tanggal_order) = ?
    GROUP BY DATE(o.tanggal_order)
    ORDER BY date DESC
');
$stmt->execute([$year, $month]);
$dailyReports = $stmt->fetchAll();

// Get service popularity from orders that are completed
$stmt = $db->prepare('
    SELECT 
        l.nama_layanan,
        COUNT(o.id_order) as total_orders,
        SUM(o.harga_snapshot) as total_revenue
    FROM orders o
    JOIN layanan l ON o.id_layanan = l.id_layanan
    JOIN laporan lp ON o.id_order = lp.id_order
    WHERE YEAR(o.tanggal_order) = ?
    GROUP BY o.id_layanan
    ORDER BY total_orders DESC
');
$stmt->execute([$year]);
$serviceStats = $stmt->fetchAll();

// Get total stats from laporan
$stmt = $db->query('SELECT COUNT(*) as total_orders, SUM(total_harga) as total_revenue FROM laporan');
$totalStats = $stmt->fetch();

$stmt = $db->query('SELECT COUNT(*) as total_users FROM user WHERE role = "customer"');
$userCount = $stmt->fetch();

// Get pending payments count
$stmt = $db->query('SELECT COUNT(*) as pending_payments FROM pembayaran WHERE status_bayar = "pending"');
$pendingPayments = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Reports - LaundryApp</title>
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
            <span class="text-xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                <?= $role === 'admin' ? 'Admin Panel' : 'Supervisor Panel' ?>
            </span>
        </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-2">
        <?php if ($role === 'admin'): ?>
        <a href="admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <?php else: ?>
        <a href="supervisor.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">verified</span>
            <span>Verify Payments</span>
        </a>
        <?php endif; ?>
        
        <a href="reports.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
            <span class="material-symbols-outlined">assessment</span>
            <span>Reports</span>
        </a>
        
        <?php if ($role === 'admin'): ?>
        <a href="history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">receipt_long</span>
            <span>All Orders</span>
        </a>
        <a href="price.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">price_check</span>
            <span>Services</span>
        </a>
        <?php endif; ?>
        
        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">account_circle</span>
            <span>Profile</span>
        </a>
    </nav>
    
    <div class="p-4 border-t dark:border-slate-700">
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">
                    <?= $role === 'admin' ? 'admin_panel_settings' : 'supervisor_account' ?>
                </span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 dark:text-white"><?= ucfirst($role) ?></p>
                <p class="text-xs text-gray-500 dark:text-gray-400"><?= $role === 'admin' ? 'Full Access' : 'Verification Access' ?></p>
            </div>
            <button id="themeToggle" class="text-xs px-2 py-1 rounded-full border dark:border-slate-600">🌙</button>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">Laporan Keuangan</h1>
            <?php if ($role === 'supervisor'): ?>
            <a href="supervisor.php" class="flex items-center gap-2 bg-amber-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-amber-600 transition">
                <span class="material-symbols-outlined text-sm">payments</span>
                <span><?= $pendingPayments['pending_payments'] ?? 0 ?> Pending</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg">
                <p class="text-gray-500 dark:text-gray-400 text-sm">Total Orders Selesai</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= number_format($totalStats['total_orders'] ?? 0) ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg">
                <p class="text-gray-500 dark:text-gray-400 text-sm">Total Pendapatan</p>
                <p class="text-2xl font-bold text-emerald-600">Rp <?= number_format($totalStats['total_revenue'] ?? 0, 0, ',', '.') ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg">
                <p class="text-gray-500 dark:text-gray-400 text-sm">Total Customer</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= number_format($userCount['total_users'] ?? 0) ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg">
                <p class="text-gray-500 dark:text-gray-400 text-sm">Rata-rata per Order</p>
                <p class="text-2xl font-bold text-primary">Rp <?= number_format(($totalStats['total_revenue'] ?? 0) / max(($totalStats['total_orders'] ?? 1), 1), 0, ',', '.') ?></p>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-lg mb-6">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tahun</label>
                    <select name="year" class="border rounded-xl p-2 bg-gray-50 dark:bg-slate-700">
                        <?php for($y = 2023; $y <= date('Y'); $y++): ?>
                        <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Bulan</label>
                    <select name="month" class="border rounded-xl p-2 bg-gray-50 dark:bg-slate-700">
                        <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $month == $m ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded-xl font-semibold hover:bg-primary-dark transition">Filter</button>
                </div>
            </form>
        </div>

        <!-- Monthly Revenue Chart -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg mb-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Pendapatan per Bulan - <?= $year ?></h2>
            <canvas id="revenueChart" height="200"></canvas>
        </div>

        <!-- Service Popularity -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg mb-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Popularitas Layanan - <?= $year ?></h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b dark:border-slate-700">
                            <th class="text-left py-3">Layanan</th>
                            <th class="text-center py-3">Jumlah Order</th>
                            <th class="text-right py-3">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($serviceStats as $stat): ?>
                        <tr class="border-b dark:border-slate-700">
                            <td class="py-3"><?= htmlspecialchars($stat['nama_layanan']) ?></td>
                            <td class="text-center py-3"><?= number_format($stat['total_orders']) ?></td>
                            <td class="text-right py-3 text-primary font-semibold">Rp <?= number_format($stat['total_revenue'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($serviceStats)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-8 text-gray-500">Belum ada data laporan</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daily Report for Selected Month -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Laporan Harian - <?= date('F Y', mktime(0,0,0,$month,1)) ?></h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b dark:border-slate-700">
                            <th class="text-left py-3">Tanggal</th>
                            <th class="text-center py-3">Jumlah Order</th>
                            <th class="text-right py-3">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dailyReports as $report): ?>
                        <tr class="border-b dark:border-slate-700">
                            <td class="py-3"><?= date('d F Y', strtotime($report['date'])) ?></td>
                            <td class="text-center py-3"><?= number_format($report['orders']) ?></td>
                            <td class="text-right py-3 text-primary font-semibold">Rp <?= number_format($report['revenue'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($dailyReports)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-8 text-gray-500">Belum ada data untuk periode ini</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Export Button (Admin only) -->
        <?php if ($role === 'admin'): ?>
        <div class="mt-6 flex justify-end">
            <button onclick="window.print()" class="flex items-center gap-2 bg-gray-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-600 transition">
                <span class="material-symbols-outlined">print</span>
                <span>Cetak Laporan</span>
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const monthsData = <?php 
    $monthlyData = array_fill(1, 12, 0);
    foreach($monthlyReports as $report) {
        $monthlyData[$report['periode_bulan']] = $report['total_revenue'];
    }
    echo json_encode(array_values($monthlyData));
?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: monthsData,
            backgroundColor: 'rgba(99, 102, 241, 0.6)',
            borderColor: '#6366f1',
            borderWidth: 1,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: { callbacks: { label: (ctx) => `Rp ${ctx.raw.toLocaleString('id-ID')}` } }
        },
        scales: {
            y: { ticks: { callback: (value) => `Rp ${value.toLocaleString('id-ID')}` } }
        }
    }
});
</script>

<?= global_route_script() ?>
</body>
</html>