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
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Login - LaundryApp</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="style.css">
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: { "primary": "#6366f1", "secondary": "#8b5cf6" },
            fontFamily: { "display": ["Inter", "sans-serif"] }
        }
    }
}
</script>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 min-h-screen flex items-center justify-center p-4">
<div class="container-responsive max-w-6xl mx-auto">
    <div class="grid md:grid-cols-2 gap-8 items-center">
        <!-- Left Side - Branding -->
        <div class="hidden md:block text-center md:text-left fade-in-left">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-r from-primary to-secondary shadow-xl float-animation mb-6">
                <span class="material-symbols-outlined text-white text-5xl">local_laundry_service</span>
            </div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">LaundryFresh</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-4 text-lg">Smart Laundry Service</p>
            <p class="text-gray-400 dark:text-gray-500 mt-6">Fast • Clean • Reliable</p>
        </div>

        <!-- Right Side - Login Form -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl p-6 md:p-8 fade-in-right">
            <div class="text-center mb-6 md:hidden">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-r from-primary to-secondary shadow-lg mb-3">
                    <span class="material-symbols-outlined text-white text-3xl">local_laundry_service</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Welcome Back!</h2>
            </div>
            
            <h2 class="hidden md:block text-2xl font-bold text-gray-800 dark:text-white mb-6">Login to Your Account</h2>

            <?php if ($error = get_flash('error')): ?>
            <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-3">
                <p class="text-red-700 dark:text-red-300 text-sm"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($success = get_flash('success')): ?>
            <div class="mb-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl p-3">
                <p class="text-emerald-700 dark:text-emerald-300 text-sm"><?= htmlspecialchars($success) ?></p>
            </div>
            <?php endif; ?>

            <form method="post" class="space-y-5">
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Email Address</label>
                    <div class="relative mt-1">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">mail</span>
                        <input name="email" type="email" class="w-full border border-gray-200 dark:border-slate-600 rounded-xl pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-700 focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="your@email.com" required/>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Password</label>
                    <div class="relative mt-1">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">lock</span>
                        <input name="password" type="password" class="w-full border border-gray-200 dark:border-slate-600 rounded-xl pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-700 focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="••••••••" required/>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="rounded text-primary focus:ring-primary"/>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                    </label>
                    <a href="#" onclick="showForgotModal(); return false;" class="text-sm text-primary hover:underline">Forgot Password?</a>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary hover:from-primary-dark hover:to-secondary-dark text-white font-bold py-3 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                    <span>Sign In</span>
                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </button>
            </form>

            <p class="text-center text-gray-600 dark:text-gray-400 text-sm mt-6">
                Don't have an account? 
                <a href="register.php" class="text-primary font-bold hover:underline">Create Account</a>
            </p>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div id="forgotModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 md:p-8 w-full max-w-md animate-scale-in">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Forgot Password?</h2>
            <button onclick="hideForgotModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="forgot_email" value="1">
            <label class="block mb-6">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email Address</p>
                <input name="forgot_email" type="email" class="w-full border border-gray-200 dark:border-slate-600 rounded-xl p-3 bg-gray-50 dark:bg-slate-700" placeholder="your@email.com" required>
            </label>
            <div class="flex gap-3">
                <button type="button" onclick="hideForgotModal()" class="flex-1 bg-gray-100 dark:bg-slate-700 py-3 rounded-xl font-semibold text-gray-700 dark:text-gray-300">Cancel</button>
                <button type="submit" class="flex-1 bg-primary text-white py-3 rounded-xl font-bold">Send Link</button>
            </div>
        </form>
    </div>
</div>

<script>
function showForgotModal() { 
    document.getElementById('forgotModal').style.display = 'flex'; 
    document.getElementById('forgotModal').style.opacity = '1';
}
function hideForgotModal() { 
    document.getElementById('forgotModal').style.display = 'none'; 
}
</script>
<?= global_route_script() ?>
</body>
</html>