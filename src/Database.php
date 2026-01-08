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
                payment_status, comment, preparation_time, 
                scheduled_time, source
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
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
        $data['scheduled_time'] ?? null,
        $data['source'] ?? 'website'
    ]);
    
    return $this->pdo->lastInsertId();
}
    
    // Добавить товар в заказ
    public function addOrderItem($order_id, $item) {
        $sql = "INSERT INTO order_items (
                    order_id, product_id, product_name, 
                    product_price, quantity, comment
                ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $order_id,
            $item['product_id'] ?? null,
            $item['product_name'],
            $item['product_price'],
            $item['quantity'],
            $item['comment'] ?? ''
        ]);
        
        return $this->pdo->lastInsertId();
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
    
    public function addProduct($data) {
        try {
            $sql = "INSERT INTO products (name, description, price, old_price, weight, prep_time, category_id, 
                    is_new, is_popular, active, pos_id, priority, photo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['name'] ?? '',
                $data['description'] ?? '',
                $data['price'] ?? 0,
                $data['old_price'],
                $data['weight'] ?? '',
                $data['prep_time'] ?? 30,
                $data['category_id'] ?? 1,
                $data['is_new'] ?? 0,
                $data['is_popular'] ?? 0,
                $data['active'] ?? 1,
                $data['pos_id'] ?? '',
                $data['priority'] ?? 100,
                $data['photo'] ?? null
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllProducts() {
        try {
            $sql = "SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    ORDER BY p.priority ASC, p.id DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return [];
        }
    }
    
    
    public function getProductById($id) {
        try {
            $sql = "SELECT * FROM products WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateProduct($id, $data) {
        try {
            $sql = "UPDATE products SET 
                    name = ?, description = ?, price = ?, old_price = ?, weight = ?, 
                    prep_time = ?, category_id = ?, is_new = ?, is_popular = ?, 
                    active = ?, pos_id = ?, priority = ?, photo = ? 
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['name'] ?? '',
                $data['description'] ?? '',
                $data['price'] ?? 0,
                $data['old_price'],
                $data['weight'] ?? '',
                $data['prep_time'] ?? 30,
                $data['category_id'] ?? 1,
                $data['is_new'] ?? 0,
                $data['is_popular'] ?? 0,
                $data['active'] ?? 1,
                $data['pos_id'] ?? '',
                $data['priority'] ?? 100,
                $data['photo'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteProduct($id) {
        try {
            // Сначала получаем фото для удаления файла
            $product = $this->getProductById($id);
            if ($product && !empty($product['photo'])) {
                // Используем абсолютный путь к публичной директории
                $photo_path = __DIR__ . '/../public' . $product['photo'];
                if (file_exists($photo_path) && is_file($photo_path)) {
                    unlink($photo_path);
                }
            }
            
            $sql = "DELETE FROM products WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }
    

}
?>