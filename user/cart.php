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
$query = "SELECT c.*, p.name, p.price, p.image, p.stock 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Marie Pet Shop</title>
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
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        body { font-family: 'Open Sans', sans-serif; }
        .logo-text { font-family: 'Pacifico', serif; }
    </style>
</head>
<body class="bg-gray-50">
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
                    <a href="cart.php" class="text-primary font-medium">
                        <i class="ri-shopping-cart-line text-xl"></i>
                        Cart
                    </a>
                    <span class="text-gray-700"><?php echo $_SESSION['full_name']; ?></span>
                    <a href="../auth/logout.php" class="text-gray-700 hover:text-primary">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Cart Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>
        
        <?php if (empty($cart_items)): ?>
        <div class="text-center py-12">
            <i class="ri-shopping-cart-line text-6xl text-gray-400 mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
            <p class="text-gray-600 mb-6">Add some products to get started!</p>
            <a href="products.php" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary transition duration-300">
                Continue Shopping
            </a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="p-6 border-b border-gray-200 last:border-b-0">
                        <div class="flex items-center">
                            <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                                <?php if ($item['image']): ?>
                                <img src="../uploads/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="w-full h-full object-cover rounded-lg">
                                <?php else: ?>
                                <i class="ri-image-line text-2xl text-gray-400"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900"><?php echo $item['name']; ?></h3>
                                <p class="text-gray-600">$<?php echo number_format($item['price'], 2); ?> each</p>
                                <p class="text-sm text-gray-500">Stock: <?php echo $item['stock']; ?></p>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <button onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)" 
                                        class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300">
                                    <i class="ri-subtract-line"></i>
                                </button>
                                <span class="w-12 text-center"><?php echo $item['quantity']; ?></span>
                                <button onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)" 
                                        class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300">
                                    <i class="ri-add-line"></i>
                                </button>
                            </div>
                            
                            <div class="ml-4 text-right">
                                <p class="font-semibold text-gray-900">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                <button onclick="removeFromCart(<?php echo $item['id']; ?>)" 
                                        class="text-red-600 hover:text-red-800 text-sm">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-semibold">$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-semibold">Free</span>
                        </div>
                        <div class="border-t pt-2">
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold">Total</span>
                                <span class="text-lg font-semibold text-primary">$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <button onclick="proceedToCheckout()" 
                            class="w-full bg-primary text-white py-3 rounded-lg hover:bg-secondary transition duration-300 font-semibold">
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function updateQuantity(cartId, newQuantity) {
            if (newQuantity < 1) {
                removeFromCart(cartId);
                return;
            }
            
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'cart_id=' + cartId + '&quantity=' + newQuantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
        
        function removeFromCart(cartId) {
            if (confirm('Are you sure you want to remove this item?')) {
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'cart_id=' + cartId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error removing item from cart');
                    }
                });
            }
        }
        
        function proceedToCheckout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>
</html>
