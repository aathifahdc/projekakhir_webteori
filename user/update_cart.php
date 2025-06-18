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
    $quantity = $_POST['quantity'];
    
    // Verify cart item belongs to user and get product stock
    $query = "SELECT c.*, p.stock FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.id = ? AND c.user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit();
    }
    
    if ($quantity > $cart_item['stock']) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
        exit();
    }
    
    $update_query = "UPDATE cart SET quantity = ? WHERE id = ?";
    $update_stmt = $db->prepare($update_query);
    $success = $update_stmt->execute([$quantity, $cart_id]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating cart']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
