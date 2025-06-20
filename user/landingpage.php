<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Marie Cat Shop - Landing</title>
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
    
    <style>
  .logo-text {
    font-family: 'Pacifico', cursive;
  }
    </style>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
</head>
<body class="bg-pink-50 min-h-screen font-sans">

  <!-- Header -->
  <header class="flex justify-between items-center p-6 bg-white shadow-md">
    <h1 class="logo-text text-2xl md:text-3xl text-primary">Marie Pet Shop</h1>
    <div class="flex items-center gap-4">
      <button onclick="showLoginModal()" class="relative text-xl">
        <i class="ri-shopping-cart-line"></i>
      </button>
      <a href="../auth/login.php" class="text-xl text-primary hover:underline">
        <i class="ri-user-line"></i>
      </a>
    </div>
  </header>

  <!-- Main Content -->
  <main class="p-6 max-w-6xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Produk Unggulan</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Contoh Produk 1 -->
      <div class="bg-white p-4 rounded-xl shadow product-card">
        <img src="../assets/catfood.jpg" alt="Makanan Kucing" class="rounded-lg h-48 w-full object-cover mb-3">
        <h3 class="font-semibold text-lg">Makanan Kucing Premium</h3>
        <p class="text-gray-500 text-sm mb-2">Gizi lengkap dan lezat untuk kucing Anda</p>
        <div class="flex justify-between items-center text-sm mb-3">
          <span class="font-bold text-primary">Rp 35.000</span>
          <span class="text-gray-500">Stok: 40</span>
        </div>
        <button onclick="showLoginModal()" class="w-full bg-primary hover:bg-secondary text-white py-2 rounded-button">
          <i class="ri-shopping-cart-line mr-2"></i> Tambah ke Keranjang
        </button>
      </div>

      <!-- Contoh Produk 2 -->
      <div class="bg-white p-4 rounded-xl shadow product-card">
        <img src="../assets/kalungkucing.jpg" alt="Kalung Kucing" class="rounded-lg h-48 w-full object-cover mb-3">
        <h3 class="font-semibold text-lg">Kalung Kucing Lucu</h3>
        <p class="text-gray-500 text-sm mb-2">Aksesoris lucu untuk kucing kesayangan</p>
        <div class="flex justify-between items-center text-sm mb-3">
          <span class="font-bold text-primary">Rp 20.000</span>
          <span class="text-gray-500">Stok: 25</span>
        </div>
        <button onclick="showLoginModal()" class="w-full bg-primary hover:bg-secondary text-white py-2 rounded-button">
          <i class="ri-shopping-cart-line mr-2"></i> Tambah ke Keranjang
        </button>
      </div>
   <!-- Sisir Hewan -->
                <div class="bg-white p-4 rounded-xl shadow product-card">
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
       <div class="bg-white p-4 rounded-xl shadow product-card">
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
               <div class="bg-white p-4 rounded-xl shadow product-card">
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
    </div>
  </main>

  <!-- Login Modal -->
  <div id="loginModal" class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center hidden z-50">
    <div class="bg-white p-6 rounded-xl max-w-sm w-full text-center shadow-lg">
      <h3 class="text-xl font-bold text-primary mb-3">Oops!</h3>
      <p class="mb-4 text-gray-600">Silakan login terlebih dahulu untuk menggunakan fitur keranjang.</p>
      <a href="../auth/login.php" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-button inline-block">Login Sekarang</a>
    </div>
  </div>

  <script>
    function showLoginModal() {
      document.getElementById('loginModal').classList.remove('hidden');
    }
  </script>

</body>
</html>