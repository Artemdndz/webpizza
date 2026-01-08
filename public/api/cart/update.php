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
    
    // Проверяем действие
    if (!isset($_POST['action'])) {
        throw new Exception('Не вказана дія');
    }
    
    $cart = new Cart();
    $response = [];
    
    switch ($_POST['action']) {
        case 'update':
            if (!isset($_POST['id']) || !isset($_POST['quantity'])) {
                throw new Exception('Не всі параметри вказані');
            }
            
            $item_id = intval($_POST['id']);
            $quantity = intval($_POST['quantity']);
            
            $success = $cart->update($item_id, $quantity);
            
            // Получаем обновленные данные
            $items = $cart->getItems();
            $item_total = 0;
            
            if ($success && isset($items[$item_id])) {
                $item = $items[$item_id];
                $item_total = $item['price'] * $item['quantity'];
            }
            
            $response = [
                'success' => $success,
                'total_items' => $cart->getTotalItems(),
                'total_price' => $cart->getTotalPrice(),
                'item_total' => $item_total
            ];
            break;
            
        case 'remove':
            if (!isset($_POST['id'])) {
                throw new Exception('Не вказаний ID товару');
            }
            
            $item_id = intval($_POST['id']);
            $success = $cart->remove($item_id);
            
            $response = [
                'success' => $success,
                'total_items' => $cart->getTotalItems(),
                'total_price' => $cart->getTotalPrice()
            ];
            break;
            
        case 'clear':
            $success = $cart->clear();
            $response = [
                'success' => $success,
                'total_items' => 0,
                'total_price' => 0
            ];
            break;
            
        default:
            throw new Exception('Невідома дія');
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>