<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get user orders
$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Pesanan Saya - Marie Pet Shop</title>
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
            <a href="orders.php" class="active">
                <i class="ri-file-list-line"></i>
                Pesanan Saya
            </a>
            <a href="profile.php">
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
            <h1 class="text-3xl font-bold text-gray-900">Pesanan Saya</h1>
            <p class="text-gray-600">Lihat riwayat dan status pesanan Anda</p>
        </div>
        
        <?php if (empty($orders)): ?>
        <div class="text-center py-12">
            <i class="ri-file-list-line text-6xl text-gray-400 mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Belum ada pesanan</h2>
            <p class="text-gray-600 mb-6">Mulai berbelanja untuk melihat pesanan Anda di sini</p>
            <a href="products.php" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary transition duration-300">
                Mulai Belanja
            </a>
        </div>
        <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($orders as $order): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">Pesanan #<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></h3>
                        <p class="text-gray-600"><?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div class="text-right">
                        <?php
                        $status_colors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'processing' => 'bg-blue-100 text-blue-800',
                            'shipped' => 'bg-purple-100 text-purple-800',
                            'delivered' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800'
                        ];
                        $status_text = [
                            'pending' => 'Menunggu',
                            'processing' => 'Diproses',
                            'shipped' => 'Dikirim',
                            'delivered' => 'Selesai',
                            'cancelled' => 'Dibatalkan'
                        ];
                        ?>
                        <span class="px-3 py-1 text-sm rounded-full <?php echo $status_colors[$order['status']]; ?>">
                            <?php echo $status_text[$order['status']]; ?>
                        </span>
                        <div class="text-lg font-semibold text-primary mt-2">
                            Rp <?php echo number_format($order['total_amount']); ?>
                        </div>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Alamat Pengiriman</h4>
                            <p class="text-gray-600 text-sm"><?php echo nl2br($order['shipping_address']); ?></p>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">No. Telepon</h4>
                            <p class="text-gray-600 text-sm"><?php echo $order['phone']; ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex justify-end">
                        <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)" 
                                class="bg-primary text-white px-4 py-2 rounded-button hover:bg-secondary transition-colors">
                            Lihat Detail
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Order Detail Modal -->
    <div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Detail Pesanan</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <div id="orderDetails">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function viewOrderDetails(orderId) {
            document.getElementById('orderDetails').innerHTML = `
                <div class="text-center py-8">
                    <i class="ri-loader-line text-4xl text-gray-400 animate-spin"></i>
                    <p class="text-gray-600 mt-2">Memuat detail pesanan...</p>
                </div>
            `;
            document.getElementById('orderModal').classList.remove('hidden');
            document.getElementById('orderModal').classList.add('flex');
            
            // Fetch order details
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let itemsHtml = '';
                        data.items.forEach(item => {
                            itemsHtml += `
                                <div class="flex justify-between items-center py-2 border-b">
                                    <div>
                                        <div class="font-medium">${item.name}</div>
                                        <div class="text-sm text-gray-500">${item.quantity}x Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</div>
                                    </div>
                                    <div class="font-semibold">Rp ${new Intl.NumberFormat('id-ID').format(item.price * item.quantity)}</div>
                                </div>
                            `;
                        });
                        
                        document.getElementById('orderDetails').innerHTML = `
                            <div class="space-y-4">
                                <div class="border-b pb-4">
                                    <h4 class="font-semibold mb-2">Item Pesanan</h4>
                                    ${itemsHtml}
                                    <div class="flex justify-between items-center pt-2 font-semibold text-lg">
                                        <span>Total</span>
                                        <span class="text-primary">Rp ${new Intl.NumberFormat('id-ID').format(data.order.total_amount)}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        document.getElementById('orderDetails').innerHTML = `
                            <div class="text-center py-8">
                                <p class="text-red-600">Error memuat detail pesanan</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('orderDetails').innerHTML = `
                        <div class="text-center py-8">
                            <p class="text-red-600">Error memuat detail pesanan</p>
                        </div>
                    `;
                });
        }
        
        function closeModal() {
            document.getElementById('orderModal').classList.add('hidden');
            document.getElementById('orderModal').classList.remove('flex');
        }
    </script>
</body>
</html>
