<?php
require_once __DIR__ . '/../../../src/config.php';
require_once __DIR__ . '/../../../src/Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('Не вказаний ID товару');
    }
    
    $productId = intval($_GET['id']);
    
    // Получаем товар с категорией
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?";
    
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception('Товар не знайдено');
    }
    
    // Обрабатываем фото
    if (empty($product['photo'])) {
        $product['photo'] = '/images/placeholder.jpg';
    }
    
    echo json_encode([
        'success' => true,
        'product' => $product
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}