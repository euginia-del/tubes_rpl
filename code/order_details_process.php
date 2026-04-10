<?php
require_once 'common.php';
$user = currentUser();
if (!$user) {
    header('Location: login.php');
    exit;
}
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
<title>Work Order Details</title>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
<div class="max-w-md mx-auto bg-white dark:bg-slate-900 min-h-screen shadow-xl flex flex-col">
<!-- TopAppBar Component -->
<div class="flex items-center p-4 border-b border-slate-200 dark:border-slate-800 sticky top-0 bg-white dark:bg-slate-900 z-10">
<button class="text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 p-2 rounded-full transition-colors">
<span class="material-symbols-outlined">arrow_back</span>
</button>
<h2 class="text-lg font-bold leading-tight tracking-tight flex-1 ml-2 text-slate-900 dark:text-slate-100">Work Order Details</h2>
<div class="flex items-center gap-2">
<button class="text-slate-600 dark:text-slate-400 p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors">
<span class="material-symbols-outlined">more_vert</span>
</button>
</div>
</div>
<!-- Scrollable Content Area -->
<div class="flex-1 overflow-y-auto pb-24">
<!-- Customer Header -->
<div class="p-6 bg-primary/5 dark:bg-primary/10">
<div class="flex items-center gap-5">
<div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full h-20 w-20 border-4 border-white dark:border-slate-800 shadow-sm" data-alt="Professional portrait of a male customer for profile display" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuARWcX_CLehuP2WNKy9b5mmsLxE0K2mV1PSU_QfCcpg-27mAiH6d1fgqIRhGML8O1Zal6CRBgxg5294wqrf33uRlTms2OmVfjAT085JBQp_0xoik0PcGiAue5NRTBhvrpJ8wGxHLMyBNpnfr0Ja7VUe-CZo5_bodfIJ2YUYzy7fE0KfX7f1FGHASV2XEgog4VhafQ1y1klJ5cRDhwymAajguCI8ffxBGfkY6Ehq0b5Hax-cfFO7AaCo8wwNyeBRJ_oHe0O-Nc8_ffs");'></div>
<div class="flex flex-col">
<p class="text-slate-900 dark:text-slate-100 text-xl font-bold leading-tight">Robert Henderson</p>
<p class="text-primary text-sm font-semibold mt-1">Order #WO-88219</p>
<div class="flex items-center gap-1 mt-2 text-slate-500 dark:text-slate-400 text-xs">
<span class="material-symbols-outlined text-sm">calendar_today</span>
<span>Created Oct 24, 2023</span>
</div>
</div>
</div>
</div>
<!-- Status Badge Area -->
<div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
<div class="flex flex-col">
<span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-slate-500">Current Status</span>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 mt-1">
                        Pending
                    </span>
</div>
<div class="text-right">
<span class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-slate-500">Service Category</span>
<div class="flex items-center gap-2 mt-1">
<span class="material-symbols-outlined text-primary">apparel</span>
<span class="text-sm font-semibold">Formal Wear</span>
</div>
</div>
</div>
<!-- Schedule Information -->
<div class="p-6">
<h3 class="text-slate-900 dark:text-slate-100 text-base font-bold mb-4 flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-xl">schedule</span>
                    Schedule Info
                </h3>
<div class="grid grid-cols-2 gap-4">
<div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
<p class="text-[10px] uppercase font-bold text-slate-400 mb-1">Pickup</p>
<p class="text-sm font-semibold">Oct 25, 2023</p>
<p class="text-xs text-slate-500">09:00 AM - 11:00 AM</p>
</div>
<div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
<p class="text-[10px] uppercase font-bold text-slate-400 mb-1">Delivery</p>
<p class="text-sm font-semibold">Oct 28, 2023</p>
<p class="text-xs text-slate-500">02:00 PM - 04:00 PM</p>
</div>
</div>
</div>
<!-- Order Items -->
<div class="p-6 border-t border-slate-100 dark:border-slate-800">
<h3 class="text-slate-900 dark:text-slate-100 text-base font-bold mb-4 flex items-center justify-between">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-xl">inventory_2</span>
                        Items (3)
                    </div>
<span class="text-xs font-normal text-primary underline">Edit Items</span>
</h3>
<div class="space-y-4">
<!-- Item 1 -->
<div class="flex items-center gap-4 bg-white dark:bg-slate-900 p-1">
<div class="size-14 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
<span class="material-symbols-outlined">checkroom</span>
</div>
<div class="flex-1">
<p class="text-sm font-bold text-slate-900 dark:text-slate-100">Navy Blue Blazer</p>
<p class="text-xs text-slate-500">Dry Clean • Premium Finish</p>
</div>
<div class="text-right">
<p class="text-sm font-bold">x1</p>
<p class="text-xs text-primary">$12.50</p>
</div>
</div>
<!-- Item 2 -->
<div class="flex items-center gap-4 bg-white dark:bg-slate-900 p-1">
<div class="size-14 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
<span class="material-symbols-outlined">checkroom</span>
</div>
<div class="flex-1">
<p class="text-sm font-bold text-slate-900 dark:text-slate-100">White Cotton Shirt</p>
<p class="text-xs text-slate-500">Launder • Heavy Starch</p>
</div>
<div class="text-right">
<p class="text-sm font-bold">x2</p>
<p class="text-xs text-primary">$8.00</p>
</div>
</div>
<!-- Item 3 -->
<div class="flex items-center gap-4 bg-white dark:bg-slate-900 p-1">
<div class="size-14 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
<span class="material-symbols-outlined">checkroom</span>
</div>
<div class="flex-1">
<p class="text-sm font-bold text-slate-900 dark:text-slate-100">Silk Tie</p>
<p class="text-xs text-slate-500">Gentle Handwash</p>
</div>
<div class="text-right">
<p class="text-sm font-bold">x1</p>
<p class="text-xs text-primary">$4.50</p>
</div>
</div>
</div>
</div>
<!-- Notes Section -->
<div class="p-6 border-t border-slate-100 dark:border-slate-800">
<div class="bg-primary/5 dark:bg-primary/10 rounded-xl p-4">
<p class="text-xs font-bold text-primary mb-1 uppercase">Special Instructions</p>
<p class="text-sm text-slate-600 dark:text-slate-300 italic">"Please ensure the blazer buttons are covered during cleaning. Customer requested eco-friendly detergent."</p>
</div>
</div>
</div>
<!-- Sticky Footer Action -->
<form method="post" action="">
<div class="p-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 fixed bottom-0 w-full max-w-md">
<input type="hidden" name="process_order" value="1" />
<button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-4 rounded-xl font-bold text-lg flex items-center justify-center gap-2 shadow-lg shadow-primary/25 transition-all active:scale-[0.98]">
<span class="material-symbols-outlined">sync</span>
                Process Order
            </button>
<p class="text-center text-[10px] text-slate-400 dark:text-slate-500 mt-2">Clicking will update status to <span class="font-bold">Diproses</span></p>
</div>
</form>
</div>
<?php echo global_route_script(); ?>
</body></html>

