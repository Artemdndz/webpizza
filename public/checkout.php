<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Cart.php';

$db = Database::getInstance();
$cart = new Cart();

$items = $cart->getItems();
$total = $cart->getTotal();
$total_items = $cart->getTotalItems();

// Если корзина пустая, перенаправляем в меню
if (empty($items)) {
    header('Location: /menu.php');
    exit;
}

// Обработка оформления заказа
$errors = [];
$success = false;
$order_number = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация данных
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $order_type = $_POST['order_type'] ?? 'delivery';
    $address = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $comment = trim($_POST['comment'] ?? '');
    $preparation_time = $_POST['preparation_time'] ?? '';
    
    // Валидация
    if (empty($name)) {
        $errors['name'] = "Будь ласка, введіть ваше ім'я";
    }
    
    if (empty($phone)) {
        $errors['phone'] = "Будь ласка, введіть номер телефону";
    } elseif (!preg_match('/^[\d\s\-\+\(\)]+$/', $phone)) {
        $errors['phone'] = "Невірний формат номера телефону";
    }
    
    if ($order_type === 'delivery' && empty($address)) {
        $errors['address'] = "Будь ласка, введіть адресу доставки";
    }
    
    // Если валидация прошла успешно
    if (empty($errors)) {
        // Применяем скидку для самовывоза
        if ($order_type === 'pickup') {
            $cart->applyPickupDiscount();
        } else {
            $cart->removeDiscount();
        }
        
        // Подготавливаем данные для заказа
        $customer_data = [
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'type' => $order_type,
            'address' => $address,
            'payment_method' => $payment_method,
            'comment' => $comment,
            'preparation_time' => $preparation_time === 'asap' ? null : date('Y-m-d H:i:s', strtotime($preparation_time))
        ];
        
        // Создаем заказ
        try {
            $order_id = $cart->createOrder($customer_data);
            
            // Получаем номер заказа
            $stmt = $db->getConnection()->prepare("SELECT order_number FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch();
            
            $order_number = $order['order_number'];
            $success = true;
            
            // Здесь можно добавить отправку уведомлений в Telegram/SMS
            
        } catch (Exception $e) {
            $errors['general'] = "Помилка при створенні замовлення: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформлення замовлення - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <style>
        .step {
            margin-bottom: 30px;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .step.active .step-number {
            background-color: #198754;
        }
        
        .order-type-card {
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .order-type-card:hover {
            border-color: #dee2e6;
        }
        
        .order-type-card.selected {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
        
        .delivery-time-option {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
            cursor: pointer;
        }
        
        .delivery-time-option:hover {
            background-color: #f8f9fa;
        }
        
        .delivery-time-option.selected {
            border-color: #0d6efd;
            background-color: #e7f1ff;
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
                <a href="/cart.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Повернутися до кошика
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container py-4">
        <?php if ($success): ?>
        <!-- Успешное оформление -->
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-success shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                        </div>
                        <h1 class="card-title text-success mb-3">Замовлення оформлено!</h1>
                        <p class="lead mb-4">Дякуємо за ваше замовлення!</p>
                        
                        <div class="alert alert-info text-start mb-4">
                            <h5 class="alert-heading">Номер вашого замовлення: <strong><?php echo $order_number; ?></strong></h5>
                            <p class="mb-0">Ми зателефонуємо вам для підтвердження замовлення.</p>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="/" class="btn btn-primary btn-lg">
                                <i class="fas fa-home"></i> На головну
                            </a>
                            <a href="/menu.php" class="btn btn-outline-primary">
                                <i class="fas fa-utensils"></i> Продовжити покупки
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Форма оформления -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <!-- Шаги -->
                        <div class="d-flex align-items-center step active">
                            <div class="step-number">1</div>
                            <div>
                                <h4 class="mb-0">Спосіб отримання</h4>
                                <p class="text-muted mb-0">Оберіть як хочете отримати замовлення</p>
                            </div>
                        </div>
                        
                        <form method="POST" id="checkout-form">
                            <!-- Выбор способа получения -->
                            <div class="row mb-4" id="order-type-section">
                                <div class="col-md-6 mb-3">
                                    <div class="card order-type-card <?php echo ($_POST['order_type'] ?? 'delivery') === 'delivery' ? 'selected' : ''; ?>" 
                                         onclick="selectOrderType('delivery')">
                                        <div class="card-body text-center">
                                            <i class="fas fa-motorcycle fa-3x mb-3 text-primary"></i>
                                            <h5>Доставка</h5>
                                            <p class="text-muted small">Доставка кур'єром за вашою адресою</p>
                                            <div class="text-success">
                                                <i class="fas fa-check-circle"></i> Безкоштовно
                                            </div>
                                        </div>
                                        <input type="radio" name="order_type" value="delivery" 
                                               <?php echo ($_POST['order_type'] ?? 'delivery') === 'delivery' ? 'checked' : ''; ?> 
                                               style="display: none;">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="card order-type-card <?php echo ($_POST['order_type'] ?? '') === 'pickup' ? 'selected' : ''; ?>" 
                                         onclick="selectOrderType('pickup')">
                                        <div class="card-body text-center">
                                            <i class="fas fa-store fa-3x mb-3 text-primary"></i>
                                            <h5>Самовивіз</h5>
                                            <p class="text-muted small">Заберіть замовлення самостійно</p>
                                            <div class="text-success">
                                                <i class="fas fa-percentage"></i> Знижка 10%
                                            </div>
                                            <small class="text-muted"><?php echo SITE_ADDRESS; ?></small>
                                        </div>
                                        <input type="radio" name="order_type" value="pickup" 
                                               <?php echo ($_POST['order_type'] ?? '') === 'pickup' ? 'checked' : ''; ?> 
                                               style="display: none;">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Контактные данные -->
                            <div class="d-flex align-items-center step">
                                <div class="step-number">2</div>
                                <div>
                                    <h4 class="mb-0">Контактні дані</h4>
                                    <p class="text-muted mb-0">Введіть ваші контактні дані</p>
                                </div>
                            </div>
                            
                            <div class="row mb-4" id="contact-details-section">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Ім'я *</label>
                                    <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                           id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Номер телефону *</label>
                                    <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                           id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                           placeholder="+380 XX XXX XX XX" required>
                                    <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email (необов'язково)</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                    <small class="text-muted">Для відправки чеку</small>
                                </div>
                                
                                <!-- Адрес доставки (показывается только при выборе доставки) -->
                                <div class="col-md-6 mb-3" id="address-field" 
                                     style="<?php echo ($_POST['order_type'] ?? 'delivery') === 'delivery' ? '' : 'display: none;'; ?>">
                                    <label for="address" class="form-label">Адреса доставки *</label>
                                    <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                                              id="address" name="address" rows="2" 
                                              <?php echo ($_POST['order_type'] ?? 'delivery') === 'delivery' ? 'required' : ''; ?>><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                                    <?php if (isset($errors['address'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['address']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Время получения -->
                            <div class="d-flex align-items-center step">
                                <div class="step-number">3</div>
                                <div>
                                    <h4 class="mb-0">Час отримання</h4>
                                    <p class="text-muted mb-0">Оберіть коли хочете отримати замовлення</p>
                                </div>
                            </div>
                            
                            <div class="row mb-4" id="delivery-time-section">
                                <div class="col-md-6">
                                    <div class="delivery-time-option selected" onclick="selectDeliveryTime('asap')">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="preparation_time" 
                                                   id="asap" value="asap" checked>
                                            <label class="form-check-label w-100" for="asap">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>Якомога швидше</strong>
                                                        <p class="mb-0 text-muted small">Приготуємо та доставимо найближчим часом</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="delivery-time-option" onclick="selectDeliveryTime('later')">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="preparation_time" 
                                                   id="later" value="later">
                                            <label class="form-check-label w-100" for="later">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>На конкретний час</strong>
                                                        <p class="mb-0 text-muted small">Оберіть зручний для вас час</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <div id="datetime-picker" style="display: none; margin-top: 15px;">
                                            <label for="delivery_datetime" class="form-label small">Оберіть дату та час:</label>
                                            <input type="text" class="form-control" id="delivery_datetime" 
                                                   name="delivery_datetime" placeholder="Оберіть дату та час">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Способ оплаты -->
                            <div class="d-flex align-items-center step">
                                <div class="step-number">4</div>
                                <div>
                                    <h4 class="mb-0">Спосіб оплати</h4>
                                    <p class="text-muted mb-0">Оберіть спосіб оплати</p>
                                </div>
                            </div>
                            
                            <div class="row mb-4" id="payment-section">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   id="cash" value="cash" checked>
                                            <label class="form-check-label" for="cash">
                                                <strong>Готівкою</strong><br>
                                                <small class="text-muted">Оплата готівкою при отриманні</small>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   id="card" value="card">
                                            <label class="form-check-label" for="card">
                                                <strong>Карткою</strong><br>
                                                <small class="text-muted">Оплата карткою при отриманні</small>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   id="online" value="online" disabled>
                                            <label class="form-check-label" for="online">
                                                <strong>Онлайн оплата</strong><br>
                                                <small class="text-muted">Оплата карткою онлайн (скоро)</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Комментарий -->
                            <div class="mb-4">
                                <label for="comment" class="form-label">Коментар до замовлення (необов'язково)</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" 
                                          placeholder="Наприклад: дзвінок у домофон не працює, зателефонуйте за 10 хвилин до прибуття..."><?php echo htmlspecialchars($_POST['comment'] ?? ''); ?></textarea>
                            </div>
                            
                            <!-- Общая ошибка -->
                            <?php if (isset($errors['general'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $errors['general']; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Кнопка оформления -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-circle"></i> Підтвердити замовлення
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Итого -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Ваше замовлення</h4>
                        
                        <div class="mb-3">
                            <?php foreach($items as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                                    <?php if (!empty($item['toppings'])): ?>
                                    <div class="small text-muted">
                                        <?php foreach($item['toppings'] as $topping): ?>
                                        + <?php echo htmlspecialchars($topping['name']); ?><br>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <span><?php echo number_format($item['price'] * $item['quantity'], 0); ?> грн</span>
                            </div>
                            <?php endforeach; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between">
                                <strong>Разом:</strong>
                                <strong class="text-primary"><?php echo number_format($total, 0); ?> грн</strong>
                            </div>
                            
                            <?php if (isset($_SESSION['onechef_cart']['discount'])): 
                                $discount = $_SESSION['onechef_cart']['discount'];
                            ?>
                            <div class="d-flex justify-content-between text-success">
                                <span>Знижка за самовивіз:</span>
                                <span>-<?php echo number_format($discount['amount'], 0); ?> грн</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <a href="/cart.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-edit"></i> Редагувати кошик
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Скрипты -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/uk.js"></script>
    
    <script>
        // Инициализация календаря
        flatpickr("#delivery_datetime", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            minTime: "10:00",
            maxTime: "22:30",
            time_24hr: true,
            locale: "uk",
            minuteIncrement: 30
        });
        
        // Выбор способа получения
        function selectOrderType(type) {
            // Обновляем визуальный выбор
            document.querySelectorAll('.order-type-card').forEach(card => {
                card.classList.remove('selected');
                card.querySelector('input[type="radio"]').checked = false;
            });
            
            const selectedCard = document.querySelector(`.order-type-card input[value="${type}"]`).closest('.order-type-card');
            selectedCard.classList.add('selected');
            selectedCard.querySelector('input[type="radio"]').checked = true;
            
            // Показываем/скрываем поле адреса
            const addressField = document.getElementById('address-field');
            const addressInput = document.getElementById('address');
            
            if (type === 'delivery') {
                addressField.style.display = 'block';
                addressInput.required = true;
            } else {
                addressField.style.display = 'none';
                addressInput.required = false;
                addressInput.value = '<?php echo SITE_ADDRESS; ?> (самовивіз)';
            }
            
            // Обновляем общую сумму (если есть скидка)
            updateOrderSummary();
        }
        
        // Выбор времени доставки
        function selectDeliveryTime(type) {
            const asapOption = document.querySelector('.delivery-time-option:first-child');
            const laterOption = document.querySelector('.delivery-time-option:last-child');
            const datetimePicker = document.getElementById('datetime-picker');
            
            if (type === 'asap') {
                asapOption.classList.add('selected');
                laterOption.classList.remove('selected');
                document.getElementById('asap').checked = true;
                datetimePicker.style.display = 'none';
            } else {
                asapOption.classList.remove('selected');
                laterOption.classList.add('selected');
                document.getElementById('later').checked = true;
                datetimePicker.style.display = 'block';
            }
        }
        
        // Обновление итоговой суммы (для динамического отображения скидки)
        function updateOrderSummary() {
            // В реальном проекте здесь бы был AJAX запрос
            // Для MVP просто показываем сообщение
            const orderType = document.querySelector('input[name="order_type"]:checked').value;
            
            if (orderType === 'pickup') {
                // Показываем сообщение о скидке
                if (!document.getElementById('discount-message')) {
                    const summaryCard = document.querySelector('.card .card-body');
                    const message = document.createElement('div');
                    message.id = 'discount-message';
                    message.className = 'alert alert-info mt-3';
                    message.innerHTML = '<i class="fas fa-info-circle"></i> За самовивіз діє знижка 10%';
                    summaryCard.appendChild(message);
                }
            } else {
                // Убираем сообщение о скидке
                const message = document.getElementById('discount-message');
                if (message) {
                    message.remove();
                }
            }
        }
        
        // Инициализация при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            // Автоматически выбираем способ получения из POST данных или по умолчанию
            const orderType = '<?php echo $_POST['order_type'] ?? 'delivery'; ?>';
            selectOrderType(orderType);
            
            // Автоматически выбираем время доставки из POST данных
            const prepTime = '<?php echo $_POST['preparation_time'] ?? 'asap'; ?>';
            selectDeliveryTime(prepTime === 'asap' ? 'asap' : 'later');
            
            // Если было выбрано конкретное время, заполняем поле
            <?php if (isset($_POST['delivery_datetime']) && !empty($_POST['delivery_datetime'])): ?>
            document.getElementById('delivery_datetime').value = '<?php echo $_POST['delivery_datetime']; ?>';
            <?php endif; ?>
            
            // Маска для телефона
            const phoneInput = document.getElementById('phone');
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.startsWith('380')) {
                    value = '+380 ' + value.substring(3, 5) + ' ' + value.substring(5, 8) + ' ' + value.substring(8, 10) + ' ' + value.substring(10, 12);
                }
                e.target.value = value.trim();
            });
        });
    </script>
</body>
</html>