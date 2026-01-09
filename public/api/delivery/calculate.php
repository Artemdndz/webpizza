<?php
require_once __DIR__ . '/../../../src/config.php';
require_once __DIR__ . '/../../../src/Cart.php';
require_once __DIR__ . '/../../../src/DeliveryCalculator.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Метод не підтримується');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $cart = new Cart();
    $total = $cart->getTotalPrice();
    
    if (empty($input['coordinates'])) {
        throw new Exception('Координати не вказані');
    }
    
    $lat = floatval($input['coordinates']['lat']);
    $lng = floatval($input['coordinates']['lng']);
    
    // Устанавливаем адрес
    $cart->setDeliveryAddress($input['address'] ?? '', $lat, $lng);
    $deliveryInfo = $cart->getDeliveryInfo();
    
    $calculator = new DeliveryCalculator();
    $zone = $deliveryInfo['delivery_zone'];
    
    $response = [
        'success' => true,
        'delivery_available' => $deliveryInfo['delivery_available'],
        'distance' => $deliveryInfo['delivery_distance'],
        'zone' => $zone,
        'total' => $total,
        'total_with_delivery' => $cart->getTotalWithDelivery()
    ];
    
    if ($deliveryInfo['delivery_available']) {
        $response['delivery_cost'] = $deliveryInfo['delivery_cost'];
        $response['delivery_message'] = $deliveryInfo['delivery_message'] ?? '';
        
        if ($zone === 4) {
            $response['is_taxi_zone'] = true;
            $response['discount'] = $deliveryInfo['discount'];
            $response['payment_methods'] = ['online']; // Только онлайн
        } else {
            $response['is_taxi_zone'] = false;
            $response['payment_methods'] = ['cash', 'card', 'online'];
        }
    } else {
        // Предлагаем добавить товары
        $minOrderInfo = $calculator->getMinOrderMessage($zone, $total);
        $response['suggestions'] = [
            'min_order_needed' => $minOrderInfo['min_order'],
            'need_to_add' => $minOrderInfo['needed'],
            'for_free_delivery' => $minOrderInfo['for_free']
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}