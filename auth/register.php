<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // Check if username or email already exists
    $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$username, $email]);
    
    if ($check_stmt->rowCount() > 0) {
        $error = "Username atau email sudah digunakan";
    } else {
        $query = "INSERT INTO users (username, email, password, full_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$username, $email, $password, $full_name, $phone, $address])) {
            $success = "Registrasi berhasil! Silakan login.";
        } else {
            $error = "Registrasi gagal. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Marie Pet Shop</title>
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
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #FFF5F7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .logo-text { font-family: 'Pacifico', serif; }
        .register-card {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            width: 90%;
            max-width: 28rem;
        }
        .register-card input, .register-card textarea {
            border: 1px solid #d1d5db;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            transition: border-color 0.2s ease-in-out;
            width: 100%;
        }
        .register-card input:focus, .register-card textarea:focus {
            outline: none;
            border-color: #FFA07A;
            box-shadow: 0 0 0 1px #FFA07A;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="text-center mb-6">
            <h1 class="logo-text text-4xl text-primary mb-2">Marie Pet Shop</h1>
            <h2 class="text-2xl font-bold text-gray-900">Daftar Akun Baru</h2>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="full_name" id="full_name" required>
            </div>
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" id="username" required>
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                <input type="text" name="phone" id="phone">
            </div>
            
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea name="address" id="address" rows="3"></textarea>
            </div>
            
            <button type="submit" class="w-full bg-primary text-white py-3 rounded-button font-semibold hover:bg-secondary transition-colors">
                Daftar
            </button>
            
            <div class="text-center">
                <a href="login.php" class="text-primary hover:text-secondary">Sudah punya akun? Login di sini</a>
            </div>
        </form>
    </div>
</body>
</html>
