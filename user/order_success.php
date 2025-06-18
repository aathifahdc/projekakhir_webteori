<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header('Location: ../auth/login.php');
    exit();
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    header('Location: dashboard.php');
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get order details
$query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - FreshMart</title>
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
                    <h1 class="logo-text text-2xl text-primary">FreshMart</h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700"><?php echo $_SESSION['full_name']; ?></span>
                    <a href="../auth/logout.php" class="text-gray-700 hover:text-primary">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Success Content -->
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="mb-6">
                <i class="ri-check-double-line text-6xl text-green-500"></i>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Pesanan Berhasil!</h1>
            <p class="text-gray-600 mb-6">Terima kasih telah berbelanja di FreshMart. Pesanan Anda sedang diproses.</p>
            
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <div class="grid grid-cols-2 gap-4 text-left">
                    <div>
                        <span class="text-gray-600">ID Pesanan:</span>
                        <div class="font-semibold">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600">Total:</span>
                        <div class="font-semibold text-primary">Rp <?php echo number_format($order['total_amount']); ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600">Status:</span>
                        <div class="font-semibold">Pending</div>
                    </div>
                    <div>
                        <span class="text-gray-600">Tanggal:</span>
                        <div class="font-semibold"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="space-y-3">
                <a href="orders.php" class="block w-full bg-primary text-white py-3 rounded-button hover:bg-secondary transition-colors font-semibold">
                    Lihat Pesanan Saya
                </a>
                <a href="products.php" class="block w-full bg-gray-200 text-gray-700 py-3 rounded-button hover:bg-gray-300 transition-colors font-semibold">
                    Lanjut Belanja
                </a>
            </div>
        </div>
    </div>
</body>
</html>
