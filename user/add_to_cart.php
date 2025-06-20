<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $product_id = $data['product_id'] ?? null;
    $quantity = $data['quantity'] ?? 1;

    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'ID produk tidak valid']);
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();
    $user_id = $_SESSION['user_id'];
    
    // Check if product exists and has stock
    $product_query = "SELECT stock FROM products WHERE id = ? AND status = 'active'";
    $product_stmt = $db->prepare($product_query);
    $product_stmt->execute([$product_id]);
    $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
        exit();
    }
    
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
        exit();
    }
    
    // Check if item already in cart
    $cart_query = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $cart_stmt = $db->prepare($cart_query);
    $cart_stmt->execute([$user_id, $product_id]);
    $cart_item = $cart_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cart_item) {
        // Update quantity
        $new_quantity = $cart_item['quantity'] + $quantity;
        if ($new_quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
            exit();
        }
        
        $update_query = "UPDATE cart SET quantity = ? WHERE id = ?";
        $update_stmt = $db->prepare($update_query);
        $success = $update_stmt->execute([$new_quantity, $cart_item['id']]);
    } else {
        // Add new item
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $insert_stmt = $db->prepare($insert_query);
        $success = $insert_stmt->execute([$user_id, $product_id, $quantity]);
    }

    // Get updated cart count
    $count_stmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $count_stmt->execute([$user_id]);
    $total_cart = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang',
            'cart_count' => $total_cart
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error menambahkan ke keranjang']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}