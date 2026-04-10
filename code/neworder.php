<?php
require_once 'common.php';
$user = currentUser();
if (!$user || $user['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>New Order - Laundry</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {"primary": "#2094f3", "background-light": "#f5f7f8", "background-dark": "#101a22"},
            fontFamily: {"display": ["Inter"]},
            borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
        },
    },
}
</script>
<style>body {min-height: max(884px, 100dvh);}</style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100">
<div class="relative flex min-h-screen w-full max-w-md mx-auto flex-col bg-background-light dark:bg-background-dark overflow-x-hidden border-x border-slate-200 dark:border-slate-800">
<div class="flex items-center bg-white dark:bg-slate-900 p-4 pb-2 sticky top-0 z-10 border-b border-slate-200 dark:border-slate-800">
<span class="material-symbols-outlined text-slate-500 cursor-pointer p-2 hover:bg-slate-100 rounded-full dark:hover:bg-slate-800">arrow_back</span>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-tight flex-1 text-center px-4">New Order</h2>
<button id="themeToggle" class="text-slate-500 dark:text-slate-200 text-xs font-semibold px-2 py-1 rounded-full border border-slate-200 dark:border-slate-700">Mode</button>
</div>
<div class="flex flex-col gap-3 p-4">
<div class="flex gap-6 justify-between items-end">
<p class="text-slate-900 dark:text-slate-100 text-base font-semibold leading-normal">Service Selection</p>
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium leading-normal">Step 1 of 3</p>
</div>
<div class="rounded-full bg-slate-200 dark:bg-slate-700 h-2 overflow-hidden">
<div class="h-full rounded-full bg-primary" style="width: 33.33%;"></div>
</div>
</div>
<div class="px-4 pb-3 pt-5">
<h2 class="text-slate-900 dark:text-slate-100 text-[22px] font-bold leading-tight tracking-tight">Select Category</h2>
<p class="text-slate-500 dark:text-slate-400 text-sm mt-1">What kind of items do you need cleaned today?</p>
</div>
<div class="flex flex-col gap-2 p-4">
<a href="order_detail.php?category=Regular" class="flex items-center gap-4 bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 text-left transition-all hover:bg-slate-50 dark:hover:bg-slate-800 hover:shadow-md">
<div class="flex items-center justify-center rounded-lg bg-primary/10 text-primary shrink-0 size-12">
<span class="material-symbols-outlined">local_laundry_service</span>
</div>
<div class="flex flex-col justify-center flex-1">
<p class="text-slate-900 dark:text-slate-100 text-base font-bold leading-tight">Regular Laundry</p>
<p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-normal">Clothes, t-shirts, linens</p>
</div>
<span class="material-symbols-outlined text-slate-400 ml-auto">chevron_right</span>
</a>
<a href="order_detail.php?category=Formal" class="flex items-center gap-4 bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 text-left transition-all hover:bg-slate-50 dark:hover:bg-slate-800 hover:shadow-md">
<div class="flex items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 shrink-0 size-12">
<span class="material-symbols-outlined">checkroom</span>
</div>
<div class="flex flex-col justify-center flex-1">
<p class="text-slate-900 dark:text-slate-100 text-base font-bold leading-tight">Formal Wear</p>
<p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-normal">Suits, dresses, delicate</p>
</div>
<span class="material-symbols-outlined text-slate-400 ml-auto">chevron_right</span>
</a>
<a href="order_detail.php?category=Bags" class="flex items-center gap-4 bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 text-left transition-all hover:bg-slate-50 dark:hover:bg-slate-800 hover:shadow-md">
<div class="flex items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 shrink-0 size-12">
<span class="material-symbols-outlined">bag</span>
</div>
<div class="flex flex-col justify-center flex-1">
<p class="text-slate-900 dark:text-slate-100 text-base font-bold leading-tight">Bags & Accessories</p>
<p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-normal">Backpacks, handbags</p>
</div>
<span class="material-symbols-outlined text-slate-400 ml-auto">chevron_right</span>
</a>
<a href="order_detail.php?category=Special" class="flex items-center gap-4 bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 text-left transition-all hover:bg-slate-50 dark:hover:bg-slate-800 hover:shadow-md">
<div class="flex items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 shrink-0 size-12">
<span class="material-symbols-outlined">bedroom_parent</span>
</div>
<div class="flex flex-col justify-center flex-1">
<p class="text-slate-900 dark:text-slate-100 text-base font-bold leading-tight">Special Items</p>
<p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-normal">Bedding, curtains, shoes</p>
</div>
<span class="material-symbols-outlined text-slate-400 ml-auto">chevron_right</span>
</a>
</div>
<div class="mt-auto flex gap-2 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 pb-6 pt-3 sticky bottom-0">
<a class="flex flex-1 flex-col items-center justify-center gap-1 text-slate-400 dark:text-slate-500 hover:text-primary" href="dashboard.php">
<span class="material-symbols-outlined text-lg">home</span>
<p class="text-xs font-medium">Home</p>
</a>
<a class="flex flex-1 flex-col items-center justify-center gap-1 text-primary font-bold" href="neworder.php">
<span class="material-symbols-outlined text-lg">add</span>
<p class="text-xs font-medium">New Order</p>
</a>
<a class="flex flex-1 flex-col items-center justify-center gap-1 text-slate-400 dark:text-slate-500 hover:text-primary" href="history.php">
<span class="material-symbols-outlined text-lg">history</span>
<p class="text-xs font-medium">Orders</p>
</a>
<a class="flex flex-1 flex-col items-center justify-center gap-1 text-slate-400 dark:text-slate-500 hover:text-primary" href="profile.php">
<span class="material-symbols-outlined text-lg">person</span>
<p class="text-xs font-medium">Profile</p>
</a>
</div>
<?= global_route_script() ?>
</body>
</html>

