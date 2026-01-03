<?php
require_once 'config.php';

class Database {
    private $pdo;
    private static $instance = null;
    
    private function __construct() {
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Помилка підключення до бази даних: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // Получить все категории
    public function getCategories($active_only = true) {
        $sql = "SELECT * FROM categories";
        if ($active_only) {
            $sql .= " WHERE active = 1";
        }
        $sql .= " ORDER BY priority ASC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    // Получить товары
    public function getProducts($category_id = null, $popular_only = false, $limit = null) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.active = 1";
        
        $params = [];
        
        if ($category_id) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category_id;
        }
        
        if ($popular_only) {
            $sql .= " AND p.is_popular = 1";
        }
        
        $sql .= " ORDER BY p.priority ASC, p.name ASC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Получить один товар
    public function getProduct($id) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ? AND p.active = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Создать заказ
    public function createOrder($data) {
        $sql = "INSERT INTO orders (
                    customer_name, phone, email, type, address, 
                    total, discount, status, payment_method, 
                    payment_status, comment, preparation_time, source
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['customer_name'],
            $data['phone'],
            $data['email'] ?? '',
            $data['type'],
            $data['address'] ?? '',
            $data['total'],
            $data['discount'] ?? 0,
            $data['status'] ?? 'new',
            $data['payment_method'] ?? 'cash',
            $data['payment_status'] ?? 'pending',
            $data['comment'] ?? '',
            $data['preparation_time'] ?? null,
            $data['source'] ?? 'website'
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    // Добавить товар в заказ
    public function addOrderItem($order_id, $item) {
        $sql = "INSERT INTO order_items (
                    order_id, product_id, product_name, 
                    product_price, quantity, toppings, comment
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $order_id,
            $item['product_id'] ?? null,
            $item['product_name'],
            $item['product_price'],
            $item['quantity'],
            json_encode($item['toppings'] ?? [], JSON_UNESCAPED_UNICODE),
            $item['comment'] ?? ''
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    // Получить добавки для категории
    public function getToppingsForCategory($category_id) {
        $sql = "SELECT tg.*, t.id as topping_id, t.name as topping_name, 
                       t.price as topping_price, t.weight as topping_weight
                FROM topping_groups tg
                LEFT JOIN toppings t ON tg.id = t.topping_group_id
                WHERE tg.category_id = ? 
                ORDER BY tg.priority ASC, t.priority ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$category_id]);
        
        $result = $stmt->fetchAll();
        $groups = [];
        
        foreach ($result as $row) {
            $group_id = $row['id'];
            if (!isset($groups[$group_id])) {
                $groups[$group_id] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'min_selection' => $row['min_selection'],
                    'max_selection' => $row['max_selection'],
                    'toppings' => []
                ];
            }
            
            if ($row['topping_id']) {
                $groups[$group_id]['toppings'][] = [
                    'id' => $row['topping_id'],
                    'name' => $row['topping_name'],
                    'price' => $row['topping_price'],
                    'weight' => $row['topping_weight']
                ];
            }
        }
        
        return array_values($groups);
    }
    
    // Проверка существования телефона в базе
    public function findCustomerByPhone($phone) {
        $sql = "SELECT * FROM customers WHERE phone = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$phone]);
        return $stmt->fetch();
    }
    
    // Регистрация/обновление клиента
    public function saveCustomer($phone, $name = null, $email = null, $address = null) {
        $customer = $this->findCustomerByPhone($phone);
        
        if ($customer) {
            // Обновляем существующего
            $sql = "UPDATE customers SET name = ?, email = ?, address = ?, updated_at = NOW() WHERE phone = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$name, $email, $address, $phone]);
            return $customer['id'];
        } else {
            // Создаем нового
            $sql = "INSERT INTO customers (phone, name, email, address) VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$phone, $name, $email, $address]);
            return $this->pdo->lastInsertId();
        }
    }
    
    // Обновить статистику клиента
    public function updateCustomerStats($customer_id, $order_total) {
        $sql = "UPDATE customers 
                SET total_orders = total_orders + 1, 
                    total_spent = total_spent + ?, 
                    last_order_date = NOW() 
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$order_total, $customer_id]);
    }
}
?>