<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Cart.php';

$db = Database::getInstance();
$cart = new Cart();

$items = $cart->getItems();
$total = $cart->getTotal();
$total_items = $cart->getTotalItems();

// Обработка действий с корзиной
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_quantity':
            $item_key = $_POST['item_key'] ?? null;
            $quantity = intval($_POST['quantity'] ?? 0);
            
            if ($item_key !== null && isset($items[$item_key])) {
                $cart->updateQuantity($item_key, $quantity);
                header('Location: /cart.php');
                exit;
            }
            break;
            
        case 'remove_item':
            $item_key = $_POST['item_key'] ?? null;
            
            if ($item_key !== null && isset($items[$item_key])) {
                $cart->removeItem($item_key);
                header('Location: /cart.php');
                exit;
            }
            break;
            
        case 'clear_cart':
            $cart->clear();
            header('Location: /cart.php');
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кошик - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .cart-item {
            border-bottom: 1px solid #dee2e6;
            padding: 20px 0;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .quantity-control {
            width: 120px;
        }
        
        .topping-badge {
            font-size: 0.8em;
            margin-right: 5px;
            margin-bottom: 3px;
        }
        
        .empty-cart {
            padding: 100px 0;
        }
    </style>
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <h1 class="h4 mb-0"><i class="fas fa-utensils"></i> One Chef</h1>
            </a>
            
            <div class="d-flex">
                <a href="/menu.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Продовжити покупки
                </a>
                <a href="/cart.php" class="btn btn-primary position-relative">
                    <i class="fas fa-shopping-cart"></i> Кошик
                    <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $total_items; ?>
                    </span>
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container py-4">
        <h1 class="mb-4">Кошик</h1>
        
        <?php if (empty($items)): ?>
        <div class="empty-cart text-center">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <h3 class="text-muted">Ваш кошик порожній</h3>
            <p class="text-muted">Додайте страви з меню, щоб зробити замовлення</p>
            <a href="/menu.php" class="btn btn-primary btn-lg mt-3">
                <i class="fas fa-utensils"></i> Перейти до меню
            </a>
        </div>
        <?php else: ?>
        <div class="row">
            <!-- Список товаров -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <?php foreach($items as $key => $item): ?>
                        <div class="cart-item">
                            <div class="row align-items-center">
                                <!-- Название товара -->
                                <div class="col-md-5">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <?php if (!empty($item['toppings'])): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Додатки:</small>
                                        <?php foreach($item['toppings'] as $topping): ?>
                                        <span class="badge bg-secondary topping-badge">
                                            <?php echo htmlspecialchars($topping['name'] ?? ''); ?>
                                            <?php if (isset($topping['quantity']) && $topping['quantity'] > 1): ?>
                                            ×<?php echo $topping['quantity']; ?>
                                            <?php endif; ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['comment'])): ?>
                                    <div class="mt-1">
                                        <small class="text-muted">Коментар: <?php echo htmlspecialchars($item['comment']); ?></small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Цена за единицу -->
                                <div class="col-md-2">
                                    <span class="text-muted"><?php echo number_format($item['price'], 0); ?> грн</span>
                                </div>
                                
                                <!-- Количество -->
                                <div class="col-md-3">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="item_key" value="<?php echo $key; ?>">
                                        <div class="input-group quantity-control">
                                            <button class="btn btn-outline-secondary" type="submit" name="quantity" value="<?php echo $item['quantity'] - 1; ?>">-</button>
                                            <input type="text" class="form-control text-center" value="<?php echo $item['quantity']; ?>" readonly>
                                            <button class="btn btn-outline-secondary" type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">+</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Итого и удаление -->
                                <div class="col-md-2 text-end">
                                    <h5 class="mb-0"><?php echo number_format($item['price'] * $item['quantity'], 0); ?> грн</h5>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="item_key" value="<?php echo $key; ?>">
                                        <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Видалити цю позицію?')">
                                            <small><i class="fas fa-trash"></i> Видалити</small>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Кнопка очистки корзины -->
                        <div class="mt-4">
                            <form method="POST">
                                <input type="hidden" name="action" value="clear_cart">
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Очистити весь кошик?')">
                                    <i class="fas fa-trash"></i> Очистити кошик
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Итого и оформление -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Разом</h4>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Товари (<?php echo $total_items; ?> шт.)</span>
                                <span><?php echo number_format($total, 0); ?> грн</span>
                            </div>
                            
                            <?php if (isset($_SESSION['onechef_cart']['discount'])): 
                                $discount = $_SESSION['onechef_cart']['discount'];
                            ?>
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Знижка за самовивіз (<?php echo $discount['percent']; ?>%)</span>
                                <span>-<?php echo number_format($discount['amount'], 0); ?> грн</span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Доставка</span>
                                <span class="text-success">Безкоштовно</span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between">
                                <strong>До сплати</strong>
                                <strong class="text-primary"><?php echo number_format($total, 0); ?> грн</strong>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="/checkout.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card"></i> Оформити замовлення
                            </a>
                            <a href="/menu.php" class="btn btn-outline-primary">
                                <i class="fas fa-plus"></i> Додати ще страви
                            </a>
                        </div>
                        
                        <!-- Информация о самовывозе -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6><i class="fas fa-info-circle text-primary"></i> Самовивіз зі знижкою 10%</h6>
                            <p class="small mb-0">Заберіть замовлення за адресою: <?php echo SITE_ADDRESS; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Автообновление при изменении количества
        document.querySelectorAll('.quantity-control button').forEach(button => {
            button.addEventListener('click', function(e) {
                const form = this.closest('form');
                const quantityInput = form.querySelector('input[name="quantity"]');
                
                // Не позволяем уходить в отрицательные значения
                if (parseInt(quantityInput.value) <= 1 && this.textContent === '-') {
                    e.preventDefault();
                    return;
                }
            });
        });
        
        // Обновление общего количества в навигации
        function updateCartCount(count) {
            document.getElementById('cart-count').textContent = count;
        }
    </script>
</body>
</html>