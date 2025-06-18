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
                $query = "INSERT INTO categories (name, description) VALUES (?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$_POST['name'], $_POST['description']]);
                $success = "Kategori berhasil ditambahkan";
                break;
                
            case 'edit':
                $query = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$_POST['name'], $_POST['description'], $_POST['id']]);
                $success = "Kategori berhasil diupdate";
                break;
                
            case 'delete':
                // Check if category has products
                $check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->execute([$_POST['id']]);
                $product_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                if ($product_count > 0) {
                    $error = "Tidak dapat menghapus kategori yang masih memiliki produk";
                } else {
                    $query = "DELETE FROM categories WHERE id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$_POST['id']]);
                    $success = "Kategori berhasil dihapus";
                }
                break;
        }
    }
}

// Get categories with product count
$query = "SELECT c.*, COUNT(p.id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id 
          ORDER BY c.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Marie Pet Shop</title>
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
        }
        .modal-content input, .modal-content textarea {
            border: 1px solid #d1d5db;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            transition: border-color 0.2s ease-in-out;
            width: 100%;
        }
        .modal-content input:focus, .modal-content textarea:focus {
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
            <a href="categories.php" class="active">
                <i class="ri-list-check"></i>
                Kategori
            </a>
            <a href="orders.php">
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
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Manajemen Kategori</h1>
                <p class="text-gray-600">Kelola kategori produk</p>
            </div>
            <button onclick="openAddModal()" class="bg-primary text-white px-6 py-3 rounded-button hover:bg-secondary transition-colors">
                <i class="ri-add-line mr-2"></i>
                Tambah Kategori
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
        
        <!-- Categories Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Produk</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo $category['id']; ?></td>
                        <td>
                            <div class="font-semibold"><?php echo $category['name']; ?></div>
                        </td>
                        <td>
                            <div class="text-sm text-gray-600">
                                <?php echo $category['description'] ? substr($category['description'], 0, 50) . '...' : '-'; ?>
                            </div>
                        </td>
                        <td>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                <?php echo $category['product_count']; ?> produk
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></td>
                        <td>
                            <button onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)" 
                                    class="bg-blue-500 text-white px-3 py-1 rounded mr-2 hover:bg-blue-600">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button onclick="deleteCategory(<?php echo $category['id']; ?>, <?php echo $category['product_count']; ?>)" 
                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold" id="modalTitle">Tambah Kategori</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            
            <form id="categoryForm" method="POST" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="categoryId">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                    <input type="text" name="name" id="categoryName" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="categoryDescription" rows="3" placeholder="Deskripsi kategori (opsional)"></textarea>
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
            document.getElementById('modalTitle').textContent = 'Tambah Kategori';
            document.getElementById('formAction').value = 'add';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryModal').classList.add('show');
        }
        
        function editCategory(category) {
            document.getElementById('modalTitle').textContent = 'Edit Kategori';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description || '';
            document.getElementById('categoryModal').classList.add('show');
        }
        
        function deleteCategory(id, productCount) {
            if (productCount > 0) {
                alert('Tidak dapat menghapus kategori yang masih memiliki produk!');
                return;
            }
            
            if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
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
            document.getElementById('categoryModal').classList.remove('show');
        }
    </script>
</body>
</html>
