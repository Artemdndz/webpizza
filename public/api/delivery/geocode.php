<?php
require_once __DIR__ . '/../../../src/config.php';
require_once __DIR__ . '/../../../src/DeliveryCalculator.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Метод не підтримується');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['address'])) {
        throw new Exception('Будь ласка, введіть адресу');
    }
    
    $calculator = new DeliveryCalculator();
    $result = $calculator->geocodeAddress($input['address']);
    
    if (!$result) {
        throw new Exception('Адресу не знайдено або доставка недоступна');
    }
    
    // Проверяем, что адрес в Днепре
    if (stripos($result['formatted_address'], 'дніпро') === false && 
        stripos($result['formatted_address'], 'dnipro') === false) {
        throw new Exception('Доставка доступна тільки у Дніпрі');
    }
    
    // Рассчитываем расстояние
    $distance = $calculator->calculateDistance(
        RESTAURANT_LAT, RESTAURANT_LNG,
        $result['lat'], $result['lng']
    );
    
    if ($distance > MAX_DELIVERY_DISTANCE) {
        throw new Exception('Доставка недоступна для вашого адресу (занадто далеко)');
    }
    
    echo json_encode([
        'success' => true,
        'address' => $result['formatted_address'],
        'coordinates' => [
            'lat' => $result['lat'],
            'lng' => $result['lng']
        ],
        'distance' => $distance,
        'zone' => $calculator->getDeliveryZone($distance)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}