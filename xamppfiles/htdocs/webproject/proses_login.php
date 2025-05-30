<?php
session_start();

$role = $_POST['role'];
$user = $_POST['username'];
$pass = $_POST['password'];

// Contoh login statis (nanti ganti pakai database)
if ($role == 'admin' && $user == 'admin' && $pass == 'admin123') {
    $_SESSION['login'] = true;
    $_SESSION['role'] = 'admin';
    header("Location: views/dashboard_admin.php");
} elseif ($role == 'guru' && $user == 'guru' && $pass == 'guru123') {
    $_SESSION['login'] = true;
    $_SESSION['role'] = 'guru';
    header("Location: views/dashboard_guru.php");
} else {
    echo "<script>alert('Login gagal!'); window.location='login.php';</script>";
}
