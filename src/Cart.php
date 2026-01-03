<?php
require_once 'Database.php';

class Cart {
    private $session_key = 'onechef_cart';
    
    public function __construct() {
        if (!isset($_SESSION[$this->session_key])) {
            $_SESSION[$this->session_key] = [
                'items' => [],
                'total' => 0,
                'count' => 0
            ];
        }
    }
    
    // Добавить товар в корзину
    public function addItem($product_id, $name, $price, $quantity = 1, $toppings = [], $comment = '') {
        $cart = $_SESSION[$this->session_key];
        
        // Проверяем, есть ли уже такой товар с такими же добавками
        $found_key = null;
        foreach ($cart['items'] as $key => $item) {
            if ($item['product_id'] == $product_id && 
                json_encode($item['toppings']) === json_encode($toppings) &&
                $item['comment'] === $comment) {
                $found_key = $key;
                break;
            }
        }
        
        if ($found_key !== null) {
            // Увеличиваем количество существующего товара
            $cart['items'][$found_key]['quantity'] += $quantity;
        } else {
            // Добавляем новый товар
            $cart['items'][] = [
                'product_id' => $product_id,
                'name' => $name,
                'price' => $price,
                'quantity' => $quantity,
                'toppings' => $toppings,
                'comment' => $comment,
                'added_at' => time()
            ];
        }
        
        $this->updateCart($cart);
        return true;
    }
    
    // Обновить количество товара
    public function updateQuantity($item_key, $quantity) {
        $cart = $_SESSION[$this->session_key];
        
        if (isset($cart['items'][$item_key])) {
            if ($quantity <= 0) {
                // Удаляем товар если количество 0 или меньше
                $this->removeItem($item_key);
                return true;
            }
            
            $cart['items'][$item_key]['quantity'] = $quantity;
            $this->updateCart($cart);
            return true;
        }
        
        return false;
    }
    
    // Удалить товар из корзины
    public function removeItem($item_key) {
        $cart = $_SESSION[$this->session_key];
        
        if (isset($cart['items'][$item_key])) {
            unset($cart['items'][$item_key]);
            $cart['items'] = array_values($cart['items']); // Переиндексация
            $this->updateCart($cart);
            return true;
        }
        
        return false;
    }
    
    // Очистить корзину
    public function clear() {
        $_SESSION[$this->session_key] = [
            'items' => [],
            'total' => 0,
            'count' => 0
        ];
    }
    
    // Получить содержимое корзины
    public function getItems() {
        return $_SESSION[$this->session_key]['items'];
    }
    
    // Получить общую сумму
    public function getTotal() {
        return $_SESSION[$this->session_key]['total'];
    }
    
    // Получить количество товаров
    public function getTotalItems() {
        return $_SESSION[$this->session_key]['count'];
    }
    
    // Получить детальную информацию о корзине
    public function getCart() {
        return $_SESSION[$this->session_key];
    }
    
    // Применить скидку (для самовывоза)
    public function applyPickupDiscount() {
        $cart = $_SESSION[$this->session_key];
        $original_total = $cart['total'];
        $discounted_total = $original_total * (1 - PICKUP_DISCOUNT / 100);
        
        $cart['discount'] = [
            'type' => 'pickup',
            'percent' => PICKUP_DISCOUNT,
            'amount' => $original_total - $discounted_total
        ];
        $cart['total'] = $discounted_total;
        
        $_SESSION[$this->session_key] = $cart;
    }
    
    // Убрать скидку
    public function removeDiscount() {
        $cart = $_SESSION[$this->session_key];
        
        if (isset($cart['discount'])) {
            $cart['total'] = $cart['total'] / (1 - $cart['discount']['percent'] / 100);
            unset($cart['discount']);
            $_SESSION[$this->session_key] = $cart;
        }
    }
    
    // Подсчитать итоги корзины
    private function updateCart(&$cart) {
        $total = 0;
        $count = 0;
        
        foreach ($cart['items'] as $item) {
            $item_total = $item['price'] * $item['quantity'];
            
            // Добавляем стоимость добавок
            if (!empty($item['toppings'])) {
                foreach ($item['toppings'] as $topping) {
                    if (isset($topping['price']) && isset($topping['quantity'])) {
                        $item_total += $topping['price'] * $topping['quantity'];
                    }
                }
            }
            
            $total += $item_total;
            $count += $item['quantity'];
        }
        
        $cart['total'] = $total;
        $cart['count'] = $count;
        
        // Если есть скидка, применяем её
        if (isset($cart['discount'])) {
            $cart['total'] = $total * (1 - $cart['discount']['percent'] / 100);
        }
    }
    
    // Создать заказ из корзины
    public function createOrder($customer_data) {
        $db = Database::getInstance();
        $cart = $this->getCart();
        
        // Рассчитываем финальную сумму с учетом типа заказа
        $total = $cart['total'];
        $discount = 0;
        
        if ($customer_data['type'] === 'pickup' && isset($cart['discount'])) {
            $discount = $cart['discount']['amount'];
        }
        
        // Подготавливаем данные заказа
        $order_data = [
            'customer_name' => $customer_data['name'],
            'phone' => $customer_data['phone'],
            'email' => $customer_data['email'] ?? '',
            'type' => $customer_data['type'],
            'address' => $customer_data['address'] ?? '',
            'total' => $total,
            'discount' => $discount,
            'status' => 'new',
            'payment_method' => $customer_data['payment_method'] ?? 'cash',
            'comment' => $customer_data['comment'] ?? '',
            'preparation_time' => $customer_data['preparation_time'] ?? null
        ];
        
        // Создаем заказ в БД
        $order_id = $db->createOrder($order_data);
        
        // Добавляем товары из корзины
        foreach ($cart['items'] as $item) {
            $db->addOrderItem($order_id, $item);
        }
        
        // Сохраняем/обновляем клиента
        $customer_id = $db->saveCustomer(
            $customer_data['phone'],
            $customer_data['name'],
            $customer_data['email'] ?? '',
            $customer_data['address'] ?? ''
        );
        
        // Обновляем статистику клиента
        $db->updateCustomerStats($customer_id, $total);
        
        // Очищаем корзину
        $this->clear();
        
        return $order_id;
    }
}
?>