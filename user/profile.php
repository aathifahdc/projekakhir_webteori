<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // Check if email is already used by another user
    $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$email, $_SESSION['user_id']]);
    
    if ($check_stmt->rowCount() > 0) {
        $error = "Email sudah digunakan oleh user lain";
    } else {
        // Update password if provided
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET full_name = ?, email = ?, password = ?, phone = ?, address = ? WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            $success = $update_stmt->execute([$full_name, $email, $password, $phone, $address, $_SESSION['user_id']]);
        } else {
            $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            $success = $update_stmt->execute([$full_name, $email, $phone, $address, $_SESSION['user_id']]);
        }
        
        if ($success) {
            $_SESSION['full_name'] = $full_name;
            $success_msg = "Profile berhasil diupdate";
            // Refresh user data
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Gagal mengupdate profile";
        }
    }
}

// Get cart count
$cart_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
$cart_stmt = $db->prepare($cart_query);
$cart_stmt->execute([$_SESSION['user_id']]);
$cart_count = $cart_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Marie Pet Shop</title>
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
            position: relative;
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
        
        .cart-badge {
            background-color: #FF7F50;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.75rem;
            position: absolute;
            top: -8px;
            right: -8px;
            min-width: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <h1 class="logo-text text-2xl text-primary">Marie Pet Shop</h1>
            <p class="text-sm text-gray-600">Happy Shopping!</p>
        </div>
        
        <nav>
            <a href="dashboard.php">
                <i class="ri-store-line"></i>
                Beranda
            </a>
            <a href="products.php">
                <i class="ri-shopping-bag-line"></i>
                Produk
            </a>
            <a href="cart.php">
                <i class="ri-shopping-cart-line"></i>
                Keranjang
                <?php if ($cart_count > 0): ?>
                <span class="cart-badge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="orders.php">
                <i class="ri-file-list-line"></i>
                Pesanan Saya
            </a>
            <a href="profile.php" class="active">
                <i class="ri-user-line"></i>
                Profile
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
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Profile Saya</h1>
            <p class="text-gray-600">Kelola informasi akun Anda</p>
        </div>
        
        <?php if (isset($success_msg)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo $success_msg; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="full_name" id="full_name" value="<?php echo $user['full_name']; ?>" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" value="<?php echo $user['username']; ?>" readonly
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                        <p class="text-xs text-gray-500 mt-1">Username tidak dapat diubah</p>
                    </div>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="<?php echo $user['email']; ?>" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                    <input type="text" name="phone" id="phone" value="<?php echo $user['phone']; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="address" id="address" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"><?php echo $user['address']; ?></textarea>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password" id="password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-primary text-white px-6 py-3 rounded-button hover:bg-secondary transition-colors font-semibold">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Account Statistics -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php
            // Get user statistics
            $stats_query = "SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_spent,
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed_orders
                FROM orders WHERE user_id = ?";
            $stats_stmt = $db->prepare($stats_query);
            $stats_stmt->execute([$_SESSION['user_id']]);
            $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Pesanan</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_orders']; ?></p>
                    </div>
                    <i class="ri-file-list-line text-3xl text-primary"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Belanja</p>
                        <p class="text-2xl font-bold text-primary">Rp <?php echo number_format($stats['total_spent']); ?></p>
                    </div>
                    <i class="ri-money-dollar-circle-line text-3xl text-primary"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Pesanan Selesai</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $stats['completed_orders']; ?></p>
                    </div>
                    <i class="ri-check-double-line text-3xl text-green-500"></i>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
