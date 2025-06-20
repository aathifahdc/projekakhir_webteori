<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get featured products
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active' 
          ORDER BY p.created_at DESC LIMIT 8";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>User Dashboard - Marie Pet Shop</title>
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
                    borderRadius: {
                        'button': '8px'
                    },
                },
            },
        };
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
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Open Sans', sans-serif;
        }
        .logo-text {
            font-family: 'Pacifico', serif;
        }

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
        .sidebar nav {
            flex-grow: 1;
        }
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

        .product-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Toast notification styles */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10B981;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        .toast.show {
            transform: translateX(0);
        }
        .toast.error {
            background: #EF4444;
        }
    </style>
</head>
<body>
    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <span id="toast-message"></span>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <h1 class="logo-text text-2xl text-primary">Marie Pet Shop</h1>
            <p class="text-sm text-gray-600">Happy Shopping!</p>
        </div>
        
        <nav>
            <a href="dashboard.php" class="active">
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
                <span class="cart-badge" id="cart-count"><?= $cart_count ?></span>
            </a>
            <a href="orders.php">
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
            <h1 class="text-3xl font-bold text-gray-900">Selamat Datang di Marie Pet Shop</h1>
            <p class="text-gray-600">Temukan produk terbaik dengan harga terjangkau untuk anabul kesayangan!</p>
        </div>
        
        <!-- Produk Unggulan Pet Shop -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Produk Unggulan</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Makanan Kucing -->
                <div class="product-card">
                    <div class="h-48 rounded-lg mb-4 overflow-hidden">
                        <img src="../assets/catfood.jpg" alt="Makanan Kucing" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Makanan Kucing Premium</h3>
                    <p class="text-gray-600 text-sm mb-2">Gizi lengkap dan lezat untuk kucing Anda</p>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xl font-bold text-primary">Rp 35.000</span>
                        <span class="text-sm text-gray-500">Stok: 40</span>
                    </div>
                    <button onclick="addToCart(1, 'Makanan Kucing Premium', 35000)" class="w-full bg-primary text-white py-2 rounded-button hover:bg-secondary transition-colors">
                        <i class="ri-shopping-cart-line mr-2"></i> Tambah ke Keranjang
                    </button>
                </div>

                <!-- Kalung Kucing -->
                <div class="product-card">
                    <div class="h-48 rounded-lg mb-4 overflow-hidden">
                        <img src="../assets/kalungkucing.jpg" alt="Kalung Kucing" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Kalung Kucing Lucu</h3>
                    <p class="text-gray-600 text-sm mb-2">Aksesoris lucu untuk kucing kesayangan</p>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xl font-bold text-primary">Rp 20.000</span>
                        <span class="text-sm text-gray-500">Stok: 25</span>
                    </div>
                    <button onclick="addToCart(2, 'Kalung Kucing Lucu', 20000)" class="w-full bg-primary text-white py-2 rounded-button hover:bg-secondary transition-colors">
                        <i class="ri-shopping-cart-line mr-2"></i> Tambah ke Keranjang
                    </button>
                </div>

                <!-- Sisir Hewan -->
                <div class="product-card">
                    <div class="h-48 rounded-lg mb-4 overflow-hidden">
                        <img src="../assets/sisirgrooming.jpg" alt="Sisir Hewan" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Sisir Bulu Kucing</h3>
                    <p class="text-gray-600 text-sm mb-2">Menjaga bulu tetap rapi dan sehat</p>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xl font-bold text-primary">Rp 15.000</span>
                        <span class="text-sm text-gray-500">Stok: 60</span>
                    </div>
                    <button onclick="addToCart(3, 'Sisir Bulu Kucing', 15000)" class="w-full bg-primary text-white py-2 rounded-button hover:bg-secondary transition-colors">
                        <i class="ri-shopping-cart-line mr-2"></i> Tambah ke Keranjang
                    </button>
                </div>

                <!-- Tempat Tidur -->
                <div class="product-card">
                    <div class="h-48 rounded-lg mb-4 overflow-hidden">
                        <img src="../assets/catbed.jpg" alt="Tempat Tidur Kucing" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Tempat Tidur Kucing</h3>
                    <p class="text-gray-600 text-sm mb-2">Nyaman dan empuk untuk tidur si kucing</p>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xl font-bold text-primary">Rp 85.000</span>
                        <span class="text-sm text-gray-500">Stok: 15</span>
                    </div>
                    <button onclick="addToCart(4, 'Tempat Tidur Kucing', 85000)" class="w-full bg-primary text-white py-2 rounded-button hover:bg-secondary transition-colors">
                        <i class="ri-shopping-cart-line mr-2"></i> Tambah ke Keranjang
                    </button>
                </div>

                <!-- Mainan Kucing -->
                <div class="product-card">
                    <div class="h-48 rounded-lg mb-4 overflow-hidden">
                        <img src="../assets/cattoy.jpg" alt="Mainan Kucing" class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Mainan Kucing Interaktif</h3>
                    <p class="text-gray-600 text-sm mb-2">Melatih dan menghibur kucing Anda</p>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xl font-bold text-primary">Rp 18.000</span>
                        <span class="text-sm text-gray-500">Stok: 30</span>
                    </div>
                    <button onclick="addToCart(5, 'Mainan Kucing Interaktif', 18000)" class="w-full bg-primary text-white py-2 rounded-button hover:bg-secondary transition-colors">
                        <i class="ri-shopping-cart-line mr-2"></i> Tambah ke Keranjang
                    </button>
                </div>

            </div>
        </div>

        <!-- Categories -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Kategori Produk</h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Makanan -->
                <div class="bg-white p-6 rounded-lg shadow text-center hover:shadow-lg transition-shadow">
                    <i class="ri-restaurant-line text-4xl text-primary mb-3"></i>
                    <h3 class="font-semibold">Makanan</h3>
                    <p class="text-sm text-gray-500">Nutrisi lengkap untuk hewan</p>
                </div>

                <!-- Aksesoris -->
                <div class="bg-white p-6 rounded-lg shadow text-center hover:shadow-lg transition-shadow">
                    <i class="ri-collar-line text-4xl text-primary mb-3"></i>
                    <h3 class="font-semibold">Aksesoris</h3>
                    <p class="text-sm text-gray-500">Kalung, baju, dan perlengkapan lucu</p>
                </div>

                <!-- Perawatan -->
                <div class="bg-white p-6 rounded-lg shadow text-center hover:shadow-lg transition-shadow">
                    <i class="ri-scissors-2-line text-4xl text-primary mb-3"></i>
                    <h3 class="font-semibold">Perawatan</h3>
                    <p class="text-sm text-gray-500">Sisir, sampo, dan peralatan grooming</p>
                </div>

                <!-- Mainan -->
                <div class="bg-white p-6 rounded-lg shadow text-center hover:shadow-lg transition-shadow">
                    <i class="ri-gamepad-line text-4xl text-primary mb-3"></i>
                    <h3 class="font-semibold">Mainan</h3>
                    <p class="text-sm text-gray-500">Mainan seru dan edukatif</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addToCart(productId, productName, price) {
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="ri-loader-4-line mr-2 animate-spin"></i> Menambahkan...';
            button.disabled = true;

            // Send AJAX request
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    product_name: productName,
                    price: price,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    document.getElementById('cart-count').textContent = data.cart_count;
                    
                    // Show success toast
                    showToast(data.message, 'success');
                } else {
                    // Show error toast
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat menambahkan produk', 'error');
            })
            .finally(() => {
                // Reset button
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            
            toastMessage.textContent = message;
            toast.className = `toast ${type}`;
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>