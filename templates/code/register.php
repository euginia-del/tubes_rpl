<?php
require_once __DIR__ . '/common.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'], $_POST['email'], $_POST['password'], $_POST['no_hp'], $_POST['alamat'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    
    if (registerUser($nama, $email, $password, $no_hp, $alamat)) {
        set_flash('success', 'Akun berhasil dibuat. Silakan login.');
        header('Location: login.php');
        exit;
    } else {
        set_flash('error', 'Email sudah terdaftar atau gagal membuat akun.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Register - LaundryApp</title>
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
            <h1 class="text-4xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Join LaundryFresh</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-4 text-lg">Create your account</p>
            <p class="text-gray-400 dark:text-gray-500 mt-6">Fast • Clean • Reliable Laundry Service</p>
        </div>

        <!-- Right Side - Register Form -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl p-6 md:p-8 fade-in-right">
            <div class="text-center mb-6 md:hidden">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-r from-primary to-secondary shadow-lg mb-3">
                    <span class="material-symbols-outlined text-white text-3xl">local_laundry_service</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Create Account</h2>
            </div>
            
            <h2 class="hidden md:block text-2xl font-bold text-gray-800 dark:text-white mb-6">Create New Account</h2>

            <?php if ($error = get_flash('error')): ?>
            <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-3">
                <p class="text-red-700 dark:text-red-300 text-sm"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Full Name</label>
                    <div class="relative mt-1">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">person</span>
                        <input name="nama" type="text" class="w-full border border-gray-200 dark:border-slate-600 rounded-xl pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-700 focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Your full name" required/>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Email Address</label>
                    <div class="relative mt-1">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">mail</span>
                        <input name="email" type="email" class="w-full border border-gray-200 dark:border-slate-600 rounded-xl pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-700 focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="your@email.com" required/>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Phone Number</label>
                    <div class="relative mt-1">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">call</span>
                        <input name="no_hp" type="tel" class="w-full border border-gray-200 dark:border-slate-600 rounded-xl pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-700 focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="08123456789" required/>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Address</label>
                    <div class="relative mt-1">
                        <span class="material-symbols-outlined absolute left-3 top-3 text-gray-400">home</span>
                        <textarea name="alamat" rows="2" class="w-full border border-gray-200 dark:border-slate-600 rounded-xl pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-700 focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Your complete address" required></textarea>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Password</label>
                    <div class="relative mt-1">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">lock</span>
                        <input name="password" type="password" class="w-full border border-gray-200 dark:border-slate-600 rounded-xl pl-10 pr-4 py-3 bg-gray-50 dark:bg-slate-700 focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="••••••••" required/>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Minimal 6 karakter</p>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary hover:from-primary-dark hover:to-secondary-dark text-white font-bold py-3 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 mt-6">
                    <span>Create Account</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
            </form>

            <p class="text-center text-gray-600 dark:text-gray-400 text-sm mt-6">
                Already have an account? 
                <a href="login.php" class="text-primary font-bold hover:underline">Sign In</a>
            </p>
        </div>
    </div>
</div>

<?= global_route_script() ?>
</body>
</html>