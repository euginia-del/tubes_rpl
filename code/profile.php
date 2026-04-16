<?php
require_once 'common.php';

$user = currentUser();
if (!$user) { 
    header('Location: login.php'); 
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'logout') {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

$db = get_db();
$stmt = $db->prepare('SELECT COUNT(*) FROM Orders WHERE id_user = ?');
$stmt->execute([$user['id_user']]);
$totalOrders = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Profile</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {darkMode: "class", theme: {extend: {colors: {"primary": "#2094f3","background-light": "#f5f7f8","background-dark": "#101a22"}, fontFamily: {"display": ["Inter"]}}}};
</script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl">
<div class="px-4 py-4 flex items-center justify-between border-b">
<h2 class="text-lg font-bold">Profile</h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border">Mode</button>
</div>

<div class="p-4 space-y-4">
<div class="flex justify-center mb-4">
<div class="bg-primary/10 rounded-full p-6">
<span class="material-symbols-outlined text-primary text-5xl">account_circle</span>
</div>
</div>

<div class="border rounded-xl p-4">
<p class="text-sm text-slate-500">Nama</p>
<p class="font-bold text-lg"><?= htmlspecialchars($user['nama'] ?? 'N/A') ?></p>
</div>

<div class="border rounded-xl p-4">
<p class="text-sm text-slate-500">Email</p>
<p class="font-semibold"><?= htmlspecialchars($user['email']) ?></p>
</div>

<div class="border rounded-xl p-4">
<p class="text-sm text-slate-500">No. HP</p>
<p class="font-semibold"><?= htmlspecialchars($user['no_hp'] ?? 'N/A') ?></p>
</div>

<div class="border rounded-xl p-4">
<p class="text-sm text-slate-500">Alamat</p>
<p class="font-semibold"><?= htmlspecialchars($user['alamat'] ?? 'N/A') ?></p>
</div>

<div class="border rounded-xl p-4">
<p class="text-sm text-slate-500">Role</p>
<span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold"><?= htmlspecialchars($user['role']) ?></span>
</div>

<div class="border rounded-xl p-4">
<p class="text-sm text-slate-500">Total Orders</p>
<p class="font-bold text-2xl"><?= $totalOrders ?></p>
</div>

<form method="post">
<input type="hidden" name="action" value="logout" />
<button class="w-full bg-red-500 hover:bg-red-600 text-white py-4 rounded-xl font-bold mt-4">Logout</button>
</form>
</div>

<!-- Bottom Nav -->
<div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto border-t bg-white dark:bg-slate-900 px-4 pb-6 pt-2">
<div class="flex gap-2">
<a href="dashboard.php" class="flex-1 text-center text-slate-500 py-2">Home</a>
<a href="neworder.php" class="flex-1 text-center text-slate-500 py-2">New Order</a>
<a href="history.php" class="flex-1 text-center text-slate-500 py-2">History</a>
<a href="profile.php" class="flex-1 text-center text-primary font-bold py-2">Profile</a>
</div>
</div>

<?= global_route_script() ?>
</body>
</html>