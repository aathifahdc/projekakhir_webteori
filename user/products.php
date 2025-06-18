<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'";
$params = [];

if ($search) {
    $query .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}

if ($category) {
    $query .= " AND p.category_id = ?";
    $params[] = $category;
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$cat_query = "SELECT * FROM categories ORDER BY name";
$cat_stmt = $db->prepare($cat_query);
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Produk - FreshMart</title>
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
            <a href="products.php" class="active">
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
            <h1 class="text-3xl font-bold text-gray-900">Semua Produk</h1>
            <p class="text-gray-600">Temukan produk yang Anda butuhkan</p>
        </div>
        
        <!-- Search and Filter -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <form method="GET" class="flex gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Cari produk..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-button hover:bg-secondary transition-colors">
                    <i class="ri-search-line mr-2"></i>Cari
                </button>
            </form>
        </div>
        
        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="h-48 bg-gray-200 rounded-lg mb-4 flex items-center justify-center">
                    <?php if ($product['image']): ?>
                    <img src="../assets/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="h-full w-full object-cover rounded-lg">
                    <?php else: ?>
                    <i class="ri-image-line text-4xl text-gray-400"></i>
                    <?php endif; ?>
                </div>
                
                <div class="mb-2">
                    <span class="text-xs bg-orangeLight text-primary px-2 py-1 rounded-full">
                        <?php echo $product['category_name']; ?>
                    </span>
                </div>
                
                <h3 class="text-lg font-semibold mb-2"><?php echo $product['name']; ?></h3>
                <p class="text-gray-600 text-sm mb-3"><?php echo substr($product['description'], 0, 100); ?>...</p>
                
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xl font-bold text-primary">Rp <?php echo number_format($product['price']); ?></span>
                    <span class="text-sm text-gray-500">Stok: <?php echo $product['stock']; ?></span>
                </div>
                
                <div class="flex gap-2">
                    <input type="number" min="1" max="<?php echo $product['stock']; ?>" value="1" 
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg" 
                           id="qty-<?php echo $product['id']; ?>">
                    <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                            class="bg-primary text-white px-4 py-2 rounded-button hover:bg-secondary transition-colors">
                        <i class="ri-shopping-cart-line"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($products)): ?>
        <div class="text-center py-12">
            <i class="ri-search-line text-6xl text-gray-400 mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Produk tidak ditemukan</h2>
            <p class="text-gray-600">Coba ubah kata kunci pencarian atau filter kategori</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function addToCart(productId) {
            const quantity = document.getElementById(`qty-${productId}`).value;
            
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Produk berhasil ditambahkan ke keranjang!');
                    location.reload();
                } else {
                    alert(data.message || 'Error menambahkan produk ke keranjang');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }
    </script>
</body>
</html>
