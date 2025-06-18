<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $query = "SELECT id, username, password, role, full_name FROM users WHERE username = ? OR email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$username, $username]);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            if ($user['role'] == 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../user/dashboard.php');
            }
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Marie Pet Shop</title>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Open Sans', sans-serif;
        }
        .logo-text {
            font-family: 'Pacifico', serif;
        }
        
        .login-card {
            background-color: white;
            border-radius: 20px; /* xl */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            width: 90%;
            max-width: 28rem;
        }
        
        .login-card input {
            border: 1px solid #d1d5db; /* gray-300 */
            padding: 0.75rem 1rem;
            border-radius: 12px; /* md */
            transition: border-color 0.2s ease-in-out;
            width: 100%;
        }
        
        .login-card input:focus {
            outline: none;
            border-color: #FFA07A; /* primary */
            box-shadow: 0 0 0 1px #FFA07A;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-8">
            <h1 class="logo-text text-4xl text-primary mb-2">Marie Pet Shop</h1>
            <h2 class="text-2xl font-bold text-gray-900">Masuk ke akun anda</h2>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                <input type="text" name="username" id="username" required>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <button type="submit" 
                    class="w-full bg-primary text-white py-3 rounded-button font-semibold hover:bg-secondary transition-colors">
                Sign in
            </button>
            
            <div class="text-center">
                <a href="register.php" class="text-primary hover:text-secondary">Belum punya akun? Buat disini.</a>
            </div>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-600">
            <p>Demo Credentials:</p>
            <p><strong>Admin:</strong> admin / admin123</p>
        </div>
    </div>
</body>
</html>
