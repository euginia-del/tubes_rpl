<?php
require_once 'common.php';
$user = require_customer();

$service_id = $_GET['service_id'] ?? 1;
$service = get_service($service_id);
if (!$service) {
    header('Location: neworder.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weight = floatval($_POST['weight'] ?? 1);
    $notes = $_POST['notes'] ?? '';
    
    // Hitung harga dengan diskon
    $price_per_kg = $service['harga_per_kg'];
    $subtotal = $weight * $price_per_kg;
    $discount = 0;
    $discount_percent = 0;
    
    // Diskon untuk berat > 5 kg
    if ($weight > 5 && $weight <= 10) {
        $discount_percent = 5;
        $discount = $subtotal * 0.05;
    } elseif ($weight > 10 && $weight <= 20) {
        $discount_percent = 10;
        $discount = $subtotal * 0.10;
    } elseif ($weight > 20) {
        $discount_percent = 15;
        $discount = $subtotal * 0.15;
    }
    
    $total_price = $subtotal - $discount;
    
    set_current_order([
        'id_layanan' => $service['id_layanan'],
        'service_name' => $service['nama_layanan'],
        'weight' => $weight,
        'notes' => $notes,
        'subtotal' => $subtotal,
        'discount' => $discount,
        'discount_percent' => $discount_percent,
        'total_price' => $total_price
    ]);
    
    header('Location: schedule.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Order Detail - LaundryApp</title>
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
<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-900 dark:to-slate-800 min-h-screen pb-20 md:pb-0">

<!-- Mobile Header -->
<div class="md:hidden bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-40">
    <div class="flex items-center justify-between px-4 py-3">
        <a href="neworder.php" class="text-gray-600 dark:text-gray-300">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <span class="text-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Order Detail</span>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>
</div>

<!-- Main Content -->
<div class="container-responsive py-6 max-w-3xl mx-auto">
    <!-- Desktop Header -->
    <div class="hidden md:flex items-center justify-between mb-6">
        <a href="neworder.php" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-primary transition">
            <span class="material-symbols-outlined">arrow_back</span>
            <span>Back to Services</span>
        </a>
        <button id="themeToggle" class="text-xs px-3 py-1 rounded-full border dark:border-slate-600">🌙</button>
    </div>

    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">Order Details</h1>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden">
        <!-- Service Info -->
        <div class="bg-gradient-to-r from-primary to-secondary p-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl">local_laundry_service</span>
                </div>
                <div>
                    <p class="text-white/80 text-sm">Selected Service</p>
                    <h2 class="text-2xl font-bold"><?= htmlspecialchars($service['nama_layanan']) ?></h2>
                    <p class="text-white/80 text-sm mt-1">Estimasi: <?= $service['estimasi_hari'] ?> hari</p>
                </div>
            </div>
        </div>

        <form method="post" class="p-6 space-y-6">
            <!-- Price Display -->
            <div class="bg-gray-50 dark:bg-slate-700/50 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Harga per kg</span>
                    <span class="text-2xl font-bold text-primary">Rp <?= number_format($service['harga_per_kg'],0,',','.') ?></span>
                </div>
            </div>

            <!-- Weight Input -->
            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-2">
                    Berat Cucian (kg)
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">fitness_center</span>
                    <input type="number" name="weight" id="weight" step="0.5" min="0.5" 
                           class="w-full border border-gray-200 dark:border-slate-600 rounded-xl pl-12 pr-4 py-3 bg-gray-50 dark:bg-slate-700 focus:ring-2 focus:ring-primary focus:border-transparent" 
                           value="1" required onchange="updateTotal()" onkeyup="updateTotal()">
                </div>
                <p class="text-xs text-gray-400 mt-1">Minimal 0.5 kg</p>
            </div>

            <!-- Discount Info -->
            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 rounded-xl p-4 border border-amber-200 dark:border-amber-800">
                <div class="flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-amber-600">local_offer</span>
                    <span class="font-semibold text-amber-800 dark:text-amber-300">🎉 Promo Spesial!</span>
                </div>
                <div class="space-y-1 text-sm text-amber-700 dark:text-amber-400">
                    <p>• Beli 5-10 kg → Diskon 5%</p>
                    <p>• Beli 10-20 kg → Diskon 10%</p>
                    <p>• Beli >20 kg → Diskon 15%</p>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-2">
                    Catatan (Opsional)
                </label>
                <textarea name="notes" rows="3" 
                          class="w-full border border-gray-200 dark:border-slate-600 rounded-xl p-3 bg-gray-50 dark:bg-slate-700 focus:ring-2 focus:ring-primary focus:border-transparent" 
                          placeholder="Contoh: Pakai deterjen mild, pisahkan warna terang dan gelap..."></textarea>
            </div>

            <!-- Price Breakdown -->
            <div class="bg-gray-50 dark:bg-slate-700/50 rounded-xl p-4 space-y-2">
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Subtotal</span>
                    <span id="subtotalDisplay">Rp <?= number_format($service['harga_per_kg'],0,',','.') ?></span>
                </div>
                <div class="flex justify-between text-emerald-600 dark:text-emerald-400" id="discountRow" style="display: none;">
                    <span>Diskon <span id="discountPercent">0</span>%</span>
                    <span id="discountDisplay">- Rp 0</span>
                </div>
                <div class="border-t border-gray-200 dark:border-slate-600 pt-2 mt-2">
                    <div class="flex justify-between">
                        <span class="font-semibold text-gray-800 dark:text-white">Total Harga</span>
                        <span class="text-2xl font-bold text-primary" id="totalDisplay">
                            Rp <?= number_format($service['harga_per_kg'],0,',','.') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary hover:from-primary-dark hover:to-secondary-dark text-white font-bold py-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                <span>Continue to Schedule</span>
                <span class="material-symbols-outlined">calendar_month</span>
            </button>
        </form>
    </div>
</div>

<script>
function updateTotal() {
    let weight = parseFloat(document.getElementById('weight').value);
    if (isNaN(weight)) weight = 0;
    let pricePerKg = <?= $service['harga_per_kg'] ?>;
    let subtotal = weight * pricePerKg;
    let discount = 0;
    let discountPercent = 0;
    
    // Hitung diskon berdasarkan berat
    if (weight > 5 && weight <= 10) {
        discountPercent = 5;
        discount = subtotal * 0.05;
    } else if (weight > 10 && weight <= 20) {
        discountPercent = 10;
        discount = subtotal * 0.10;
    } else if (weight > 20) {
        discountPercent = 15;
        discount = subtotal * 0.15;
    }
    
    let total = subtotal - discount;
    
    // Update display
    document.getElementById('subtotalDisplay').innerHTML = 'Rp ' + subtotal.toLocaleString('id-ID');
    document.getElementById('totalDisplay').innerHTML = 'Rp ' + total.toLocaleString('id-ID');
    
    if (discount > 0) {
        document.getElementById('discountRow').style.display = 'flex';
        document.getElementById('discountPercent').innerHTML = discountPercent;
        document.getElementById('discountDisplay').innerHTML = '- Rp ' + discount.toLocaleString('id-ID');
    } else {
        document.getElementById('discountRow').style.display = 'none';
    }
}

// Initial call
updateTotal();
</script>

<?= global_route_script() ?>
</body>
</html>