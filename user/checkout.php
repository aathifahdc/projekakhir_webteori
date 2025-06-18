<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get cart items
$query = "SELECT c.*, p.name, p.price, p.stock 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Get user info
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $db->prepare($user_query);
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = $_POST['shipping_address'];
    $phone = $_POST['phone'];
    
    try {
        $db->beginTransaction();
        
        // Create order
        $order_query = "INSERT INTO orders (user_id, total_amount, shipping_address, phone) VALUES (?, ?, ?, ?)";
        $order_stmt = $db->prepare($order_query);
        $order_stmt->execute([$_SESSION['user_id'], $total, $shipping_address, $phone]);
        $order_id = $db->lastInsertId();
        
        // Add order items and update stock
        foreach ($cart_items as $item) {
            // Add to order_items
            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $item_stmt = $db->prepare($item_query);
            $item_stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            
            // Update product stock
            $stock_query = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stock_stmt = $db->prepare($stock_query);
            $stock_stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Clear cart
        $clear_query = "DELETE FROM cart WHERE user_id = ?";
        $clear_stmt = $db->prepare($clear_query);
        $clear_stmt->execute([$_SESSION['user_id']]);
        
        $db->commit();
        
        // Redirect to success page
        header('Location: order_success.php?order_id=' . $order_id);
        exit();
        
    } catch (Exception $e) {
        $db->rollback();
        $error = "Terjadi kesalahan saat memproses pesanan";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Marie Pet Shop</title>
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
        }
        .logo-text { font-family: 'Pacifico', serif; }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="logo-text text-2xl text-primary">Marie Pet Shop</h1>
                </div>
                
                <nav class="hidden md:flex space-x-8">
                    <a href="dashboard.php" class="text-gray-700 hover:text-primary">Home</a>
                    <a href="products.php" class="text-gray-700 hover:text-primary">Products</a>
                    <a href="orders.php" class="text-gray-700 hover:text-primary">My Orders</a>
                </nav>
                
                <div class="flex items-center space-x-4">
                    <a href="cart.php" class="text-gray-700 hover:text-primary">
                        <i class="ri-shopping-cart-line text-xl"></i>
                        Cart
                    </a>
                    <span class="text-gray-700"><?php echo $_SESSION['full_name']; ?></span>
                    <a href="../auth/logout.php" class="text-gray-700 hover:text-primary">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Checkout Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>
        
        <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Shipping Information -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Informasi Pengiriman</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" id="full_name" value="<?php echo $user['full_name']; ?>" readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                            <input type="text" name="phone" id="phone" value="<?php echo $user['phone']; ?>" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">Alamat Pengiriman</label>
                            <textarea name="shipping_address" id="shipping_address" rows="4" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"><?php echo $user['address']; ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Metode Pembayaran</h2>
                    
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="radio" name="payment_method" value="cod" checked class="text-primary">
                            <span class="ml-2">Cash on Delivery (COD)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="payment_method" value="transfer" class="text-primary">
                            <span class="ml-2">Transfer Bank</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Ringkasan Pesanan</h2>
                    
                    <div class="space-y-3 mb-4">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="font-medium"><?php echo $item['name']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $item['quantity']; ?>x Rp <?php echo number_format($item['price']); ?></div>
                            </div>
                            <div class="font-semibold">
                                Rp <?php echo number_format($item['price'] * $item['quantity']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="border-t pt-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-semibold">Rp <?php echo number_format($total); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ongkos Kirim</span>
                            <span class="font-semibold">Gratis</span>
                        </div>
                        <div class="border-t pt-2">
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold">Total</span>
                                <span class="text-lg font-semibold text-primary">Rp <?php echo number_format($total); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-primary text-white py-3 rounded-lg hover:bg-secondary transition duration-300 font-semibold mt-6">
                        Buat Pesanan
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
