<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Marie Pet Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FFA07A', // Oranye utama
                        secondary: '#FF8C61', // Oranye sekunder
                        orangeLight: '#FFD8CC', // Oranye terang
                        orangeDark: '#FF7F50' // Oranye gelap
                    },
                    borderRadius: {
                        'none': '0px',
                        'sm': '4px',
                        DEFAULT: '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '20px',
                        '2xl': '24px',
                        '3xl': '32px',
                        'full': '9999px',
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
        /* Styling sesuai dengan yang diminta */
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

        /* Sidebar - konsisten dengan dashboard admin yang memanjang */
        .sidebar {
            flex-shrink: 0;
            width: 256px; /* width-64 */
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
            color: #1f2937; /* gray-800 */
            font-weight: 500;
            border-radius: 8px;
            margin: 6px 12px;
            text-decoration: none;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #FFD8CC; /* orangeLight */
            color: #FFA07A; /* primary */
        }
        .sidebar a i {
            margin-right: 12px;
            font-size: 1.3rem;
        }
        .sidebar .logout-section {
            margin-top: auto;
            padding: 1rem;
            border-top: 1px solid #e5e7eb; /* gray-200 */
        }

        .content {
            flex-grow: 1;
            padding: 2rem;
            background-color: #fcfcfc;
        }

        /* Tabel */
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
            background-color: #FFA07A; /* primary - Header tabel oranye */
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
            background-color: #FFD8CC; /* orangeLight - Hover baris oranye terang */
        }

        /* Modal */
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
        .modal.show {
            display: flex;
        }
        .modal-content {
            background-color: white;
            border-radius: 20px; /* xl */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            width: 90%;
            max-width: 28rem;
        }
        .modal-content input, .modal-content select, .modal-content textarea {
            border: 1px solid #d1d5db; /* gray-300 */
            padding: 0.75rem 1rem;
            border-radius: 12px; /* md */
            transition: border-color 0.2s ease-in-out;
            width: 100%;
        }
        .modal-content input:focus, .modal-content select:focus, .modal-content textarea:focus {
            outline: none;
            border-color: #FFA07A; /* primary */
            box-shadow: 0 0 0 1px #FFA07A;
        }

        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #FFA07A;
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
            <a href="dashboard.php" class="active">
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
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600">Selamat datang di panel admin!</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Produk</p>
                        <p class="text-2xl font-bold text-gray-800">150</p>
                    </div>
                    <i class="ri-shopping-bag-line text-3xl text-primary"></i>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Pesanan</p>
                        <p class="text-2xl font-bold text-gray-800">89</p>
                    </div>
                    <i class="ri-file-list-line text-3xl text-primary"></i>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Users</p>
                        <p class="text-2xl font-bold text-gray-800">245</p>
                    </div>
                    <i class="ri-user-line text-3xl text-primary"></i>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Pendapatan</p>
                        <p class="text-2xl font-bold text-gray-800">Rp 15.5M</p>
                    </div>
                    <i class="ri-money-dollar-circle-line text-3xl text-primary"></i>
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold text-gray-900">Pesanan Terbaru</h2>
            </div>
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
                    <tr>
                        <td>#001</td>
                        <td>John Doe</td>
                        <td>Rp 250.000</td>
                        <td><span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pending</span></td>
                        <td>2024-01-15</td>
                        <td>
                            <button class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                <i class="ri-eye-line"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#002</td>
                        <td>Jane Smith</td>
                        <td>Rp 180.000</td>
                        <td><span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Delivered</span></td>
                        <td>2024-01-14</td>
                        <td>
                            <button class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                <i class="ri-eye-line"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sample Modal -->
    <div id="sampleModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Sample Modal</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            
            <form class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Input Field</label>
                    <input type="text" placeholder="Enter text...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Field</label>
                    <select>
                        <option>Option 1</option>
                        <option>Option 2</option>
                    </select>
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
        function closeModal() {
            document.getElementById('sampleModal').classList.remove('show');
        }
    </script>
</body>
</html>
