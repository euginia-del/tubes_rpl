<?php
require_once 'common.php';
require_worker();

$db = get_db();

// Handle status update via dropdown
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['new_status'];
    
    if (update_order_status($orderId, $newStatus)) {
        // Jika status menjadi selesai, insert ke laporan
        if ($newStatus === 'selesai') {
            $stmt = $db->prepare('SELECT id_user, tanggal_order, harga_snapshot FROM orders WHERE id_order = ?');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            if ($order) {
                $stmt = $db->prepare('SELECT COUNT(*) FROM laporan WHERE id_order = ?');
                $stmt->execute([$orderId]);
                $exists = $stmt->fetchColumn();
                
                if (!$exists) {
                    $stmt = $db->prepare('
                        INSERT INTO laporan (id_order, id_user, periode_bulan, periode_tahun, total_harga)
                        VALUES (?, ?, ?, ?, ?)
                    ');
                    $stmt->execute([
                        $orderId,
                        $order['id_user'],
                        date('n', strtotime($order['tanggal_order'])),
                        date('Y', strtotime($order['tanggal_order'])),
                        $order['harga_snapshot']
                    ]);
                }
            }
        }
        set_flash('success', 'Order #' . $orderId . ' status diubah menjadi ' . $newStatus);
    } else {
        set_flash('error', 'Gagal mengubah status order');
    }
    header('Location: worker.php');
    exit;
}

// Get all orders
$stmt = $db->prepare('
    SELECT o.*, u.nama as customer_name, l.nama_layanan as service_name 
    FROM orders o 
    LEFT JOIN user u ON o.id_user = u.id_user 
    LEFT JOIN layanan l ON o.id_layanan = l.id_layanan 
    ORDER BY o.tanggal_order DESC
');
$stmt->execute();
$allOrders = $stmt->fetchAll();

$pendingOrders = array_filter($allOrders, function($o) { 
    return $o['status_order'] === 'pending'; 
});
$processOrders = array_filter($allOrders, function($o) { 
    return $o['status_order'] === 'proses'; 
});
$completedOrders = array_filter($allOrders, function($o) { 
    return $o['status_order'] === 'selesai'; 
});
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Worker Dashboard - LaundryApp</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="style.css">
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: { "primary": "#f97316", "secondary": "#ef4444" },
            fontFamily: { "display": ["Inter", "sans-serif"] }
        }
    }
}
</script>
</head>
<body class="bg-gradient-main font-display text-slate-900 dark:text-slate-100 min-h-screen">
<div class="laundry-pattern"></div>

<!-- Desktop Sidebar -->
<div class="hidden md:flex md:fixed md:inset-y-0 md:left-0 md:w-72 bg-white dark:bg-slate-800 shadow-xl flex-col z-10">
    <div class="flex items-center justify-center p-6 border-b dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">handyman</span>
            </div>
            <span class="text-xl font-bold text-primary">Worker Panel</span>
        </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-2">
        <a href="worker.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">account_circle</span>
            <span>Profile</span>
        </a>
    </nav>
    
    <div class="p-4 border-t dark:border-slate-700">
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">engineering</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 dark:text-white">Worker</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Staff Access</p>
            </div>
            <button id="themeToggle" class="text-xs px-2 py-1 rounded-full border dark:border-slate-600">🌙</button>
        </div>
    </div>
</div>

<!-- Mobile Header -->
<div class="md:hidden bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-40">
    <div class="flex items-center justify-between px-4 py-3">
        <span class="text-lg font-bold text-primary">Worker Panel</span>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">Worker Dashboard</h1>

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

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-lg card-animate">
                <p class="text-gray-500 dark:text-gray-400 text-sm">Total Orders</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= count($allOrders) ?></p>
            </div>
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-5 text-white shadow-lg">
                <p class="text-amber-100 text-sm">Pending</p>
                <p class="text-2xl font-bold"><?= count($pendingOrders) ?></p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg">
                <p class="text-purple-100 text-sm">In Process</p>
                <p class="text-2xl font-bold"><?= count($processOrders) ?></p>
            </div>
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg">
                <p class="text-emerald-100 text-sm">Completed</p>
                <p class="text-2xl font-bold"><?= count($completedOrders) ?></p>
            </div>
        </div>

        <!-- All Orders Table with Dropdown -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b dark:border-slate-700">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">All Orders (<?= count($allOrders) ?>)</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Klik dropdown untuk mengubah status order</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left">ID</th>
                            <th class="px-4 py-3 text-left">Customer</th>
                            <th class="px-4 py-3 text-left">Service</th>
                            <th class="px-4 py-3 text-center">Weight</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allOrders)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                Belum ada order
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($allOrders as $order): ?>
                            <tr class="border-b dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                <td class="px-4 py-3 font-medium">#<?= $order['id_order'] ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($order['service_name'] ?? 'Layanan') ?></td>
                                <td class="px-4 py-3 text-center"><?= $order['berat_cucian'] ?> kg</td>
                                <td class="px-4 py-3 text-right text-primary font-semibold">Rp <?= number_format($order['harga_snapshot'],0,',','.') ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge <?= $order['status_order'] == 'selesai' ? 'badge-success' : ($order['status_order'] == 'proses' ? 'badge-info' : 'badge-warning') ?>">
                                        <?= $order['status_order'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <form method="post" class="inline-block" onsubmit="return confirm('Ubah status order #<?= $order['id_order'] ?> menjadi ' + this.querySelector('select').value + '?')">
                                        <input type="hidden" name="order_id" value="<?= $order['id_order'] ?>">
                                        <select name="new_status" onchange="this.form.submit()" 
                                            class="text-sm border rounded-lg px-3 py-1.5 cursor-pointer font-semibold
                                            <?= $order['status_order'] == 'selesai' ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : ($order['status_order'] == 'proses' ? 'bg-purple-50 border-purple-300 text-purple-700' : 'bg-amber-50 border-amber-300 text-amber-700') ?>">
                                            <option value="pending" <?= $order['status_order'] == 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                            <option value="proses" <?= $order['status_order'] == 'proses' ? 'selected' : '' ?>>🔄 Proses</option>
                                            <option value="selesai" <?= $order['status_order'] == 'selesai' ? 'selected' : '' ?>>✅ Selesai</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav md:hidden">
    <div class="flex justify-around">
        <a href="worker.php" class="flex flex-col items-center gap-1 text-primary">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="text-xs">Home</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center gap-1 text-gray-500 dark:text-gray-400">
            <span class="material-symbols-outlined">person</span>
            <span class="text-xs">Profile</span>
        </a>
    </div>
</div>

<?= global_route_script() ?>
</body>
</html>