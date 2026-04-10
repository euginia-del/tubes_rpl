<?php
require_once __DIR__ . '/common.php';
if (isset($_GET['logout'])) { logout_user(); header('Location: login.php'); exit; }
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    // Simple registration stub; in production use DB and hashing.
    set_flash('success', 'Akun berhasil dibuat. Silakan login.');
    header('Location: login.php');
    exit;
}
?>
\n<!DOCTYPE html>

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
                        "primary": "#3398db",
                        "background-light": "#f6f7f8",
                        "background-dark": "#121a20",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
                },
            },
        }
    </script>
<style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined { font-size: 24px; }
    </style>
<title>Join LaundryFresh</title>
<style>
    body {
      min-height: max(884px, 100dvh);
    }
  </style>
  </head>
<body class="bg-background-light dark:bg-background-dark font-display min-h-screen">
<div class="relative flex h-auto min-h-screen w-full flex-col bg-background-light dark:bg-background-dark group/design-root overflow-x-hidden">
<!-- Top App Bar -->
<div class="flex items-center bg-background-light dark:bg-background-dark p-4 pb-2 justify-between sticky top-0 z-10">
<div class="text-slate-900 dark:text-slate-100 flex size-12 shrink-0 items-center justify-start cursor-pointer">
<span class="material-symbols-outlined">arrow_back</span>
</div>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] flex-1 text-center pr-12">Create Account</h2>
<button id="themeToggle" class="text-slate-500 dark:text-slate-200 text-xs font-semibold px-2 py-1 rounded-full border border-slate-200 dark:border-slate-700">Mode</button>
</div>
<div class="flex flex-col max-w-[480px] mx-auto w-full">
<!-- Hero Image Section -->
<div class="@container">
<div class="px-4 py-3">
<div class="w-full bg-center bg-no-repeat bg-cover flex flex-col justify-end overflow-hidden rounded-xl min-h-64 shadow-sm border border-slate-200 dark:border-slate-800" data-alt="A modern and clean laundromat interior with bright lighting" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAIwl8kMPhVRcgqYz-41egtHd5PND5rwJ4sgvIBK6MpsuHySeFLfownSLpUSmxupw6arIg8Qm3cdnmdeQ7SW2MqjAXG8aNBb0_hWOwxwB4AFUQhq_ongBaQDeEZcXg6mg3KRQbCMXVy0ZU8EZDFa3rAaLwJhs5FCfvZeRAZer-4tbWFpMO2gmFPf5YeJEA6k7f_HWgPk5S4v3RAHYNWzLtOtGvNf6u_B40MFx0HaiD-M2Uge1ZwgynEzKnZUlvwXqndEkCfdEw44dg");'>
</div>
</div>
</div>
<!-- Welcome Text -->
<div class="px-4 pt-6 pb-2">
<h1 class="text-slate-900 dark:text-slate-100 tracking-tight text-[32px] font-bold leading-tight">Join LaundryFresh</h1>
<p class="text-slate-600 dark:text-slate-400 text-base font-normal leading-normal mt-2">Sign up to manage your laundry pick-ups and professional cleaning services.</p>
</div>
<!-- Registration Form -->
<div class="flex flex-col gap-4 px-4 py-4">
<label class="flex flex-col w-full">
<p class="text-slate-800 dark:text-slate-200 text-sm font-semibold leading-normal pb-2">Full Name</p>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">person</span>
<input class="form-input flex w-full rounded-lg text-slate-900 dark:text-slate-100 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-primary h-14 pl-12 pr-4 placeholder:text-slate-400 text-base font-normal" placeholder="Alex Johnson"/>
</div>
</label>
<label class="flex flex-col w-full">
<p class="text-slate-800 dark:text-slate-200 text-sm font-semibold leading-normal pb-2">Email Address</p>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">mail</span>
<input class="form-input flex w-full rounded-lg text-slate-900 dark:text-slate-100 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-primary h-14 pl-12 pr-4 placeholder:text-slate-400 text-base font-normal" placeholder="alex@example.com" type="email"/>
</div>
</label>
<label class="flex flex-col w-full">
<p class="text-slate-800 dark:text-slate-200 text-sm font-semibold leading-normal pb-2">Phone Number</p>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">call</span>
<input class="form-input flex w-full rounded-lg text-slate-900 dark:text-slate-100 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-primary h-14 pl-12 pr-4 placeholder:text-slate-400 text-base font-normal" placeholder="+1 (555) 000-0000" type="tel"/>
</div>
</label>
<label class="flex flex-col w-full">
<p class="text-slate-800 dark:text-slate-200 text-sm font-semibold leading-normal pb-2">Password</p>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">lock</span>
<input class="form-input flex w-full rounded-lg text-slate-900 dark:text-slate-100 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-primary h-14 pl-12 pr-4 placeholder:text-slate-400 text-base font-normal" placeholder="••••••••" type="password"/>
<span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 cursor-pointer">visibility</span>
</div>
</label>
</div>
<!-- Terms and CTA -->
<div class="px-4 py-4 mt-2">
<button class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-xl shadow-lg transition-colors flex items-center justify-center gap-2 mb-6">
<span>Create Account</span>
<span class="material-symbols-outlined">arrow_forward</span>
</button>
<p class="text-slate-500 dark:text-slate-400 text-xs text-center leading-relaxed">
                    By creating an account, you agree to our <span class="text-primary font-medium cursor-pointer">Terms of Service</span> and <span class="text-primary font-medium cursor-pointer">Privacy Policy</span>.
                </p>
</div>
<!-- Footer Login Link -->
<div class="px-4 py-8 text-center">
<p class="text-slate-600 dark:text-slate-400 text-sm">
                    Already have an account? <a class="text-primary font-bold" href="login.php">Log In</a>
</p>
</div>
<div class="h-10"></div>
</div>
</div>
<?php echo global_route_script(); ?>
</body></html>

