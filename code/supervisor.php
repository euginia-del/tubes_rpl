<?php
require_once 'common.php';
require_supervisor();

$db = get_db();

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

        <!-- Recent Orders Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b dark:border-slate-700">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Recent Orders</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Order ID</th>
                            <th class="px-4 py-3 text-left">Customer</th>
                            <th class="px-4 py-3 text-left">Service</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $stmt = $db->query('
                            SELECT o.*, u.nama as customer_name, l.nama_layanan as service_name
                            FROM orders o
                            JOIN user u ON o.id_user = u.id_user
                            JOIN layanan l ON o.id_layanan = l.id_layanan
                            ORDER BY o.tanggal_order DESC
                            LIMIT 10
                        ');
                        $recentOrders = $stmt->fetchAll();
                        ?>
                        <?php foreach($recentOrders as $order): ?>
                        <tr class="border-b dark:border-slate-700">
                            <td class="px-4 py-3">#<?= $order['id_order'] ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($order['service_name']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge <?= $order['status_order'] == 'selesai' ? 'badge-success' : ($order['status_order'] == 'proses' ? 'badge-info' : 'badge-warning') ?>">
                                    <?= $order['status_order'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></td>
                        </tr>
                        <?php endforeach; ?>
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