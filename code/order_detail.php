<?php
require_once __DIR__ . '/common.php';
require_customer();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_order') {
    set_current_order([
        'category' => $_POST['category'] ?? 'Reguler',
        'pickup_date' => $_POST['pickup_date'] ?? date('Y-m-d'),
        'pickup_time' => $_POST['pickup_time'] ?? '08:00-10:00',
        'address' => $_POST['address'] ?? 'Jl. Kebon Jeruk No. 12, West Jakarta, 11530',
        'notes' => $_POST['notes'] ?? '',
    ]);
    header('Location: schedule.php');
    exit;
}

$selectedCategory = $_GET['category'] ?? (get_current_order()['category'] ?? 'Reguler');
set_current_order(['category' => $selectedCategory]);
?>
<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
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
            font-family: 'Inter', sans-serif;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100">
<div class="relative flex min-h-screen w-full flex-col max-w-md mx-auto bg-background-light dark:bg-background-dark shadow-xl">
<!-- Header -->
<div class="flex items-center bg-background-light dark:bg-background-dark p-4 sticky top-0 z-10 border-b border-primary/10">
<a href="neworder.php" class="text-primary flex size-10 shrink-0 items-center justify-center rounded-full hover:bg-primary/10 transition-colors">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight flex-1 text-center pr-10">New Laundry Order - <?= htmlspecialchars($selectedCategory) ?></h2>
<button id="themeToggle" class="text-slate-500 dark:text-slate-200 text-xs font-semibold px-2 py-1 rounded-full border border-slate-200 dark:border-slate-700">Mode</button>
</div>
<!-- Content -->
<div class="flex-1 overflow-y-auto pb-24">
<form method="post" action="" id="orderFlowForm">
<input type="hidden" name="action" value="save_order" />
<!-- Service Category Selection -->
<div class="px-4 pt-6">
<h3 class="text-slate-900 dark:text-slate-100 text-base font-bold mb-3">Select Service Category</h3>
<div class="flex bg-primary/5 dark:bg-primary/10 p-1.5 rounded-xl border border-primary/10">
<label class="flex cursor-pointer h-11 grow items-center justify-center overflow-hidden rounded-lg px-2 has-[:checked]:bg-primary has-[:checked]:text-white text-slate-600 dark:text-slate-400 text-sm font-semibold transition-all">
<span class="truncate">Reguler</span>
<input class="hidden" name="category" type="radio" value="Reguler" <?= $selectedCategory === 'Reguler' ? 'checked' : '' ?> />
</label>
<label class="flex cursor-pointer h-11 grow items-center justify-center overflow-hidden rounded-lg px-2 has-[:checked]:bg-primary has-[:checked]:text-white text-slate-600 dark:text-slate-400 text-sm font-semibold transition-all">
<span class="truncate">Kilat</span>
<input class="hidden" name="category" type="radio" value="Kilat" <?= $selectedCategory === 'Kilat' ? 'checked' : '' ?> />
</label>
<label class="flex cursor-pointer h-11 grow items-center justify-center overflow-hidden rounded-lg px-2 has-[:checked]:bg-primary has-[:checked]:text-white text-slate-600 dark:text-slate-400 text-sm font-semibold transition-all">
<span class="truncate">Ekspres</span>
<input class="hidden" name="category" type="radio" value="Ekspres" <?= $selectedCategory === 'Ekspres' ? 'checked' : '' ?> />
</label>
</div>
<p class="text-xs text-slate-500 mt-2 px-1">Reguler: 2-3 days, Kilat: 24h, Ekspres: 6-8h</p>
</div>
<!-- Pickup Details -->
<div class="px-4 pt-8">
<div class="flex items-center gap-2 mb-4">
<span class="material-symbols-outlined text-primary">local_shipping</span>
<h3 class="text-slate-900 dark:text-slate-100 text-base font-bold">Pickup Details</h3>
</div>
<div class="space-y-4">
<!-- Date Selector -->
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Pickup Date</label>
<div class="relative">
<input class="w-full bg-white dark:bg-slate-800 border border-primary/20 dark:border-primary/30 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none appearance-none" type="date" name="pickup_date" value="<?= date('Y-m-d') ?>" required />
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">calendar_today</span>
</div>
</div>
<!-- Time Slot Selector -->
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Pickup Time Slot</label>
<div class="grid grid-cols-2 gap-3">
<label class="cursor-pointer">
<input checked="" class="hidden peer" name="pickup_time" type="radio" value="08:00-10:00"/>
<div class="border border-primary/20 dark:border-primary/30 rounded-lg p-3 text-center peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary transition-all">
<span class="text-sm font-medium">08:00 - 10:00</span>
</div>
</label>
<label class="cursor-pointer">
<input class="hidden peer" name="pickup_time" type="radio" value="10:00-12:00"/>
<div class="border border-primary/20 dark:border-primary/30 rounded-lg p-3 text-center peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary transition-all">
<span class="text-sm font-medium">10:00 - 12:00</span>
</div>
</label>
<label class="cursor-pointer">
<input class="hidden peer" name="pickup_time" type="radio" value="13:00-15:00"/>
<div class="border border-primary/20 dark:border-primary/30 rounded-lg p-3 text-center peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary transition-all">
<span class="text-sm font-medium">13:00 - 15:00</span>
</div>
</label>
<label class="cursor-pointer">
<input class="hidden peer" name="pickup_time" type="radio" value="15:00-17:00"/>
<div class="border border-primary/20 dark:border-primary/30 rounded-lg p-3 text-center peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary transition-all">
<span class="text-sm font-medium">15:00 - 17:00</span>
</div>
</label>
</div>
</div>
<!-- Address Detail -->
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Pickup Address</label>
<div class="flex items-start gap-3 bg-white dark:bg-slate-800 p-4 rounded-xl border border-primary/20 dark:border-primary/30 shadow-sm">
<span class="material-symbols-outlined text-primary mt-1">location_on</span>
<div class="flex-1">
<p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Home</p>
<p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Jl. Kebon Jeruk No. 12, West Jakarta, 11530</p>
<input type="hidden" name="address" value="Jl. Kebon Jeruk No. 12, West Jakarta, 11530" />
</div>
<button class="text-primary text-xs font-bold uppercase tracking-wider">Change</button>
</div>
</div>
<!-- Notes -->
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Special Instructions (Optional)</label>
<textarea name="notes" class="w-full bg-white dark:bg-slate-800 border border-primary/20 dark:border-primary/30 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none min-h-[100px]" placeholder="e.g. Leave at the front gate, use mild detergent..."></textarea>
</div>
</div>
</div>
</form>
</div>
<!-- Bottom Action Area -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-t border-primary/10 p-4 flex flex-col gap-3">
<button type="submit" class="w-full text-center bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/20 transition-all active:scale-[0.98]">
                Complete Order
            </button>
<!-- Bottom Navigation Bar Placeholder -->
<div class="flex gap-2 pt-2">
<a class="flex flex-1 flex-col items-center justify-end gap-1 text-slate-400 dark:text-slate-500" href="dashboard.php">
<div class="flex h-8 items-center justify-center">
<span class="material-symbols-outlined">home</span>
</div>
<p class="text-[10px] font-medium leading-normal">Home</p>
</a>
<a class="flex flex-1 flex-col items-center justify-end gap-1 text-primary" href="neworder.php">
<div class="flex h-8 items-center justify-center">
<span class="material-symbols-outlined">receipt_long</span>
</div>
<p class="text-[10px] font-medium leading-normal">New Order</p>
</a>
<a class="flex flex-1 flex-col items-center justify-end gap-1 text-slate-400 dark:text-slate-500" href="history.php">
<div class="flex h-8 items-center justify-center">
<span class="material-symbols-outlined">history</span>
</div>
<p class="text-[10px] font-medium leading-normal">History</p>
</a>
<a class="flex flex-1 flex-col items-center justify-end gap-1 text-slate-400 dark:text-slate-500" href="profile.php">
<div class="flex h-8 items-center justify-center">
<span class="material-symbols-outlined">person</span>
</div>
<p class="text-[10px] font-medium leading-normal">Profile</p>
</a>
</div>
</div>
</div>
<?php echo global_route_script(); ?>
</body></html>

