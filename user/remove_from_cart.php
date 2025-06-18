<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $cart_id = $_POST['cart_id'];
    
    // Verify cart item belongs to user
    $query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    $success = $stmt->execute([$cart_id, $_SESSION['user_id']]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error removing item']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
