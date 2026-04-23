<?php
require_once 'common.php';
require_supervisor();

$db = get_db();

// Ambil parameter filter
$filter_customer = $_GET['customer'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';
$filter_date = $_GET['date'] ?? 'all';

// Get statistics for overview
$totalOrders = get_order_count($db);
$completedOrders = get_completed_orders_count($db);
$pendingOrders = get_pending_orders_count($db);
$processOrders = get_processed_orders_count($db);
$totalRevenue = $db->query('SELECT SUM(harga_snapshot) FROM orders WHERE status_order = "selesai"')->fetchColumn();

// Get monthly revenue
$stmt = $db->prepare('
    SELECT DATE_FORMAT(tanggal_order, "%Y-%m") as month, 
           COUNT(*) as total_orders,
           SUM(harga_snapshot) as revenue
    FROM orders 
    WHERE YEAR(tanggal_order) = YEAR(NOW())
    GROUP BY DATE_FORMAT(tanggal_order, "%Y-%m")
    ORDER BY month DESC
');
$stmt->execute();
$monthlyData = $stmt->fetchAll();

// Build query untuk recent orders dengan filter
$sql = '
    SELECT o.*, u.nama as customer_name, l.nama_layanan as service_name
    FROM orders o
    JOIN user u ON o.id_user = u.id_user
    JOIN layanan l ON o.id_layanan = l.id_layanan
    WHERE 1=1
';

$params = [];

// Filter by customer
if ($filter_customer !== 'all') {
    $sql .= ' AND u.id_user = ?';
    $params[] = $filter_customer;
}

// Filter by status
if ($filter_status !== 'all') {
    $sql .= ' AND o.status_order = ?';
    $params[] = $filter_status;
}

// Filter by date
if ($filter_date === 'today') {
    $sql .= ' AND DATE(o.tanggal_order) = CURDATE()';
} elseif ($filter_date === 'week') {
    $sql .= ' AND YEARWEEK(o.tanggal_order) = YEARWEEK(CURDATE())';
} elseif ($filter_date === 'month') {
    $sql .= ' AND MONTH(o.tanggal_order) = MONTH(CURDATE()) AND YEAR(o.tanggal_order) = YEAR(CURDATE())';
}

$sql .= ' ORDER BY o.tanggal_order DESC LIMIT 50';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$recentOrders = $stmt->fetchAll();

// Get all customers list for filter
$stmt = $db->query('SELECT id_user, nama, email FROM user WHERE role = "customer" ORDER BY nama ASC');
$allCustomers = $stmt->fetchAll();

// Get status counts for filter badges
$statusCounts = [
    'pending' => get_pending_orders_count($db),
    'proses' => get_processed_orders_count($db),
    'selesai' => get_completed_orders_count($db)
];
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
<script>
function applyFilter() {
    var customer = document.getElementById('filter_customer').value;
    var status = document.getElementById('filter_status').value;
    var date = document.getElementById('filter_date').value;
    window.location.href = 'supervisor.php?customer=' + customer + '&status=' + status + '&date=' + date;
}

function resetFilter() {
    window.location.href = 'supervisor.php';
}
</script>
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
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
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
                <p class="text-xs text-gray-500 dark:text-gray-400">Monitoring Access</p>
            </div>
            <button id="themeToggle" class="text-xs px-2 py-1 rounded-full border dark:border-slate-600">🌙</button>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">Supervisor Dashboard</h1>

        <!-- Stats Overview -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg">
                <p class="text-gray-500 text-sm">Total Orders</p>
                <p class="text-2xl font-bold"><?= $totalOrders ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg">
                <p class="text-gray-500 text-sm">Pending</p>
                <p class="text-2xl font-bold text-amber-600"><?= $pendingOrders ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg">
                <p class="text-gray-500 text-sm">In Process</p>
                <p class="text-2xl font-bold text-purple-600"><?= $processOrders ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg">
                <p class="text-gray-500 text-sm">Completed</p>
                <p class="text-2xl font-bold text-emerald-600"><?= $completedOrders ?></p>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="bg-gradient-to-r from-primary to-secondary rounded-2xl p-6 text-white shadow-lg mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm">Total Revenue</p>
                    <p class="text-3xl font-bold">Rp <?= number_format($totalRevenue ?? 0, 0, ',', '.') ?></p>
                </div>
                <span class="material-symbols-outlined text-5xl text-white/30">payments</span>
            </div>
        </div>

        <!-- Monthly Revenue Chart -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg mb-8">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Monthly Revenue <?= date('Y') ?></h2>
            <canvas id="revenueChart" height="200"></canvas>
        </div>

        <!-- Filter Section -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg mb-6">
            <h3 class="text-md font-semibold text-gray-800 dark:text-white mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">filter_list</span>
                Filter Orders
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                    <select id="filter_customer" onchange="applyFilter()" class="w-full border rounded-lg p-2 mt-1 dark:bg-slate-700">
                        <option value="all" <?= $filter_customer == 'all' ? 'selected' : '' ?>>Semua Customer</option>
                        <?php foreach($allCustomers as $customer): ?>
                        <option value="<?= $customer['id_user'] ?>" <?= $filter_customer == $customer['id_user'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($customer['nama']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select id="filter_status" onchange="applyFilter()" class="w-full border rounded-lg p-2 mt-1 dark:bg-slate-700">
                        <option value="all" <?= $filter_status == 'all' ? 'selected' : '' ?>>Semua Status</option>
                        <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>Pending (<?= $statusCounts['pending'] ?>)</option>
                        <option value="proses" <?= $filter_status == 'proses' ? 'selected' : '' ?>>Proses (<?= $statusCounts['proses'] ?>)</option>
                        <option value="selesai" <?= $filter_status == 'selesai' ? 'selected' : '' ?>>Selesai (<?= $statusCounts['selesai'] ?>)</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Periode</label>
                    <select id="filter_date" onchange="applyFilter()" class="w-full border rounded-lg p-2 mt-1 dark:bg-slate-700">
                        <option value="all" <?= $filter_date == 'all' ? 'selected' : '' ?>>Semua Waktu</option>
                        <option value="today" <?= $filter_date == 'today' ? 'selected' : '' ?>>Hari Ini</option>
                        <option value="week" <?= $filter_date == 'week' ? 'selected' : '' ?>>Minggu Ini</option>
                        <option value="month" <?= $filter_date == 'month' ? 'selected' : '' ?>>Bulan Ini</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="resetFilter()" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="px-6 py-4 border-b dark:border-slate-700 bg-cyan-50 dark:bg-cyan-900/20 flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-bold text-cyan-800 dark:text-cyan-300">Recent Orders</h2>
                    <p class="text-sm text-cyan-600">Menampilkan <?= count($recentOrders) ?> order</p>
                </div>
                <div class="text-sm text-gray-500">
                    <?php if ($filter_customer != 'all'): ?>
                    <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                        Customer: <?php 
                            $cust = array_filter($allCustomers, fn($c) => $c['id_user'] == $filter_customer);
                            echo htmlspecialchars(reset($cust)['nama'] ?? '');
                        ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($filter_status != 'all'): ?>
                    <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 px-2 py-1 rounded-full text-xs">
                        Status: <?= $filter_status ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($filter_date != 'all'): ?>
                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                        Periode: <?= $filter_date == 'today' ? 'Hari Ini' : ($filter_date == 'week' ? 'Minggu Ini' : 'Bulan Ini') ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Order ID</th>
                            <th class="px-4 py-3 text-left">Customer</th>
                            <th class="px-4 py-3 text-left">Service</th>
                            <th class="px-4 py-3 text-center">Weight</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <span class="material-symbols-outlined text-4xl">inbox</span>
                                <p class="mt-2">Tidak ada order yang sesuai dengan filter</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($recentOrders as $order): ?>
                            <tr class="border-b dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                <td class="px-4 py-3 font-medium">#<?= $order['id_order'] ?></td>
                                <td class="px-4 py-3">
                                    <span class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-sm text-gray-400">person</span>
                                        <?= htmlspecialchars($order['customer_name']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3"><?= htmlspecialchars($order['service_name']) ?> </td>
                                <td class="px-4 py-3 text-center"><?= $order['berat_cucian'] ?> kg</td>
                                <td class="px-4 py-3 text-right font-semibold">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge <?= $order['status_order'] == 'selesai' ? 'badge-success' : ($order['status_order'] == 'proses' ? 'badge-info' : 'badge-warning') ?>">
                                        <?= $order['status_order'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm"><?= date('d/m/Y', strtotime($order['tanggal_order'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daftar Semua Customer -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b dark:border-slate-700">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Daftar Customer</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Semua customer yang terdaftar (<?= count($allCustomers) ?> customer)</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left">ID</th>
                            <th class="px-4 py-3 text-left">Nama Customer</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-center">Total Orders</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($allCustomers as $customer): 
                            // Hitung total order customer ini
                            $stmt = $db->prepare('SELECT COUNT(*) FROM orders WHERE id_user = ?');
                            $stmt->execute([$customer['id_user']]);
                            $totalOrderCustomer = $stmt->fetchColumn();
                        ?>
                        <tr class="border-b dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50">
                            <td class="px-4 py-3">#<?= $customer['id_user'] ?> </td>
                            <td class="px-4 py-3">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-gray-400">person</span>
                                    <?= htmlspecialchars($customer['nama']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3"><?= htmlspecialchars($customer['email']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge badge-info"><?= $totalOrderCustomer ?> orders</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="supervisor.php?customer=<?= $customer['id_user'] ?>" class="text-primary text-sm hover:underline">
                                    Lihat Orders →
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($allCustomers)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada customer terdaftar</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const months = <?php 
    $data = array_fill(1, 12, 0);
    foreach($monthlyData as $m) {
        $monthNum = intval(substr($m['month'], 5));
        $data[$monthNum] = $m['revenue'];
    }
    echo json_encode(array_values($data));
?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Revenue (Rp)',
            data: months,
            backgroundColor: 'rgba(6, 182, 212, 0.6)',
            borderColor: '#06b6d4',
            borderWidth: 1,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: { y: { ticks: { callback: (value) => `Rp ${value.toLocaleString('id-ID')}` } } }
    }
});
</script>

<?= global_route_script() ?>
</body>
</html>