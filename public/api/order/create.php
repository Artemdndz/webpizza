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
    if (empty($input['customer_name']) || empty($input['phone'])) {
        throw new Exception('Будь ласка, заповніть обов\'язкові поля');
    }
    
    $db = Database::getInstance();
    $cart = new Cart();
    $cart_items = $cart->getItems();
    
    if (empty($cart_items)) {
        throw new Exception('Кошик порожній');
    }
    
    // Рассчитываем итоговую сумму
    $items_total = $cart->getTotalPrice();
    $delivery_cost = ($items_total >= 400) ? 0 : 50;
    $discount = 0;
    
    // Применяем скидку для самовывоза
    if ($input['type'] === 'pickup') {
        $discount = $items_total * PICKUP_DISCOUNT / 100;
    }
    
    $total = $items_total + $delivery_cost - $discount;
    
    // Формируем детали доставки
    $delivery_details = '';
    if ($input['type'] === 'delivery' && !empty($input['delivery_details'])) {
        $details = [];
        if (!empty($input['delivery_details']['entrance'])) {
            $details[] = 'Під\'їзд: ' . $input['delivery_details']['entrance'];
        }
        if (!empty($input['delivery_details']['floor'])) {
            $details[] = 'Поверх: ' . $input['delivery_details']['floor'];
        }
        if (!empty($input['delivery_details']['intercom'])) {
            $details[] = 'Домофон: ' . $input['delivery_details']['intercom'];
        }
        $delivery_details = implode(', ', $details);
    }
    
    // Формируем информацию о времени
    $delivery_time_info = '';
    if ($input['delivery_time'] === 'scheduled' && !empty($input['delivery_schedule'])) {
        $schedule = $input['delivery_schedule'];
        $delivery_time_info = "Заплановано на: {$schedule['date']} {$schedule['time']}";
    } else {
        $delivery_time_info = 'Як можна швидше';
    }
    
    // Объединяем все комментарии
    $full_comment = trim($input['comment'] ?? '');
    if ($delivery_details) {
        $full_comment .= ($full_comment ? "\n" : '') . "Деталі доставки: " . $delivery_details;
    }
    if ($delivery_time_info) {
        $full_comment .= ($full_comment ? "\n" : '') . "Час отримання: " . $delivery_time_info;
    }
    
    // Создаем заказ в базе данных
    $order_id = $db->createOrder([
        'customer_name' => $input['customer_name'],
        'phone' => $input['phone'],
        'email' => $input['email'] ?? '',
        'type' => $input['type'],
        'address' => $input['address'] ?? '',
        'total' => $total,
        'discount' => $discount,
        'status' => 'new',
        'payment_method' => $input['payment_method'] ?? 'cash',
        'payment_status' => 'pending',
        'comment' => $full_comment,
        'preparation_time' => $input['delivery_time'] === 'scheduled' ? null : 'ASAP',
        'scheduled_time' => $input['delivery_time'] === 'scheduled' ? 
            ($input['delivery_schedule']['date'] . ' ' . $input['delivery_schedule']['time']) : null,
        'source' => 'website'
    ]);
    
    // Добавляем товары в заказ
    foreach ($cart_items as $item) {
        $db->addOrderItem($order_id, [
            'product_id' => $item['id'],
            'product_name' => $item['name'],
            'product_price' => $item['price'],
            'quantity' => $item['quantity'],
            'comment' => $item['comment'] ?? ''
        ]);
    }
    
    // Регистрируем/обновляем клиента
    $customer_id = $db->saveCustomer(
        $input['phone'],
        $input['customer_name'],
        $input['email'] ?? '',
        $input['address'] ?? ''
    );
    
    if ($customer_id) {
        $db->updateCustomerStats($customer_id, $total);
    }
    
    // Очищаем корзину
    $cart->clear();
    
    // Отправляем уведомление (можно добавить позже)
    // sendNotification($order_id, $input);
    
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Замовлення успішно створено! Наш менеджер зв\'яжеться з вами для підтвердження.'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>