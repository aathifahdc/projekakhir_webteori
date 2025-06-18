<?php
session_start();

// Redirect based on user role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
} else {
    header('Location: auth/login.php');
    exit();
}
?>
