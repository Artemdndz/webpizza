<?php
require_once 'Database.php';

class Cart {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    
    // Добавить товар в корзину
    public function add($product_id, $product_name, $product_price, $quantity = 1, $comment = '') {
        $product_id = (int)$product_id;
        $quantity = max(1, (int)$quantity);
        
        if ($product_id <= 0) {
            return false;
        }
        
        // Получаем товар из базы для проверки
        $product = $this->db->getProduct($product_id);
        if (!$product) {
            return false;
        }
        
        // Если товар уже есть в корзине, увеличиваем количество
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            $_SESSION['cart'][$product_id]['comment'] = $comment;
        } else {
            // Добавляем новый товар
            $_SESSION['cart'][$product_id] = [
                'id' => $product_id,
                'name' => $product_name,
                'price' => (float)$product_price,
                'quantity' => $quantity,
                'comment' => $comment,
                'image' => $product['photo'] ?? null
            ];
        }
        
        return true;
    }
    
    // Обновить количество товара
public function update($product_id, $quantity) {
    $product_id = (int)$product_id;
    $quantity = max(0, (int)$quantity);
    
    if ($quantity <= 0) {
        return $this->remove($product_id);
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        return true;
    }
    
    return false;
}
    
    // Удалить товар из корзины
    public function remove($product_id) {
        $product_id = (int)$product_id;
        
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            return true;
        }
        
        return false;
    }
    
    // Очистить корзину
    public function clear() {
        $_SESSION['cart'] = [];
        return true;
    }
    
    // Получить содержимое корзины
    public function getItems() {
        return $_SESSION['cart'] ?? [];
    }
    
    // Получить общее количество товаров
    public function getTotalItems() {
        $total = 0;
        foreach ($_SESSION['cart'] ?? [] as $item) {
            $total += $item['quantity'];
        }
        return $total;
    }
    
    // Получить общую сумму
    public function getTotalPrice() {
        $total = 0;
        foreach ($_SESSION['cart'] ?? [] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
    
    // Получить количество одного товара
    public function getItemQuantity($product_id) {
        return $_SESSION['cart'][$product_id]['quantity'] ?? 0;
    }
    
    // Проверить, пуста ли корзина
    public function isEmpty() {
        return empty($_SESSION['cart']);
    }
}
?>