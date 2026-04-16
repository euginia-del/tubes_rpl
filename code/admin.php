<?php
require_once 'common.php';
require_admin();

$db = get_db();
$totalOrders = get_order_count($db);
$completed = get_completed_orders_count($db);
$pending = get_pending_orders_count($db);
$totalUsers = get_user_count($db);
$allOrders = get_all_orders($db);

// Get pending payments for admin verification
$stmt = $db->prepare('
    SELECT p.*, o.id_order, o.harga_snapshot, u.nama as customer_name
    FROM pembayaran p
    JOIN orders o ON p.id_order = o.id_order
    JOIN user u ON o.id_user = u.id_user
    WHERE p.status_bayar = "pending"
    ORDER BY p.tanggal_pembayaran DESC
');
$stmt->execute();
$pendingPayments = $stmt->fetchAll();

// Get recent users
$stmt = $db->prepare('SELECT * FROM user WHERE role = "customer" ORDER BY id_user DESC LIMIT 5');
$stmt->execute();
$recentUsers = $stmt->fetchAll();

// Get monthly revenue
$stmt = $db->prepare('
    SELECT 
        DATE_FORMAT(tanggal_order, "%Y-%m") as period,
        SUM(harga_snapshot) as total_revenue
    FROM orders 
    WHERE YEAR(tanggal_order) = YEAR(NOW())
    GROUP BY DATE_FORMAT(tanggal_order, "%Y-%m")
    ORDER BY period DESC
    LIMIT 6
');
$stmt->execute();
$monthlyRevenue = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Admin Dashboard - LaundryApp</title>
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
        <a href="admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <a href="verify_payments.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
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
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">Admin Dashboard</h1>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg card-animate">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1"><?= $totalOrders ?></p>
                    </div>
                    <span class="material-symbols-outlined text-3xl text-primary">receipt_long</span>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg card-animate">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Completed</p>
                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1"><?= $completed ?></p>
                    </div>
                    <span class="material-symbols-outlined text-3xl text-emerald-500">task_alt</span>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg card-animate">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Pending</p>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1"><?= $pending ?></p>
                    </div>
                    <span class="material-symbols-outlined text-3xl text-amber-500">schedule</span>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg card-animate">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Total Users</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1"><?= $totalUsers ?></p>
                    </div>
                    <span class="material-symbols-outlined text-3xl text-primary">group</span>
                </div>
            </div>
        </div>

        <!-- Pending Payments Alert -->
        <?php if (count($pendingPayments) > 0): ?>
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-4 mb-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-amber-600">warning</span>
                <div>
                    <p class="font-semibold text-amber-800 dark:text-amber-300"><?= count($pendingPayments) ?> Pembayaran Menunggu Verifikasi</p>
                    <p class="text-sm text-amber-600 dark:text-amber-400">Segera verifikasi pembayaran customer</p>
                </div>
            </div>
            <a href="verify_payments.php" class="bg-amber-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-amber-600 transition">Verifikasi Sekarang</a>
        </div>
        <?php endif; ?>

        <!-- Revenue Chart -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg mb-8">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Revenue Overview (Last 6 Months)</h2>
            <canvas id="revenueChart" height="200"></canvas>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <a href="verify_payments.php" class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-lg hover-lift card-animate">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl">verified</span>
                    <div>
                        <h3 class="font-bold">Verifikasi Pembayaran</h3>
                        <p class="text-sm opacity-90">Verifikasi bukti pembayaran customer</p>
                    </div>
                </div>
            </a>
            <a href="reports.php" class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-2xl p-5 text-white shadow-lg hover-lift card-animate">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl">assessment</span>
                    <div>
                        <h3 class="font-bold">Reports & Analytics</h3>
                        <p class="text-sm opacity-90">View financial reports</p>
                    </div>
                </div>
            </a>
            <a href="history.php" class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg hover-lift card-animate">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl">receipt_long</span>
                    <div>
                        <h3 class="font-bold">All Orders</h3>
                        <p class="text-sm opacity-90">View complete order history</p>
                    </div>
                </div>
            </a>
            <a href="users.php" class="bg-gradient-to-r from-orange-500 to-red-600 rounded-2xl p-5 text-white shadow-lg hover-lift card-animate">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl">group_add</span>
                    <div>
                        <h3 class="font-bold">Manage Users</h3>
                        <p class="text-sm opacity-90">View and manage customers</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Recent Orders -->
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Recent Orders</h2>
                    <a href="history.php" class="text-primary text-sm hover:underline">View All →</a>
                </div>
                <div class="space-y-3">
                    <?php foreach (array_slice($allOrders, 0, 5) as $order): ?>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm card-animate">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <p class="font-bold text-gray-800 dark:text-white">#<?= $order['id_order'] ?></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
                            </div>
                            <span class="badge <?= $order['status_order'] == 'selesai' ? 'badge-success' : ($order['status_order'] == 'proses' ? 'badge-info' : 'badge-warning') ?>">
                                <?= $order['status_order'] ?>
                            </span>
                        </div>
                        <p class="text-primary font-bold mt-2">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Users -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">New Customers</h2>
                    <a href="users.php" class="text-primary text-sm hover:underline">View All →</a>
                </div>
                <div class="space-y-3">
                    <?php foreach ($recentUsers as $user): ?>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm card-animate">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-sm">person</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($user['nama']) ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
                            </div>
                            <span class="badge badge-info">customer</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueData = <?php 
    $revData = array_fill(0, 6, 0);
    $i = 0;
    foreach(array_reverse($monthlyRevenue) as $rev) {
        $revData[$i] = $rev['total_revenue'];
        $i++;
    }
    echo json_encode($revData);
?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6'],
        datasets: [{
            label: 'Revenue (Rp)',
            data: revenueData,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#6366f1',
            pointBorderColor: '#fff',
            pointRadius: 4,
            pointHoverRadius: 6
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