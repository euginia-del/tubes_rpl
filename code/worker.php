<?php
require_once 'common.php';
require_worker();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    if ($_POST['action'] === 'mark_in_progress') {
        update_order_status($_POST['order_id'], 'In Progress');
        set_flash('success', 'Order ' . htmlspecialchars($_POST['order_id']) . ' diproses.');
        header('Location: worker.php');
        exit;
    }
    if ($_POST['action'] === 'mark_completed') {
        update_order_status($_POST['order_id'], 'Completed');
        set_flash('success', 'Order ' . htmlspecialchars($_POST['order_id']) . ' selesai.');
        header('Location: worker.php');
        exit;
    }
}

$orders = get_orders();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Worker - Laundry Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet" />
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl pt-4 pb-24">
    <div class="px-4 flex items-center justify-between">
        <h1 class="text-xl font-bold">Worker Dashboard</h1>
        <button id="themeToggle" class="text-slate-500 dark:text-slate-200 text-xs font-semibold px-2 py-1 rounded-full border border-slate-200 dark:border-slate-700">Mode</button>
    </div>
    <div class="px-4 mt-3">
        <?php if ($msg = get_flash('success')): ?>
            <div class="mb-2 bg-emerald-100 border border-emerald-200 text-emerald-800 p-3 rounded-lg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if ($err = get_flash('error')): ?>
            <div class="mb-2 bg-red-100 border border-red-200 text-red-800 p-3 rounded-lg"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>
    </div>
    <div class="px-4">
        <?php if (empty($orders)): ?>
            <p class="text-slate-500">Tidak ada order saat ini.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($orders as $order): ?>
                    <div class="border rounded-xl p-3 bg-slate-50 dark:bg-slate-800">
                        <p class="font-semibold">Order <?= htmlspecialchars($order['id'] ?? 'ID-?') ?> - <?= htmlspecialchars($order['status'] ?? 'Pending') ?></p>
                        <p class="text-sm">Customer: <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
                        <p class="text-sm">Category: <?= htmlspecialchars($order['category'] ?? 'N/A') ?> - Service: <?= htmlspecialchars($order['service_type'] ?? 'N/A') ?></p>
                        <div class="mt-2 flex gap-2">
                            <?php if (($order['status'] ?? '') !== 'In Progress'): ?>
                                <form method="post">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>" />
                                    <input type="hidden" name="action" value="mark_in_progress" />
                                    <button class="px-3 py-2 bg-blue-600 text-white rounded-lg text-xs">Mark In Progress</button>
                                </form>
                            <?php endif; ?>
                            <?php if (($order['status'] ?? '') !== 'Completed'): ?>
                                <form method="post">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>" />
                                    <input type="hidden" name="action" value="mark_completed" />
                                    <button class="px-3 py-2 bg-green-600 text-white rounded-lg text-xs">Mark Completed</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 px-4 py-3">
        <div class="flex gap-2">
            <a class="flex-1 text-center text-slate-500 hover:text-primary" href="worker.php">Orders</a>
            <a class="flex-1 text-center text-slate-500 hover:text-primary" href="login.php?logout=1">Logout</a>
        </div>
    </div>
</div>
<?= global_route_script() ?>
</body>
</html>
