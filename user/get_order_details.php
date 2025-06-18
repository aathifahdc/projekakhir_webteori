<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

require_once '../config/database.php';

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get order details
$order_query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$order_stmt = $db->prepare($order_query);
$order_stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit();
}

// Get order items
$items_query = "SELECT oi.*, p.name 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
$items_stmt = $db->prepare($items_query);
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items
]);
?>
