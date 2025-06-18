<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    $success = "Status pesanan berhasil diupdate";
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Build query
$query = "SELECT o.*, u.full_name, u.email, u.phone as user_phone 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE 1=1";
$params = [];

if ($status_filter) {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
}

if ($date_filter) {
    $query .= " AND DATE(o.created_at) = ?";
    $params[] = $date_filter;
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get order statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
    SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(total_amount) as total_revenue
    FROM orders";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - Marie Pet Shop</title>
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
            max-width: 40rem;
            max-height: 80vh;
            overflow-y: auto;
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
            <a href="orders.php" class="active">
                <i class="ri-file-list-line"></i>
                Pesanan
            </a>
            <a href="users.php">
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
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Pesanan</h1>
            <p class="text-gray-600">Kelola semua pesanan pelanggan</p>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Pesanan</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_orders']; ?></p>
                    </div>
                    <i class="ri-file-list-line text-3xl text-primary"></i>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['pending_orders']; ?></p>
                    </div>
                    <i class="ri-time-line text-3xl text-yellow-500"></i>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Terkirim</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $stats['delivered_orders']; ?></p>
                    </div>
                    <i class="ri-check-line text-3xl text-green-500"></i>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Revenue</p>
                        <p class="text-2xl font-bold text-primary">Rp <?php echo number_format($stats['total_revenue']); ?></p>
                    </div>
                    <i class="ri-money-dollar-circle-line text-3xl text-primary"></i>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <form method="GET" class="flex gap-4">
                <div>
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div>
                    <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-button hover:bg-secondary transition-colors">
                    <i class="ri-search-line mr-2"></i>Filter
                </button>
                <a href="orders.php" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-button hover:bg-gray-400 transition-colors">
                    Reset
                </a>
            </form>
        </div>
        
        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table>
                <thead>
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <div class="font-semibold">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></div>
                        </td>
                        <td>
                            <div>
                                <div class="font-semibold"><?php echo $order['full_name']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $order['email']; ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="font-semibold">Rp <?php echo number_format($order['total_amount']); ?></div>
                        </td>
                        <td>
                            <?php
                            $status_colors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'shipped' => 'bg-purple-100 text-purple-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                            ?>
                            <span class="px-2 py-1 text-xs rounded-full <?php echo $status_colors[$order['status']]; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <button onclick="viewOrder(<?php echo $order['id']; ?>)" 
                                    class="bg-blue-500 text-white px-3 py-1 rounded mr-2 hover:bg-blue-600">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button onclick="updateStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')" 
                                    class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                <i class="ri-edit-line"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
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

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Update Status Pesanan</h3>
                <button onclick="closeStatusModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="statusOrderId">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Baru</label>
                    <select name="status" id="newStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-primary text-white py-2 rounded-button hover:bg-secondary transition-colors">
                        Update Status
                    </button>
                    <button type="button" onclick="closeStatusModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-button hover:bg-gray-400 transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function viewOrder(orderId) {
            // In a real application, you would fetch order details via AJAX
            document.getElementById('orderDetails').innerHTML = `
                <div class="space-y-4">
                    <div class="text-center py-8">
                        <i class="ri-loader-line text-4xl text-gray-400 animate-spin"></i>
                        <p class="text-gray-600 mt-2">Memuat detail pesanan...</p>
                    </div>
                </div>
            `;
            document.getElementById('orderModal').classList.add('show');
            
            // Simulate loading
            setTimeout(() => {
                document.getElementById('orderDetails').innerHTML = `
                    <div class="space-y-4">
                        <div class="border-b pb-4">
                            <h4 class="font-semibold">Informasi Pesanan</h4>
                            <p>ID Pesanan: #${orderId.toString().padStart(4, '0')}</p>
                            <p>Status: <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pending</span></p>
                        </div>
                        <div class="border-b pb-4">
                            <h4 class="font-semibold">Informasi Customer</h4>
                            <p>Nama: John Doe</p>
                            <p>Email: john@example.com</p>
                            <p>Alamat: Jl. Contoh No. 123, Jakarta</p>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2">Item Pesanan</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Apel Fuji Premium x2</span>
                                    <span>Rp 50.000</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Beras Premium x1</span>
                                    <span>Rp 65.000</span>
                                </div>
                                <div class="border-t pt-2 flex justify-between font-semibold">
                                    <span>Total</span>
                                    <span>Rp 115.000</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }, 1000);
        }
        
        function updateStatus(orderId, currentStatus) {
            document.getElementById('statusOrderId').value = orderId;
            document.getElementById('newStatus').value = currentStatus;
            document.getElementById('statusModal').classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('orderModal').classList.remove('show');
        }
        
        function closeStatusModal() {
            document.getElementById('statusModal').classList.remove('show');
        }
    </script>
</body>
</html>
