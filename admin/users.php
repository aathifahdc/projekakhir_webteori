<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $query = "INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$_POST['username'], $_POST['email'], $password, $_POST['full_name'], $_POST['phone'], $_POST['address'], $_POST['role']]);
                $success = "User berhasil ditambahkan";
                break;
                
            case 'edit':
                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $query = "UPDATE users SET username = ?, email = ?, password = ?, full_name = ?, phone = ?, address = ?, role = ? WHERE id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$_POST['username'], $_POST['email'], $password, $_POST['full_name'], $_POST['phone'], $_POST['address'], $_POST['role'], $_POST['id']]);
                } else {
                    $query = "UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, address = ?, role = ? WHERE id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$_POST['username'], $_POST['email'], $_POST['full_name'], $_POST['phone'], $_POST['address'], $_POST['role'], $_POST['id']]);
                }
                $success = "User berhasil diupdate";
                break;
                
            case 'delete':
                // Check if user has orders
                $check_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->execute([$_POST['id']]);
                $order_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                if ($order_count > 0) {
                    $error = "Tidak dapat menghapus user yang memiliki riwayat pesanan";
                } else {
                    $query = "DELETE FROM users WHERE id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$_POST['id']]);
                    $success = "User berhasil dihapus";
                }
                break;
        }
    }
}

// Get search parameter
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';

// Build query
$query = "SELECT u.*, 
          COUNT(o.id) as order_count,
          COALESCE(SUM(o.total_amount), 0) as total_spent
          FROM users u 
          LEFT JOIN orders o ON u.id = o.user_id 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role_filter) {
    $query .= " AND u.role = ?";
    $params[] = $role_filter;
}

$query .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user statistics
$stats_query = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
    SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as user_count,
    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as new_today
    FROM users";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Users - Marie Pet Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FFA07A',
                        secondary: '#FF8C61',
                        orangeLight: '#FFD8CC',
                        orangeDark: '#FF7F50'
                    },
                    borderRadius: { 'button': '8px' }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #FFF5F7;
            display: flex;
            min-height: 100vh;
            padding: 0;
        }
        .logo-text { font-family: 'Pacifico', serif; }
        
        .sidebar {
            flex-shrink: 0;
            width: 256px;
            background-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            z-index: 50;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            min-height: 100vh;
            align-self: stretch;
        }
        .sidebar .logo-container {
            padding: 1rem;
            margin-bottom: 2rem;
        }
        .sidebar nav { flex-grow: 1; }
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #1f2937;
            font-weight: 500;
            border-radius: 8px;
            margin: 6px 12px;
            text-decoration: none;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #FFD8CC;
            color: #FFA07A;
        }
        .sidebar a i {
            margin-right: 12px;
            font-size: 1.3rem;
        }
        .sidebar .logout-section {
            margin-top: auto;
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .content {
            flex-grow: 1;
            padding: 2rem;
            background-color: #fcfcfc;
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background-color: #FFA07A;
            color: white;
            font-weight: 600;
            border-bottom: none;
        }
        tbody tr {
            border-bottom: 1px solid #ddd;
        }
        tbody tr:last-child {
            border-bottom: none;
        }
        tbody tr:hover {
            background-color: #FFD8CC;
        }
        
        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #FFA07A;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        .modal.show { display: flex; }
        .modal-content {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            width: 90%;
            max-width: 28rem;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-content input, .modal-content select, .modal-content textarea {
            border: 1px solid #d1d5db;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            transition: border-color 0.2s ease-in-out;
            width: 100%;
        }
        .modal-content input:focus, .modal-content select:focus, .modal-content textarea:focus {
            outline: none;
            border-color: #FFA07A;
            box-shadow: 0 0 0 1px #FFA07A;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <h1 class="logo-text text-2xl text-primary">Marie Pet Shop</h1>
            <p class="text-sm text-gray-600">Admin Panel</p>
        </div>
        
        <nav>
            <a href="dashboard.php">
                <i class="ri-dashboard-line"></i>
                Dashboard
            </a>
            <a href="products.php">
                <i class="ri-shopping-bag-line"></i>
                Produk
            </a>
            <a href="categories.php">
                <i class="ri-list-check"></i>
                Kategori
            </a>
            <a href="orders.php">
                <i class="ri-file-list-line"></i>
                Pesanan
            </a>
            <a href="users.php" class="active">
                <i class="ri-user-line"></i>
                Users
            </a>
        </nav>
        
        <div class="logout-section">
            <a href="../auth/logout.php">
                <i class="ri-logout-box-line"></i>
                Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Manajemen Users</h1>
                <p class="text-gray-600">Kelola pengguna sistem</p>
            </div>
            <button onclick="openAddModal()" class="bg-primary text-white px-6 py-3 rounded-button hover:bg-secondary transition-colors">
                <i class="ri-add-line mr-2"></i>
                Tambah User
            </button>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Users</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_users']; ?></p>
                    </div>
                    <i class="ri-user-line text-3xl text-primary"></i>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Admin</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo $stats['admin_count']; ?></p>
                    </div>
                    <i class="ri-admin-line text-3xl text-blue-500"></i>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Customer</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $stats['user_count']; ?></p>
                    </div>
                    <i class="ri-user-3-line text-3xl text-green-500"></i>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Baru Hari Ini</p>
                        <p class="text-2xl font-bold text-primary"><?php echo $stats['new_today']; ?></p>
                    </div>
                    <i class="ri-user-add-line text-3xl text-primary"></i>
                </div>
            </div>
        </div>
        
        <!-- Search and Filter -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <form method="GET" class="flex gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Cari nama, email, atau username..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">Semua Role</option>
                        <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>User</option>
                    </select>
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-button hover:bg-secondary transition-colors">
                    <i class="ri-search-line mr-2"></i>Cari
                </button>
                <a href="users.php" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-button hover:bg-gray-400 transition-colors">
                    Reset
                </a>
            </form>
        </div>
        
        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Info</th>
                        <th>Role</th>
                        <th>Kontak</th>
                        <th>Statistik</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td>
                            <div>
                                <div class="font-semibold"><?php echo $user['full_name']; ?></div>
                                <div class="text-sm text-gray-500">@<?php echo $user['username']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $user['email']; ?></div>
                            </div>
                        </td>
                        <td>
                            <span class="px-2 py-1 text-xs rounded-full <?php echo $user['role'] == 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="text-sm">
                                <div><?php echo $user['phone'] ?: '-'; ?></div>
                                <div class="text-gray-500"><?php echo $user['address'] ? substr($user['address'], 0, 30) . '...' : '-'; ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm">
                                <div><?php echo $user['order_count']; ?> pesanan</div>
                                <div class="text-gray-500">Rp <?php echo number_format($user['total_spent']); ?></div>
                            </div>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" 
                                    class="bg-blue-500 text-white px-3 py-1 rounded mr-2 hover:bg-blue-600">
                                <i class="ri-edit-line"></i>
                            </button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <button onclick="deleteUser(<?php echo $user['id']; ?>, <?php echo $user['order_count']; ?>)" 
                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold" id="modalTitle">Tambah User</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            
            <form id="userForm" method="POST" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="userId">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="full_name" id="userFullName" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" id="userUsername" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="userEmail" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="userPassword">
                    <p class="text-xs text-gray-500 mt-1" id="passwordHelp">Kosongkan jika tidak ingin mengubah password</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                        <input type="text" name="phone" id="userPhone">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role" id="userRole" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="address" id="userAddress" rows="3"></textarea>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-primary text-white py-2 rounded-button hover:bg-secondary transition-colors">
                        Simpan
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-button hover:bg-gray-400 transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Tambah User';
            document.getElementById('formAction').value = 'add';
            document.getElementById('userForm').reset();
            document.getElementById('passwordHelp').style.display = 'none';
            document.getElementById('userPassword').required = true;
            document.getElementById('userModal').classList.add('show');
        }
        
        function editUser(user) {
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('userId').value = user.id;
            document.getElementById('userFullName').value = user.full_name;
            document.getElementById('userUsername').value = user.username;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userPassword').value = '';
            document.getElementById('userPhone').value = user.phone || '';
            document.getElementById('userRole').value = user.role;
            document.getElementById('userAddress').value = user.address || '';
            document.getElementById('passwordHelp').style.display = 'block';
            document.getElementById('userPassword').required = false;
            document.getElementById('userModal').classList.add('show');
        }
        
        function deleteUser(id, orderCount) {
            if (orderCount > 0) {
                alert('Tidak dapat menghapus user yang memiliki riwayat pesanan!');
                return;
            }
            
            if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function closeModal() {
            document.getElementById('userModal').classList.remove('show');
        }
    </script>
</body>
</html>
