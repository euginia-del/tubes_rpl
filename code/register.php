<?php
require_once __DIR__ . '/common.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'], $_POST['email'], $_POST['password'], $_POST['no_hp'], $_POST['alamat'])) {
    $db = get_db();
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    
    $stmt = $db->prepare('SELECT COUNT(*) FROM User WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        set_flash('error', 'Email sudah terdaftar.');
    } else {
        $stmt = $db->prepare('INSERT INTO User (nama, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, "customer")');
        if ($stmt->execute([$nama, $email, $password, $no_hp, $alamat])) {
            set_flash('success', 'Akun berhasil dibuat. Silakan login.');
            header('Location: login.php');
            exit;
        } else {
            set_flash('error', 'Gagal membuat akun.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: { "primary": "#3398db", "background-light": "#f6f7f8", "background-dark": "#121a20" },
            fontFamily: { "display": ["Inter"] }
        }
    }
}
</script>
<title>Register - Laundry</title>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="max-w-md mx-auto min-h-screen bg-white dark:bg-slate-900 shadow-xl">
<div class="flex items-center p-4 border-b">
<a href="login.php" class="text-primary p-2 rounded-full hover:bg-slate-100">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<h2 class="text-lg font-bold flex-1 text-center">Register</h2>
<button id="themeToggle" class="text-xs px-2 py-1 rounded-full border">Mode</button>
</div>

<div class="p-4">
<h1 class="text-2xl font-bold text-center">Join LaundryFresh</h1>
<p class="text-slate-500 text-center mt-2">Sign up to manage your laundry</p>

<?php if ($error = get_flash('error')): ?>
<div class="mt-4 bg-red-100 text-red-700 p-3 rounded-lg"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" class="mt-6 space-y-4">
<div>
<label class="text-sm font-semibold">Full Name</label>
<div class="relative mt-1">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">person</span>
<input name="nama" class="w-full border rounded-lg pl-10 pr-3 py-3" placeholder="Your Name" required/>
</div>
</div>

<div>
<label class="text-sm font-semibold">Email</label>
<div class="relative mt-1">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">mail</span>
<input name="email" type="email" class="w-full border rounded-lg pl-10 pr-3 py-3" placeholder="your@email.com" required/>
</div>
</div>

<div>
<label class="text-sm font-semibold">Phone Number</label>
<div class="relative mt-1">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">call</span>
<input name="no_hp" class="w-full border rounded-lg pl-10 pr-3 py-3" placeholder="08123456789" required/>
</div>
</div>

<div>
<label class="text-sm font-semibold">Address</label>
<div class="relative mt-1">
<span class="material-symbols-outlined absolute left-3 top-3 text-slate-400 text-lg">home</span>
<textarea name="alamat" class="w-full border rounded-lg pl-10 pr-3 py-3" rows="2" placeholder="Your address" required></textarea>
</div>
</div>

<div>
<label class="text-sm font-semibold">Password</label>
<div class="relative mt-1">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock</span>
<input name="password" type="password" class="w-full border rounded-lg pl-10 pr-3 py-3" placeholder="••••••••" required/>
</div>
</div>

<button type="submit" class="w-full bg-primary text-white font-bold py-3 rounded-xl mt-4">Create Account</button>

<p class="text-center text-sm mt-4">
Already have an account? <a href="login.php" class="text-primary font-bold">Log In</a>
</p>
</form>
</div>
</div>
<?php echo global_route_script(); ?>
</body>
</html>