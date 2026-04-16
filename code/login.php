<?php
require_once 'common.php';

if (isset($_GET['logout'])) { 
    logout_user(); 
    header('Location: login.php'); 
    exit; 
}

if (is_logged_in()) { 
    $user = currentUser();
    $role = $user['role'] ?? 'customer';
    $redirect = match($role) {
        'customer' => 'dashboard.php',
        'worker' => 'worker.php',
        'supervisor' => 'supervisor.php',
        'admin' => 'admin.php',
        default => 'dashboard.php'
    };
    header('Location: ' . $redirect);
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_email'])) {
    $email = trim($_POST['forgot_email']);
    set_flash('success', 'Reset link telah dikirim ke ' . htmlspecialchars($email));
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $loginResult = loginUser(trim($_POST['email']), trim($_POST['password']));
    if ($loginResult) {
        $_SESSION['user_id'] = $loginResult['id_user'];
        set_flash('success', 'Login berhasil. Selamat datang ' . htmlspecialchars($loginResult['nama']) . '!');
        $role = $loginResult['role'] ?? 'customer';
        if ($role === 'customer') {
            header('Location: dashboard.php');
        } elseif ($role === 'worker') {
            header('Location: worker.php');
        } elseif ($role === 'admin') {
            header('Location: admin.php');
        } elseif ($role === 'supervisor') {
            header('Location: supervisor.php');
        } else {
            header('Location: dashboard.php');
        }
        exit;
    }
    set_flash('error', 'Email atau password salah.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "primary": "#3398db",
                "background-light": "#f6f7f8",
                "background-dark": "#121a20",
            },
            fontFamily: {
                "display": ["Inter"]
            }
        }
    }
}
</script>
<title>Laundry Service Login</title>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100">
<div class="relative flex h-auto min-h-screen w-full flex-col bg-background-light dark:bg-background-dark overflow-x-hidden">
<div class="flex items-center bg-background-light dark:bg-background-dark p-4 pb-2 justify-between">
<div class="text-primary flex size-12 shrink-0 items-center cursor-pointer">
<span class="material-symbols-outlined text-3xl">local_laundry_service</span>
</div>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight flex-1 text-center">Laundry Portal</h2>
<button id="themeToggle" class="text-slate-500 dark:text-slate-200 text-xs font-semibold px-2 py-1 rounded-full border border-slate-200 dark:border-slate-700">Mode</button>
</div>

<div class="@container">
<div class="@[480px]:px-4 @[480px]:py-3 flex justify-center">
<div class="w-full max-w-[480px] bg-center bg-no-repeat bg-cover flex flex-col items-center justify-center overflow-hidden bg-primary/10 dark:bg-primary/20 @[480px]:rounded-xl min-h-64 relative">
<div class="bg-white/90 dark:bg-slate-800/90 p-6 rounded-full shadow-lg border-4 border-primary/20">
<span class="material-symbols-outlined text-primary !text-6xl">local_laundry_service</span>
</div>
</div>
</div>
</div>

<div class="max-w-[480px] mx-auto w-full">
<h1 class="text-slate-900 dark:text-slate-100 text-[32px] font-bold leading-tight px-4 text-center pb-2 pt-8">Welcome Back</h1>
<p class="text-slate-600 dark:text-slate-400 text-base px-4 text-center pb-6">Manage your laundry orders and schedule fresh pickups.</p>

<?php if ($error = get_flash('error')): ?>
<div class="mx-4 mb-4 rounded-lg bg-red-100 p-3 text-sm font-medium text-red-700 border border-red-200">
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if ($success = get_flash('success')): ?>
<div class="mx-4 mb-4 rounded-lg bg-emerald-100 p-3 text-sm font-medium text-emerald-700 border border-emerald-200">
<?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<div class="flex flex-col gap-4 px-4 py-3">
<form method="post" action="">
<label class="flex flex-col w-full">
<p class="text-slate-900 dark:text-slate-100 text-sm font-semibold pb-2">Email Address</p>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-primary/60">mail</span>
<input name="email" class="w-full rounded-lg border border-primary/20 bg-white dark:bg-slate-800 h-14 pl-12 pr-4 focus:ring-2 focus:ring-primary" placeholder="your@email.com" type="email" required />
</div>
</label>

<label class="flex flex-col w-full">
<p class="text-slate-900 dark:text-slate-100 text-sm font-semibold pb-2">Password</p>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-primary/60">lock</span>
<input name="password" class="w-full rounded-lg border border-primary/20 bg-white dark:bg-slate-800 h-14 pl-12 pr-12 focus:ring-2 focus:ring-primary" placeholder="••••••••" type="password" required />
</div>
</label>

<div class="flex items-center justify-between py-2">
<label class="flex items-center gap-2">
<input class="rounded text-primary" type="checkbox" />
<span class="text-sm text-slate-600 dark:text-slate-400">Remember me</span>
</label>
<a class="text-sm font-semibold text-primary hover:underline" href="#" onclick="showForgotModal(); return false;">Forgot Password?</a>
</div>

<button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-xl shadow-md transition-all mt-4 flex items-center justify-center gap-2">
<span>Log In</span>
<span class="material-symbols-outlined">login</span>
</button>
</form>

<p class="text-center text-slate-600 dark:text-slate-400 text-sm mt-6">
Don't have an account? <a class="text-primary font-bold hover:underline" href="register.php">Create Account</a>
</p>
</div>
</div>

<div id="forgotModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
<div class="bg-white dark:bg-slate-900 rounded-2xl p-8 w-full max-w-md">
<div class="flex justify-between items-center mb-6">
<h2 class="text-2xl font-bold">Forgot Password?</h2>
<button onclick="hideForgotModal()" class="text-2xl">&times;</button>
</div>
<form method="POST">
<input type="hidden" name="forgot_email" value="1">
<label class="flex flex-col w-full mb-6">
<p class="text-sm font-semibold mb-2">Email</p>
<input name="forgot_email" class="w-full rounded-xl border bg-white dark:bg-slate-800 p-3" placeholder="your@email.com" type="email" required>
</label>
<div class="flex gap-3">
<button type="button" onclick="hideForgotModal()" class="flex-1 bg-slate-100 py-3 rounded-xl font-semibold">Cancel</button>
<button type="submit" class="flex-1 bg-primary text-white py-3 rounded-xl font-bold">Send Link</button>
</div>
</form>
</div>
</div>
</div>

<script>
function showForgotModal() { document.getElementById('forgotModal').style.display = 'flex'; }
function hideForgotModal() { document.getElementById('forgotModal').style.display = 'none'; }
</script>
<?php echo global_route_script(); ?>
</body>
</html>