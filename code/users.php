<?php
require_once 'common.php';
require_admin();

$db = get_db();
$message = '';
$error = '';

// Handle delete user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM user WHERE id_user = ? AND role != "admin"');
    if ($stmt->execute([$id])) {
        $message = 'User berhasil dihapus';
    } else {
        $error = 'Gagal menghapus user';
    }
}

// Handle add/edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            // Simpan password plain text
            $stmt = $db->prepare('INSERT INTO user (nama, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, ?)');
            if ($stmt->execute([$_POST['nama'], $_POST['email'], $_POST['password'], $_POST['no_hp'], $_POST['alamat'], $_POST['role']])) {
                $message = 'User berhasil ditambahkan';
            } else {
                $error = 'Gagal menambahkan user';
            }
        } elseif ($_POST['action'] === 'edit') {
            // Jika password diisi, update password
            if (!empty($_POST['password'])) {
                $stmt = $db->prepare('UPDATE user SET nama = ?, email = ?, password = ?, no_hp = ?, alamat = ?, role = ? WHERE id_user = ?');
                $stmt->execute([$_POST['nama'], $_POST['email'], $_POST['password'], $_POST['no_hp'], $_POST['alamat'], $_POST['role'], $_POST['id_user']]);
            } else {
                // Update tanpa mengubah password
                $stmt = $db->prepare('UPDATE user SET nama = ?, email = ?, no_hp = ?, alamat = ?, role = ? WHERE id_user = ?');
                $stmt->execute([$_POST['nama'], $_POST['email'], $_POST['no_hp'], $_POST['alamat'], $_POST['role'], $_POST['id_user']]);
            }
            $message = 'User berhasil diupdate';
        }
    }
}

// Get all users
$stmt = $db->query('SELECT * FROM user ORDER BY id_user DESC');
$users = $stmt->fetchAll();

// Get stats per role
$stmt = $db->query('SELECT role, COUNT(*) as count FROM user GROUP BY role');
$roleStats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Manage Users - Admin</title>
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
<style>
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal.active { display: flex; }
.modal-content { max-width: 500px; width: 90%; }
</style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-900 dark:to-slate-800 min-h-screen pb-20 md:pb-0">

<!-- Desktop Sidebar -->
<div class="hidden md:flex md:fixed md:inset-y-0 md:left-0 md:w-72 bg-white dark:bg-slate-800 shadow-xl flex-col">
    <div class="flex items-center justify-center p-6 border-b dark:border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">local_laundry_service</span>
            </div>
            <span class="text-xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Admin Panel</span>
        </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-2">
        <a href="admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <a href="verify_payments.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">verified</span>
            <span>Verifikasi Pembayaran</span>
        </a>
        <a href="reports.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">assessment</span>
            <span>Reports</span>
        </a>
        <a href="history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">receipt_long</span>
            <span>All Orders</span>
        </a>
        <a href="price.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">price_check</span>
            <span>Services</span>
        </a>
        <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-semibold">
            <span class="material-symbols-outlined">group</span>
            <span>Users</span>
        </a>
        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            <span class="material-symbols-outlined">account_circle</span>
            <span>Profile</span>
        </a>
    </nav>
    
    <div class="p-4 border-t dark:border-slate-700">
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-xl">admin_panel_settings</span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 dark:text-white">Administrator</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Full Access</p>
            </div>
            <button id="themeToggle" class="text-xs px-2 py-1 rounded-full border dark:border-slate-600">🌙</button>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="md:ml-72">
    <div class="container-responsive py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">Manage Users</h1>
            <button onclick="showAddModal()" class="bg-primary text-white px-4 py-2 rounded-xl font-semibold hover:bg-primary-dark transition flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span>
                Add User
            </button>
        </div>

        <?php if ($message): ?>
        <div class="mb-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 rounded-xl p-3">
            <p class="text-emerald-700 dark:text-emerald-300"><?= htmlspecialchars($message) ?></p>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="mb-4 bg-red-50 dark:bg-red-900/30 border border-red-200 rounded-xl p-3">
            <p class="text-red-700 dark:text-red-300"><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <?php foreach($roleStats as $stat): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm capitalize"><?= $stat['role'] ?></p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $stat['count'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Users Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">ID</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Name</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Email</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Phone</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Role</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr class="border-b dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50">
                            <td class="px-4 py-3"><?= $user['id_user'] ?></td>
                            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($user['nama']) ?></td>
                            <td class="px-4 py-3 text-sm"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 py-3 text-sm"><?= htmlspecialchars($user['no_hp'] ?? '-') ?></td>
                            <td class="px-4 py-3">
                                <span class="badge <?= $user['role'] == 'admin' ? 'badge-success' : ($user['role'] == 'supervisor' ? 'badge-info' : 'badge-secondary') ?>">
                                    <?= $user['role'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='editUser(<?= json_encode($user) ?>)' class="text-primary hover:text-primary-dark">
                                        <span class="material-symbols-outlined text-sm">edit</span>
                                    </button>
                                    <?php if($user['role'] != 'admin'): ?>
                                    <a href="?delete=<?= $user['id_user'] ?>" onclick="return confirm('Yakin hapus user ini?')" class="text-red-500 hover:text-red-700">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="userModal" class="modal">
    <div class="modal-content bg-white dark:bg-slate-800 rounded-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 id="modalTitle" class="text-xl font-bold text-gray-800 dark:text-white">Add User</h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <form method="POST" id="userForm">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="id_user" id="userId">
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-semibold">Name</label>
                    <input type="text" name="nama" id="userName" class="w-full border rounded-xl p-2 mt-1 dark:bg-slate-700" required>
                </div>
                <div>
                    <label class="text-sm font-semibold">Email</label>
                    <input type="email" name="email" id="userEmail" class="w-full border rounded-xl p-2 mt-1 dark:bg-slate-700" required>
                </div>
                <div>
                    <label class="text-sm font-semibold">Password</label>
                    <input type="text" name="password" id="userPassword" class="w-full border rounded-xl p-2 mt-1 dark:bg-slate-700" placeholder="Min 6 karakter">
                    <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                </div>
                <div>
                    <label class="text-sm font-semibold">Phone</label>
                    <input type="text" name="no_hp" id="userPhone" class="w-full border rounded-xl p-2 mt-1 dark:bg-slate-700">
                </div>
                <div>
                    <label class="text-sm font-semibold">Address</label>
                    <textarea name="alamat" id="userAddress" rows="2" class="w-full border rounded-xl p-2 mt-1 dark:bg-slate-700"></textarea>
                </div>
                <div>
                    <label class="text-sm font-semibold">Role</label>
                    <select name="role" id="userRole" class="w-full border rounded-xl p-2 mt-1 dark:bg-slate-700">
                        <option value="customer">Customer</option>
                        <option value="worker">Worker</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-primary text-white py-2 rounded-xl font-semibold mt-4 hover:bg-primary-dark transition">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('modalTitle').innerText = 'Add User';
    document.getElementById('formAction').value = 'add';
    document.getElementById('userId').value = '';
    document.getElementById('userName').value = '';
    document.getElementById('userEmail').value = '';
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').required = false;
    document.getElementById('userPhone').value = '';
    document.getElementById('userAddress').value = '';
    document.getElementById('userRole').value = 'customer';
    document.getElementById('userModal').classList.add('active');
}

function editUser(user) {
    document.getElementById('modalTitle').innerText = 'Edit User';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('userId').value = user.id_user;
    document.getElementById('userName').value = user.nama;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').placeholder = 'Kosongkan jika tidak diubah';
    document.getElementById('userPhone').value = user.no_hp || '';
    document.getElementById('userAddress').value = user.alamat || '';
    document.getElementById('userRole').value = user.role;
    document.getElementById('userModal').classList.add('active');
}

function closeModal() {
    document.getElementById('userModal').classList.remove('active');
}

// Close modal when clicking outside
document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?= global_route_script() ?>
</body>
</html>