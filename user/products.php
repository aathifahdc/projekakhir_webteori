<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

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

// Kategori
$cat_stmt = $db->prepare("SELECT * FROM categories ORDER BY name");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Cart count
$cart_stmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
$cart_stmt->execute([$_SESSION['user_id']]);
$cart_count = $cart_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Produk - Marie Pet Shop</title>
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
        }
        .logo-text { font-family: 'Pacifico', serif; }
        .sidebar {
            flex-shrink: 0;
            width: 256px;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding-top: 20px;
        }
        .sidebar nav a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #1f2937;
            font-weight: 500;
            border-radius: 8px;
            margin: 6px 12px;
            text-decoration: none;
            transition: 0.3s;
            position: relative;
        }
        .sidebar nav a:hover, .sidebar nav a.active {
            background-color: #FFD8CC;
            color: #FFA07A;
        }
        .cart-badge {
            background-color: #FF7F50;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.75rem;
            position: absolute;
            top: -6px;
            right: -10px;
            min-width: 20px;
            text-align: center;
        }
        .content {
            flex-grow: 1;
            padding: 2rem;
            background-color: #fcfcfc;
        }
        .product-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: 0.2s;
        }
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
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
        .toast.show { transform: translateX(0); }
        .toast.error { background: #EF4444; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="logo-container px-4 mb-4">
        <h1 class="logo-text text-2xl text-primary">Marie Pet Shop</h1>
        <p class="text-sm text-gray-600">Happy Shopping!</p>
    </div>
    <nav>
        <a href="dashboard.php"><i class="ri-store-line mr-2"></i> Beranda</a>
        <a href="products.php" class="active"><i class="ri-shopping-bag-line mr-2"></i> Produk</a>
        <a href="cart.php">
            <i class="ri-shopping-cart-line mr-2"></i> Keranjang
            <?php if ($cart_count > 0): ?>
            <span class="cart-badge"><?= $cart_count ?></span>
            <?php endif; ?>
        </a>
        <a href="orders.php"><i class="ri-file-list-line mr-2"></i> Pesanan Saya</a>
        <a href="profile.php"><i class="ri-user-line mr-2"></i> Profil</a>
    </nav>
    <div class="logout-section mt-auto px-4 py-4 border-t border-gray-200">
        <a href="../auth/logout.php"><i class="ri-logout-box-line mr-2"></i> Logout</a>
    </div>
</div>

<!-- Content -->
<div class="content">
    <h1 class="text-3xl font-bold mb-6">Semua Produk</h1>

    <!-- Filter Form -->
    <form method="GET" class="flex gap-4 bg-white p-4 rounded-lg shadow mb-6">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari produk..." class="flex-1 border px-4 py-2 rounded-lg">
        <select name="category" class="border px-4 py-2 rounded-lg">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-primary text-white px-4 py-2 rounded-button hover:bg-secondary">
            <i class="ri-search-line"></i>
        </button>
    </form>

    <!-- Produk Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($products as $product): ?>
        <div class="product-card">
            <div class="h-48 mb-4 overflow-hidden rounded-lg">
                <img src="../assets/<?= $product['image'] ?>" alt="<?= $product['name'] ?>" class="w-full h-full object-cover">
            </div>
            <span class="text-xs bg-orangeLight text-primary px-2 py-1 rounded-full"><?= $product['category_name'] ?></span>
            <h3 class="text-lg font-semibold mt-2"><?= $product['name'] ?></h3>
            <p class="text-sm text-gray-600 mb-2"><?= substr($product['description'], 0, 100) ?>...</p>
            <div class="flex justify-between items-center mb-3">
                <span class="text-xl font-bold text-primary">Rp <?= number_format($product['price']) ?></span>
                <span class="text-sm text-gray-500">Stok: <?= $product['stock'] ?></span>
            </div>
            <div class="flex gap-2">
                <input type="number" min="1" max="<?= $product['stock'] ?>" value="1" id="qty-<?= $product['id'] ?>" class="w-16 border px-2 py-1 rounded-lg">
                <button onclick="addToCart(<?= $product['id'] ?>)" class="bg-primary text-white px-4 py-2 rounded-button hover:bg-secondary">
                    <i class="ri-shopping-cart-line"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($products)): ?>
    <div class="text-center py-12 text-gray-500">Produk tidak ditemukan.</div>
    <?php endif; ?>
</div>

<!-- Toast -->
<div id="toast" class="toast hidden">
    <span id="toast-message"></span>
</div>

<script>
function addToCart(productId) {
    const qty = document.getElementById(`qty-${productId}`).value;

    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId, quantity: qty })
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) updateCartBadge();
    })
    .catch(() => showToast('Terjadi kesalahan.', 'error'));
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-message');
    toastMsg.textContent = message;
    toast.classList.remove('hidden', 'error');
    toast.classList.add('show');
    if (type === 'error') toast.classList.add('error');
    setTimeout(() => { toast.classList.remove('show'); }, 3000);
}

function updateCartBadge() {
    fetch('get_cart_count.php')
    .then(res => res.json())
    .then(data => {
        const badge = document.querySelector('.cart-badge');
        const cartLink = document.querySelector('a[href="cart.php"]');
        if (data.total > 0) {
            if (!badge) {
                const span = document.createElement('span');
                span.className = 'cart-badge';
                span.textContent = data.total;
                cartLink.appendChild(span);
            } else {
                badge.textContent = data.total;
            }
        } else if (badge) {
            badge.remove();
        }
    });
}

window.onload = updateCartBadge;
</script>
</body>
</html>