<?php
require_once __DIR__ . '/../../../src/config.php';
require_once __DIR__ . '/../../../src/Cart.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$cart = new Cart();
$deliveryInfo = $cart->getDeliveryInfo();

// Пересчитываем доставку с актуальными данными
$cart->calculateDelivery();
$deliveryInfo = $cart->getDeliveryInfo();

echo json_encode([
    'success' => true,
    'delivery_info' => $deliveryInfo
]);
?>