<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['total' => 0]);
    exit;
}

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
echo json_encode(['total' => (int)$total]);