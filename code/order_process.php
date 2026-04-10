<?php
require_once __DIR__ . '/common.php';
require_worker();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    if ($_POST['action'] === 'process_order') {
        $orderId = $_POST['order_id'];
        if (update_order_status($orderId, 'In Progress')) {
            set_flash('success', 'Order ' . htmlspecialchars($orderId) . ' berhasil diproses.');
        } else {
            set_flash('error', 'Gagal memproses order ' . htmlspecialchars($orderId) . '.');
        }
        header('Location: history.php');
        exit;
    }
}

$orderId = $_GET['order'] ?? $_POST['order_id'] ?? null;
$order = $orderId ? get_order($orderId) : null;
if (!$order) {
    set_flash('error', 'Order tidak ditemukan.');
    header('Location: history.php');
    exit;
}
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Worker Order Detail - Laundry Service</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#ec5b13",
                        "background-light": "#f8f6f6",
                        "background-dark": "#221610",
                    },
                    fontFamily: {
                        "display": ["Public Sans"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
<style>
        body {
            font-family: 'Public Sans', sans-serif;
        }
    </style>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen">
<div class="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden">
<!-- TopAppBar Navigation -->
<div class="flex items-center bg-white dark:bg-slate-900 p-4 border-b border-slate-200 dark:border-slate-800 justify-between sticky top-0 z-10">
<div class="text-slate-900 dark:text-slate-100 flex size-10 shrink-0 items-center justify-center cursor-pointer">
<span class="material-symbols-outlined">back</span>
</div>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-tight flex-1 px-4">Order <?= htmlspecialchars($order['id']) ?></h2>
<div class="flex size-10 items-center justify-end cursor-pointer">
<span class="material-symbols-outlined text-primary">more_vert</span>
</div>
</div>
<!-- Customer Profile Section -->
<div class="flex p-4 @container bg-white dark:bg-slate-900 mt-2">
<div class="flex w-full flex-col gap-4 @[520px]:flex-row @[520px]:justify-between @[520px]:items-center">
<div class="flex gap-4">
<div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full h-20 w-20 border-2 border-primary/20" data-alt="Portrait of customer Sarah Johnson" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDuaM0LbrFkx5IzfyS1clBq1PiOc-sx7-72xfhyweEt0OG6QhcRMYp72Tj_80mns8f2DxLUU4w9B1-AjJz7ePK7STe7Ty7CCkfc_79cYtV3WEtVQuzlF8sguS_5-ZFDVYVVLKoCGwcApefus1bT0a9G1mDDuQeq8hIVAYyIwipOefkS5SnGENTPJr9crCeJZgRveMnSmSpXxJAKb5WH5WG3IID6HNKu-uQmu2LyT7kKvN71CZSdw10fTyRY5gKzu8tWoKQS0OUbxWA");'></div>
<div class="flex flex-col justify-center">
<p class="text-slate-900 dark:text-slate-100 text-xl font-bold leading-tight tracking-tight"><?= htmlspecialchars($order['customer_name'] ?? 'Unknown Customer') ?></p>
<p class="text-slate-500 dark:text-slate-400 text-sm font-normal">Order Category: <?= htmlspecialchars($order['category'] ?? 'N/A') ?></p>
<div class="mt-1">
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary">
                                Premium Member
                            </span>
</div>
</div>
</div>
<div class="flex gap-2 @[520px]:flex-col">
<button class="flex-1 flex items-center justify-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-medium">
<span class="material-symbols-outlined text-lg">call</span> Call
                    </button>
<button class="flex-1 flex items-center justify-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-medium">
<span class="material-symbols-outlined text-lg">chat</span> Chat
                    </button>
</div>
</div>
</div>
<!-- Order Specifications -->
<div class="p-4">
<h3 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-tight mb-3">Order Information</h3>
<div class="space-y-2">
<!-- Service Type -->
<div class="flex items-center gap-4 bg-white dark:bg-slate-900 px-4 min-h-[72px] py-2 justify-between rounded-xl border border-slate-100 dark:border-slate-800">
<div class="flex items-center gap-4">
<div class="text-primary flex items-center justify-center rounded-lg bg-primary/10 shrink-0 size-12">
<span class="material-symbols-outlined">local_laundry_service</span>
</div>
<div class="flex flex-col justify-center">
<p class="text-slate-900 dark:text-slate-100 text-sm font-medium leading-none mb-1">Service Type</p>
<p class="text-slate-500 dark:text-slate-400 text-xs font-normal"><?= htmlspecialchars($order['service_type'] ?? 'Standard Full Service') ?></p>
</div>
</div>
<div class="shrink-0">
<p class="text-primary text-sm font-bold">Wash &amp; Fold</p>
</div>
</div>
<!-- Pickup Time -->
<div class="flex items-center gap-4 bg-white dark:bg-slate-900 px-4 min-h-[72px] py-2 justify-between rounded-xl border border-slate-100 dark:border-slate-800">
<div class="flex items-center gap-4">
<div class="text-primary flex items-center justify-center rounded-lg bg-primary/10 shrink-0 size-12">
<span class="material-symbols-outlined">schedule</span>
</div>
<div class="flex flex-col justify-center">
<p class="text-slate-900 dark:text-slate-100 text-sm font-medium leading-none mb-1">Pickup Time</p>
<p class="text-slate-500 dark:text-slate-400 text-xs font-normal">Scheduled Collection</p>
</div>
</div>
<div class="shrink-0 text-right">
<p class="text-slate-900 dark:text-slate-100 text-sm font-bold"><?= htmlspecialchars($order['pickup_date'] ?? 'Not set') ?></p>
<p class="text-slate-500 dark:text-slate-400 text-xs font-normal"><?= htmlspecialchars($order['pickup_time'] ?? 'Not set') ?></p>
</div>
</div>
<!-- Pickup Address -->
<div class="flex items-center gap-4 bg-white dark:bg-slate-900 px-4 min-h-[72px] py-2 justify-between rounded-xl border border-slate-100 dark:border-slate-800">
<div class="flex items-center gap-4">
<div class="text-primary flex items-center justify-center rounded-lg bg-primary/10 shrink-0 size-12">
<span class="material-symbols-outlined">location_on</span>
</div>
<div class="flex flex-col justify-center">
<p class="text-slate-900 dark:text-slate-100 text-sm font-medium leading-none mb-1">Pickup Location</p>
<p class="text-slate-500 dark:text-slate-400 text-xs font-normal max-w-[200px] truncate"><?= htmlspecialchars($order['address'] ?? 'Alamat tidak tersedia') ?></p>
</div>
</div>
<div class="shrink-0">
<span class="material-symbols-outlined text-slate-400">map</span>
</div>
</div>
</div>
</div>
<!-- Item Categories -->
<div class="p-4 pt-0">
<h3 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-tight mb-3">Items to Process</h3>
<div class="grid grid-cols-2 gap-3">
<div class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-100 dark:border-slate-800 flex items-center gap-3">
<div class="size-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
<span class="material-symbols-outlined">bed</span>
</div>
<div>
<p class="text-sm font-bold text-slate-900 dark:text-slate-100">Bedding</p>
<p class="text-xs text-slate-500 dark:text-slate-400">3 Pieces</p>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-100 dark:border-slate-800 flex items-center gap-3">
<div class="size-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
<span class="material-symbols-outlined">checkroom</span>
</div>
<div>
<p class="text-sm font-bold text-slate-900 dark:text-slate-100">Clothing</p>
<p class="text-xs text-slate-500 dark:text-slate-400">12 Pieces</p>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-100 dark:border-slate-800 flex items-center gap-3">
<div class="size-10 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400">
<span class="material-symbols-outlined">layers</span>
</div>
<div>
<p class="text-sm font-bold text-slate-900 dark:text-slate-100">Towels</p>
<p class="text-xs text-slate-500 dark:text-slate-400">5 Pieces</p>
</div>
</div>
<div class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-100 dark:border-slate-800 flex items-center gap-3">
<div class="size-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
<span class="material-symbols-outlined">nest_eco_leaf</span>
</div>
<div>
<p class="text-sm font-bold text-slate-900 dark:text-slate-100">Delicates</p>
<p class="text-xs text-slate-500 dark:text-slate-400">2 Pieces</p>
</div>
</div>
</div>
</div>
<!-- Notes -->
<div class="p-4 pt-0">
<div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl border border-amber-100 dark:border-amber-900/30">
<div class="flex items-center gap-2 mb-2">
<span class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-lg">sticky_note_2</span>
<p class="text-sm font-bold text-amber-900 dark:text-amber-100">Worker Notes</p>
</div>
<p class="text-sm text-amber-800 dark:text-amber-200">Please use hypoallergenic detergent. The blue comforter has a small stain near the corner that needs extra attention.</p>
</div>
</div>
<!-- Footer Action Button -->
<div class="mt-auto p-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 sticky bottom-0">
<div class="flex items-center justify-between mb-4">
<div class="flex flex-col">
<p class="text-xs text-slate-500 dark:text-slate-400">Total Est. Weight</p>
<p class="text-lg font-bold text-slate-900 dark:text-slate-100">~6.5 kg</p>
</div>
<div class="flex flex-col text-right">
<p class="text-xs text-slate-500 dark:text-slate-400">Service Fee</p>
<p class="text-lg font-bold text-primary">$42.00</p>
</div>
</div>
<form method="post" action="" class="">
    <input type="hidden" name="action" value="process_order" />
    <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderId) ?>" />
    <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/20 flex items-center justify-center gap-3 transition-colors">
        <span class="material-symbols-outlined">sync</span>
        Process Order (Diproses)
    </button>
</form>
</div>
</div>
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 px-4 py-3">
    <div class="flex gap-2">
        <a class="flex-1 text-center text-slate-500 hover:text-primary" href="dashboard.php">Home</a>
        <a class="flex-1 text-center text-slate-500 hover:text-primary" href="neworder.php">New Order</a>
        <a class="flex-1 text-center text-primary font-bold" href="history.php">Orders</a>
        <a class="flex-1 text-center text-slate-500 hover:text-primary" href="profile.php">Profile</a>
    </div>
</div>
<?php echo global_route_script(); ?>
</body></html>

