<?php
require_once __DIR__ . '/../../../src/config.php';
require_once __DIR__ . '/../../../src/Database.php';
require_once __DIR__ . '/../../../src/Cart.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    // Проверяем метод запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Метод не підтримується');
    }
    
    // Получаем данные из запроса
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    // Проверяем обязательные поля
    if (!isset($input['id']) || !isset($input['name']) || !isset($input['price'])) {
        throw new Exception('Не всі обов\'язкові поля заповнені');
    }
    
    $cart = new Cart();
    
    // Добавляем товар в корзину
    $result = $cart->add(
        $input['id'],
        $input['name'],
        $input['price'],
        $input['quantity'] ?? 1,
        $input['comment'] ?? ''
    );
    
    if (!$result) {
        throw new Exception('Помилка при додаванні товару до кошика');
    }
    
    // Возвращаем успешный ответ
    echo json_encode([
        'success' => true,
        'total_items' => $cart->getTotalItems(),
        'total_price' => $cart->getTotalPrice(),
        'message' => 'Товар додано до кошика'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>