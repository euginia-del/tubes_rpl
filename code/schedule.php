<?php
require_once __DIR__ . '/common.php';
require_customer();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'finish_order') {
    $orderId = finish_current_order();
    if ($orderId) {
        set_flash('success', 'Order ' . htmlspecialchars($orderId) . ' berhasil dibuat.');
header('Location: history.php');
    exit;
}
    set_flash('error', 'Gagal membuat order. Silakan coba lagi.');
    header('Location: order_detail.php');
    exit;
}

$currentOrder = get_current_order();
if (empty($currentOrder)) {
    set_flash('error', 'Tidak ada order aktif, silahkan isi order terlebih dahulu.');
    header('Location: order_detail.php');
    exit;
}

$category = htmlspecialchars($currentOrder['category'] ?? 'Reguler', ENT_QUOTES, 'UTF-8');
$pickupDate = htmlspecialchars($currentOrder['pickup_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8');
$pickupTime = htmlspecialchars($currentOrder['pickup_time'] ?? '08:00-10:00', ENT_QUOTES, 'UTF-8');
$address = htmlspecialchars($currentOrder['address'] ?? 'Jl. Kebon Jeruk No. 12, West Jakarta, 11530', ENT_QUOTES, 'UTF-8');
$notes = htmlspecialchars($currentOrder['notes'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Order Details - Laundry Service</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2094f3",
                        "background-light": "#f5f7f8",
                        "background-dark": "#101a22",
                    },
                    fontFamily: {
                        "display": ["Inter"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100">
<div class="relative flex h-auto min-h-screen w-full max-w-md mx-auto flex-col bg-background-light dark:bg-background-dark overflow-x-hidden shadow-xl">
<!-- Header -->
<div class="flex items-center bg-background-light dark:bg-background-dark p-4 pb-2 justify-between sticky top-0 z-10 border-b border-slate-200 dark:border-slate-800">
<a href="order_detail.php" class="text-slate-900 dark:text-slate-100 flex size-12 shrink-0 items-center hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full p-2 transition-colors">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] flex-1 text-center pr-12">Pickup Schedule</h2>
<button id="themeToggle" class="text-slate-500 dark:text-slate-200 text-xs font-semibold px-2 py-1 rounded-full border border-slate-200 dark:border-slate-700">Mode</button>
</div>
<!-- Progress Indicator -->
<div class="px-4 pt-6 pb-2">
<div class="flex items-center justify-between mb-2">
<span class="text-xs font-semibold uppercase tracking-wider text-primary">Step 2 of 2</span>
<span class="text-xs font-medium text-slate-500">Scheduling</span>
</div>
<div class="h-1.5 w-full bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
<div class="h-full bg-primary w-full"></div>
</div>
</div>
<!-- Date Picker Section -->
<div class="px-4 py-4">
<h3 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] mb-4">Select Pickup Date</h3>
<div class="bg-white dark:bg-slate-900 rounded-xl p-4 shadow-sm border border-slate-100 dark:border-slate-800">
<div class="flex items-center p-1 justify-between mb-4">
<button class="text-slate-900 dark:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full p-2">
<span class="material-symbols-outlined">chevron_left</span>
</button>
<p class="text-slate-900 dark:text-slate-100 text-base font-bold leading-tight flex-1 text-center">October 2023</p>
<button class="text-slate-900 dark:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full p-2">
<span class="material-symbols-outlined">chevron_right</span>
</button>
</div>
<div class="grid grid-cols-7 gap-1">
<p class="text-slate-400 text-[11px] font-bold uppercase flex h-10 w-full items-center justify-center">S</p>
<p class="text-slate-400 text-[11px] font-bold uppercase flex h-10 w-full items-center justify-center">M</p>
<p class="text-slate-400 text-[11px] font-bold uppercase flex h-10 w-full items-center justify-center">T</p>
<p class="text-slate-400 text-[11px] font-bold uppercase flex h-10 w-full items-center justify-center">W</p>
<p class="text-slate-400 text-[11px] font-bold uppercase flex h-10 w-full items-center justify-center">T</p>
<p class="text-slate-400 text-[11px] font-bold uppercase flex h-10 w-full items-center justify-center">F</p>
<p class="text-slate-400 text-[11px] font-bold uppercase flex h-10 w-full items-center justify-center">S</p>
<button class="h-10 w-full text-slate-300 dark:text-slate-700 col-start-1 text-sm font-medium">27</button>
<button class="h-10 w-full text-slate-300 dark:text-slate-700 text-sm font-medium">28</button>
<button class="h-10 w-full text-slate-300 dark:text-slate-700 text-sm font-medium">29</button>
<button class="h-10 w-full text-slate-300 dark:text-slate-700 text-sm font-medium">30</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium">1</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium">2</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium">3</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium">4</button>
<button class="h-10 w-full text-white text-sm font-medium">
<div class="flex size-8 mx-auto items-center justify-center rounded-full bg-primary shadow-md shadow-primary/30">5</div>
</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full">6</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full">7</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full">8</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full">9</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full">10</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full">11</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full">12</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full">13</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full">14</button>
<button class="h-10 w-full text-slate-900 dark:text-slate-100 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full">15</button>
</div>
</div>
</div>
<!-- Time Slot Section -->
<div class="px-4 py-4">
<h3 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] mb-4">Select Time Slot</h3>
<div class="grid grid-cols-1 gap-3">
<div class="flex items-center justify-between p-4 rounded-xl border-2 border-primary bg-primary/5 dark:bg-primary/10 cursor-pointer">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-primary">schedule</span>
<p class="text-slate-900 dark:text-slate-100 text-sm font-semibold">08:00 AM - 10:00 AM</p>
</div>
<div class="size-5 rounded-full border-2 border-primary flex items-center justify-center">
<div class="size-2.5 bg-primary rounded-full"></div>
</div>
</div>
<div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 cursor-pointer hover:border-primary/50 transition-colors">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-slate-400">schedule</span>
<p class="text-slate-600 dark:text-slate-400 text-sm font-medium">10:00 AM - 12:00 PM</p>
</div>
<div class="size-5 rounded-full border border-slate-300 dark:border-slate-700"></div>
</div>
<div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 cursor-pointer hover:border-primary/50 transition-colors">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-slate-400">schedule</span>
<p class="text-slate-600 dark:text-slate-400 text-sm font-medium">12:00 PM - 02:00 PM</p>
</div>
<div class="size-5 rounded-full border border-slate-300 dark:border-slate-700"></div>
</div>
<div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 cursor-pointer hover:border-primary/50 transition-colors">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined text-slate-400">schedule</span>
<p class="text-slate-600 dark:text-slate-400 text-sm font-medium">02:00 PM - 04:00 PM</p>
</div>
<div class="size-5 rounded-full border border-slate-300 dark:border-slate-700"></div>
</div>
</div>
</div>
<!-- Special Instructions Section -->
<div class="px-4 py-4 mb-24">
<h3 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] mb-4">Special Instructions</h3>
<div class="bg-white dark:bg-slate-900 rounded-xl p-1 shadow-sm border border-slate-100 dark:border-slate-800">
<textarea class="w-full min-h-[120px] p-4 bg-transparent border-none focus:ring-0 text-slate-700 dark:text-slate-300 text-sm resize-none" placeholder="E.g. Ring the bell at the side gate, leave items in the blue bin, etc."></textarea>
</div>
<p class="mt-2 text-xs text-slate-500 flex items-center gap-1 px-1">
<span class="material-symbols-outlined text-[14px]">info</span>
                Optional: Let us know any specific handling requests.
            </p>
</div>
<!-- Footer Action -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto p-4 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md border-t border-slate-200 dark:border-slate-800">
<form method="post" action="" class="w-full">
<input type="hidden" name="action" value="finish_order" />
<button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
<span>Complete Order</span>
<span class="material-symbols-outlined">check_circle</span>
</button>
</form>
<div class="fixed bottom-16 left-0 right-0 max-w-md mx-auto bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 px-4 py-2">
    <div class="flex gap-2">
        <a class="flex-1 text-center text-slate-500 hover:text-primary" href="dashboard.php">Home</a>
        <a class="flex-1 text-center text-primary font-bold" href="neworder.php">New Order</a>
        <a class="flex-1 text-center text-slate-500 hover:text-primary" href="history.php">Orders</a>
        <a class="flex-1 text-center text-slate-500 hover:text-primary" href="profile.php">Profile</a>
    </div>
</div>
</div>
<?php echo global_route_script(); ?>
</body></html>

