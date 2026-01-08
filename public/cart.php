<?php
require_once __DIR__ . '/header.php';

$cart = new Cart();
$items = $cart->getItems();
$total_price = $cart->getTotalPrice();
$total_items = $cart->getTotalItems();

// Получаем текущее время для установки минимального времени заказа
$current_time = date('H:i');
$min_time = date('H:i', strtotime('+60 minutes')); // Минимум через 60 минут
?>

        <div class="container py-5">
            <h1 class="display-5 fw-bold mb-4">Кошик</h1>
            
            <?php if ($cart->isEmpty()): ?>
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                <h3 class="text-muted mb-3">Ваш кошик порожній</h3>
                <p class="text-secondary mb-4">Додайте товари з меню, щоб зробити замовлення</p>
                <a href="/menu.php" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-utensils me-2"></i>Перейти до меню
                </a>
            </div>
            <?php else: ?>
            
            <div class="row">
                <!-- Список товаров -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width: 100px;">Фото</th>
                                            <th scope="col">Товар</th>
                                            <th scope="col" class="text-center">Кількість</th>
                                            <th scope="col" class="text-end">Ціна</th>
                                            <th scope="col" class="text-end">Сума</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                        <tr class="cart-item" data-id="<?php echo $item['id']; ?>">
                                            <td>
                                                <img src="<?php echo htmlspecialchars($item['image'] ?: '/images/placeholder.jpg'); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                     class="img-fluid rounded" 
                                                     style="width: 80px; height: 80px; object-fit: cover;">
                                            </td>
                                            <td>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <?php if (!empty($item['comment'])): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($item['comment']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="input-group input-group-sm" style="width: 120px;">
                                                    <button class="btn btn-outline-secondary minus-btn" type="button">-</button>
                                                    <input type="number" class="form-control text-center quantity-input" 
                                                           value="<?php echo $item['quantity']; ?>" min="1" max="99">
                                                    <button class="btn btn-outline-secondary plus-btn" type="button">+</button>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="item-price"><?php echo number_format($item['price'], 0); ?></span> грн
                                            </td>
                                            <td class="text-end">
                                                <span class="item-total"><?php echo number_format($item['price'] * $item['quantity'], 0); ?></span> грн
                                            </td>
                                            <td>
                                                <button class="btn btn-link text-danger remove-btn" title="Видалити">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="/menu.php" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i>Продовжити покупки
                                </a>
                                <button class="btn btn-outline-danger" id="clear-cart-btn">
                                    <i class="fas fa-trash me-2"></i>Очистити кошик
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Итоги и оформление -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Разом</h5>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Товари (<?php echo $total_items; ?> шт.)</span>
                                    <span id="cart-items-total"><?php echo number_format($total_price, 0); ?></span> грн
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Доставка</span>
                                    <span id="delivery-cost">
                                        <?php if ($total_price >= 400): ?>
                                            <span class="text-success">Безкоштовно</span>
                                        <?php else: ?>
                                            <span>50 грн</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                
                                <?php if ($total_price < 200): ?>
                                <div class="alert alert-warning py-2 small">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    Мінімальне замовлення 200 грн
                                </div>
                                <?php endif; ?>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between fw-bold fs-5">
                                    <span>Всього</span>
                                    <span id="cart-total">
                                        <?php 
                                        $delivery = ($total_price >= 400) ? 0 : 50;
                                        echo number_format($total_price + $delivery, 0);
                                        ?>
                                    </span> грн
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="order-type" id="pickup" value="pickup" checked>
                                    <label class="form-check-label" for="pickup">
                                        <strong>Самовивіз</strong>
                                        <small class="d-block text-success">Знижка <?php echo PICKUP_DISCOUNT; ?>%</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="order-type" id="delivery" value="delivery">
                                    <label class="form-check-label" for="delivery">
                                        <strong>Доставка</strong>
                                        <small class="d-block">
                                            <?php if ($total_price >= 400): ?>
                                                <span class="text-success">Безкоштовно</span>
                                            <?php else: ?>
                                                50 грн
                                            <?php endif; ?>
                                        </small>
                                    </label>
                                </div>
                            </div>
                            
                            <button class="btn btn-primary btn-lg w-100 py-3" id="checkout-btn" 
                                    <?php echo ($total_price < 200) ? 'disabled' : ''; ?>>
                                <i class="fas fa-check-circle me-2"></i>Оформити замовлення
                            </button>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">Натискаючи "Оформити замовлення", ви погоджуєтесь з умовами обробки даних</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Модальное окно оформления заказа -->
        <div class="modal fade" id="checkoutModal" tabindex="-1" data-bs-theme="dark">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Оформлення замовлення</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="order-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="customer-name" class="form-label">Ім'я *</label>
                                    <input type="text" class="form-control" id="customer-name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="customer-phone" class="form-label">Телефон *</label>
                                    <input type="tel" class="form-control" id="customer-phone" required 
                                           placeholder="+380 XX XXX XX XX">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="customer-email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="customer-email" 
                                       placeholder="email@example.com">
                            </div>
                            
                            <!-- Блок адреса доставки -->
                            <div class="mb-3 delivery-address-block" style="display: none;">
                                <label for="delivery-address" class="form-label">Адреса доставки *</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" id="delivery-address" 
                                           placeholder="Вулиця, будинок, квартира" required>
                                    <button class="btn btn-outline-primary" type="button" id="geolocation-btn">
                                        <i class="fas fa-location-arrow"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Натисніть кнопку <i class="fas fa-location-arrow"></i> для використання вашої поточної локації</small>
                                
                                <!-- Дополнительные поля адреса -->
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control mt-2" id="address-entrance" 
                                               placeholder="Під'їзд (необов'язково)">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control mt-2" id="address-floor" 
                                               placeholder="Поверх (необов'язково)">
                                    </div>
                                    <div class="col-12">
                                        <input type="text" class="form-control mt-2" id="address-intercom" 
                                               placeholder="Домофон (необов'язково)">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Блок адреса самовывоза -->
                            <div id="pickup-address" class="text-muted small mt-1">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo SITE_ADDRESS; ?>
                            </div>
                            
                            <!-- Время получения заказа -->
                            <div class="mb-3">
                                <label class="form-label">Час отримання замовлення</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="delivery-time" 
                                           id="delivery-asap" value="asap" checked>
                                    <label class="form-check-label" for="delivery-asap">
                                        <i class="fas fa-bolt me-2 text-warning"></i>Як можна швидше
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="delivery-time" 
                                           id="delivery-scheduled" value="scheduled">
                                    <label class="form-check-label" for="delivery-scheduled">
                                        <i class="fas fa-clock me-2 text-info"></i>На певний час
                                    </label>
                                </div>
                                
                                <!-- Блок выбора времени -->
                                <div id="time-selection-block" class="mt-3" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="delivery-date" class="form-label">Дата</label>
                                            <input type="date" class="form-control" id="delivery-date" 
                                                   min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="delivery-time-input" class="form-label">Час</label>
                                            <input type="time" class="form-control" id="delivery-time-input" 
                                                   min="<?php echo $min_time; ?>" 
                                                   max="22:00" 
                                                   value="<?php echo $min_time; ?>">
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Мінімальний час доставки: <?php echo $min_time; ?>
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Способ оплаты -->
                            <div class="mb-4">
                                <label class="form-label">Спосіб оплати</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment-method" 
                                           id="cash" value="cash" checked>
                                    <label class="form-check-label" for="cash">
                                        <i class="fas fa-money-bill-wave me-2"></i>Готівкою
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment-method" 
                                           id="card" value="card">
                                    <label class="form-check-label" for="card">
                                        <i class="fas fa-credit-card me-2"></i>Картою
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="order-comment" class="form-label">Коментар до замовлення</label>
                                <textarea class="form-control" id="order-comment" rows="3" 
                                          placeholder="Бажаний час доставки, деталі замовлення..."></textarea>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Інформація про замовлення:</strong>
                                <div class="mt-2">
                                    <div>Спосіб отримання: <span id="order-type-summary"></span></div>
                                    <div>Кількість товарів: <span id="order-items-summary"><?php echo $total_items; ?></span></div>
                                    <div>Загальна сума: <span id="order-total-summary"></span> грн</div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                        <button type="button" class="btn btn-primary" id="submit-order-btn">
                            <i class="fas fa-paper-plane me-2"></i>Надіслати замовлення
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
                let currentOrderType = 'pickup';
                let deliveryCost = <?php echo ($total_price >= 400) ? 0 : 50; ?>;
                let autocomplete = null;
                let googleMapsLoaded = typeof google !== 'undefined';
                
                // Инициализация даты и времени
                initDateTime();
                
                // Инициализация Google Maps Autocomplete
                if (googleMapsLoaded) {
                    initGoogleMapsAutocomplete();
                } else {
                    console.log('Google Maps API не завантажено');
                }
                
                // Обработчики изменения количества
                document.querySelectorAll('.quantity-input').forEach(input => {
                    input.addEventListener('change', function() {
                        const quantity = parseInt(this.value);
                        const itemId = this.closest('.cart-item').dataset.id;
                        
                        if (quantity < 1 || quantity > 99) {
                            this.value = Math.min(99, Math.max(1, quantity));
                            return;
                        }
                        
                        updateCartItem(itemId, quantity);
                    });
                });
                
                // Кнопки +/-
                document.querySelectorAll('.plus-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const input = this.parentElement.querySelector('.quantity-input');
                        const newValue = parseInt(input.value) + 1;
                        if (newValue <= 99) {
                            input.value = newValue;
                            const itemId = this.closest('.cart-item').dataset.id;
                            updateCartItem(itemId, newValue);
                        }
                    });
                });
                
                document.querySelectorAll('.minus-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const input = this.parentElement.querySelector('.quantity-input');
                        const newValue = parseInt(input.value) - 1;
                        if (newValue >= 1) {
                            input.value = newValue;
                            const itemId = this.closest('.cart-item').dataset.id;
                            updateCartItem(itemId, newValue);
                        }
                    });
                });
                
                // Кнопки удаления
                document.querySelectorAll('.remove-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const itemId = this.closest('.cart-item').dataset.id;
                        if (confirm('Видалити товар з кошика?')) {
                            removeCartItem(itemId);
                        }
                    });
                });
                
                // Кнопка очистки корзины
                document.getElementById('clear-cart-btn').addEventListener('click', function() {
                    if (confirm('Очистити весь кошик?')) {
                        clearCart();
                    }
                });
                
                // Переключатели типа заказа
                document.querySelectorAll('input[name="order-type"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        currentOrderType = this.value;
                        updateOrderSummary();
                        toggleAddressFields();
                    });
                });
                
                // Переключатели времени доставки
                document.querySelectorAll('input[name="delivery-time"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        toggleTimeSelection();
                    });
                });
                
                // Кнопка геолокации
                document.getElementById('geolocation-btn').addEventListener('click', getCurrentLocation);
                
                // Кнопка оформления заказа
                document.getElementById('checkout-btn').addEventListener('click', function() {
                    updateOrderSummary();
                    checkoutModal.show();
                });
                
                // Отправка заказа
                document.getElementById('submit-order-btn').addEventListener('click', submitOrder);
                
                // Функция инициализации даты и времени
                function initDateTime() {
                    const today = new Date();
                    const tomorrow = new Date(today);
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    
                    // Устанавливаем минимальную дату как сегодня
                    document.getElementById('delivery-date').min = today.toISOString().split('T')[0];
                    
                    // Устанавливаем дефолтную дату как завтра
                    document.getElementById('delivery-date').value = tomorrow.toISOString().split('T')[0];
                    
                    // Устанавливаем текущее время + 1 час как минимальное
                    const minTime = new Date();
                    minTime.setHours(minTime.getHours() + 1);
                    const minTimeString = minTime.getHours().toString().padStart(2, '0') + ':' + 
                                         minTime.getMinutes().toString().padStart(2, '0');
                    
                    document.getElementById('delivery-time-input').min = minTimeString;
                    
                    // Устанавливаем максимальное время 22:00
                    document.getElementById('delivery-time-input').max = '22:00';
                    
                    // Если минимальное время больше 22:00, устанавливаем на завтра
                    if (minTime.getHours() >= 22) {
                        const tomorrow = new Date();
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        document.getElementById('delivery-date').value = tomorrow.toISOString().split('T')[0];
                        document.getElementById('delivery-time-input').min = '10:00';
                        document.getElementById('delivery-time-input').value = '12:00';
                    } else {
                        document.getElementById('delivery-time-input').value = minTimeString;
                    }
                }
                
                // Функция инициализации Google Maps Autocomplete
                function initGoogleMapsAutocomplete() {
                    const addressInput = document.getElementById('delivery-address');
                    if (addressInput && google.maps.places) {
                        autocomplete = new google.maps.places.Autocomplete(addressInput, {
                            componentRestrictions: { country: 'ua' },
                            fields: ['formatted_address', 'geometry', 'name'],
                            types: ['address']
                        });
                        
                        autocomplete.addListener('place_changed', function() {
                            const place = autocomplete.getPlace();
                            if (!place.geometry) {
                                console.log('Не вдалося отримати геолокацію для адреси');
                                return;
                            }
                            
                            console.log('Адреса вибрана:', place.formatted_address);
                            addressInput.value = place.formatted_address;
                        });
                        
                        console.log('Google Maps Autocomplete ініціалізовано');
                    }
                }
                
                // Функция получения текущей геолокации
                function getCurrentLocation() {
                    if (!navigator.geolocation) {
                        showNotification('Геолокація не підтримується вашим браузером', 'error');
                        return;
                    }
                    
                    showNotification('Визначаємо ваше місцезнаходження...', 'info');
                    
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const latitude = position.coords.latitude;
                            const longitude = position.coords.longitude;
                            
                            if (googleMapsLoaded) {
                                geocodeLatLng(latitude, longitude);
                            } else {
                                // Если Google Maps не загружен, показываем координаты
                                document.getElementById('delivery-address').value = 
                                    `Координати: ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                                showNotification('Місцезнаходження визначено (координати)', 'success');
                            }
                        },
                        function(error) {
                            let errorMessage = 'Не вдалося отримати ваше місцезнаходження';
                            
                            switch(error.code) {
                                case error.PERMISSION_DENIED:
                                    errorMessage = 'Доступ до геолокації заборонено. Дозвольте доступ в налаштуваннях браузера.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    errorMessage = 'Інформація про місцезнаходження недоступна.';
                                    break;
                                case error.TIMEOUT:
                                    errorMessage = 'Час очікування геолокації вийшов.';
                                    break;
                            }
                            
                            showNotification(errorMessage, 'error');
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                }
                
                // Функция геокодирования координат в адрес
                function geocodeLatLng(lat, lng) {
                    if (!googleMapsLoaded) return;
                    
                    const geocoder = new google.maps.Geocoder();
                    const latlng = { lat: lat, lng: lng };
                    
                    geocoder.geocode({ location: latlng }, function(results, status) {
                        if (status === 'OK') {
                            if (results[0]) {
                                document.getElementById('delivery-address').value = results[0].formatted_address;
                                showNotification('Адресу визначено за вашим місцезнаходженням', 'success');
                            } else {
                                showNotification('Адресу не знайдено для цих координат', 'warning');
                            }
                        } else {
                            showNotification('Помилка геокодування: ' + status, 'error');
                        }
                    });
                }
                
                // Функция переключения полей адреса
                function toggleAddressFields() {
                    const addressBlock = document.querySelector('.delivery-address-block');
                    const pickupAddress = document.getElementById('pickup-address');
                    
                    if (currentOrderType === 'delivery') {
                        addressBlock.style.display = 'block';
                        pickupAddress.style.display = 'none';
                        document.getElementById('delivery-address').required = true;
                    } else {
                        addressBlock.style.display = 'none';
                        pickupAddress.style.display = 'block';
                        document.getElementById('delivery-address').required = false;
                    }
                }
                
                // Функция переключения выбора времени
                function toggleTimeSelection() {
                    const timeSelectionBlock = document.getElementById('time-selection-block');
                    const isScheduled = document.getElementById('delivery-scheduled').checked;
                    
                    timeSelectionBlock.style.display = isScheduled ? 'block' : 'none';
                    
                    if (isScheduled) {
                        document.getElementById('delivery-date').required = true;
                        document.getElementById('delivery-time-input').required = true;
                    } else {
                        document.getElementById('delivery-date').required = false;
                        document.getElementById('delivery-time-input').required = false;
                    }
                }
                
                // Функция обновления товара в корзине
                function updateCartItem(itemId, quantity) {
                    const formData = new FormData();
                    formData.append('action', 'update');
                    formData.append('id', itemId);
                    formData.append('quantity', quantity);
                    
                    fetch('/api/cart/update.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Помилка сервера: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Обновляем общие суммы
                            document.getElementById('cart-count').textContent = data.total_items;
                            document.getElementById('cart-items-total').textContent = 
                                formatPrice(data.total_price);
                            
                            // Обновляем сумму товара
                            const itemRow = document.querySelector(`.cart-item[data-id="${itemId}"]`);
                            if (itemRow) {
                                itemRow.querySelector('.item-total').textContent = 
                                    formatPrice(data.item_total);
                            }
                            
                            // Пересчитываем итоги
                            updateTotals(data.total_price);
                            showNotification('Кількість оновлено', 'success');
                        } else {
                            showNotification('Помилка: ' + (data.message || 'невідома помилка'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Помилка оновлення: ' + error.message, 'error');
                    });
                }
                
                // Функция удаления товара из корзины
                function removeCartItem(itemId) {
                    const formData = new FormData();
                    formData.append('action', 'remove');
                    formData.append('id', itemId);
                    
                    fetch('/api/cart/update.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Помилка сервера: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Удаляем строку из таблицы
                            const itemRow = document.querySelector(`.cart-item[data-id="${itemId}"]`);
                            if (itemRow) {
                                itemRow.remove();
                            }
                            
                            // Обновляем счетчик
                            document.getElementById('cart-count').textContent = data.total_items;
                            
                            // Обновляем общие суммы
                            document.getElementById('cart-items-total').textContent = 
                                formatPrice(data.total_price);
                            
                            // Пересчитываем итоги
                            updateTotals(data.total_price);
                            
                            // Если корзина пуста, перезагружаем страницу
                            if (data.total_items === 0) {
                                setTimeout(() => {
                                    location.reload();
                                }, 1000);
                            }
                            
                            showNotification('Товар видалено', 'success');
                        } else {
                            showNotification('Помилка: ' + (data.message || 'невідома помилка'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Помилка видалення', 'error');
                    });
                }
                
                // Функция очистки корзины
                function clearCart() {
                    const formData = new FormData();
                    formData.append('action', 'clear');
                    
                    fetch('/api/cart/update.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Помилка сервера: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        } else {
                            showNotification('Помилка: ' + (data.message || 'невідома помилка'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Помилка очищення кошика', 'error');
                    });
                }
                
                // Функция обновления итогов
                function updateTotals(itemsTotal) {
                    itemsTotal = parseFloat(itemsTotal) || 0;
                    
                    // Пересчитываем стоимость доставки
                    const deliveryElement = document.getElementById('delivery-cost');
                    const deliveryText = deliveryElement.querySelector('span');
                    
                    if (itemsTotal >= 400) {
                        deliveryCost = 0;
                        deliveryText.textContent = 'Безкоштовно';
                        deliveryText.className = 'text-success';
                    } else {
                        deliveryCost = 50;
                        deliveryText.textContent = '50 грн';
                        deliveryText.className = '';
                    }
                    
                    // Обновляем общую сумму
                    const total = itemsTotal + deliveryCost;
                    document.getElementById('cart-total').textContent = formatPrice(total);
                    
                    // Обновляем кнопку оформления
                    const checkoutBtn = document.getElementById('checkout-btn');
                    checkoutBtn.disabled = itemsTotal < 200;
                    
                    // Обновляем количество товаров в сводке
                    const cartCount = document.getElementById('cart-count').textContent;
                    document.getElementById('order-items-summary').textContent = cartCount;
                }
                
                // Функция обновления сводки заказа
                function updateOrderSummary() {
                    const itemsTotal = parseFloat(document.getElementById('cart-items-total').textContent.replace(/\s/g, '')) || 0;
                    let orderTotal = itemsTotal + deliveryCost;
                    
                    // Применяем скидку для самовывоза
                    if (currentOrderType === 'pickup') {
                        const discount = itemsTotal * <?php echo PICKUP_DISCOUNT; ?> / 100;
                        orderTotal -= discount;
                    }
                    
                    // Обновляем сводку
                    const orderTypeText = currentOrderType === 'pickup' ? 'Самовивіз' : 'Доставка';
                    document.getElementById('order-type-summary').textContent = orderTypeText;
                    document.getElementById('order-total-summary').textContent = formatPrice(orderTotal);
                }
                
                // Функция отправки заказа
                function submitOrder() {
                    const form = document.getElementById('order-form');
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }
                    
                    // Проверяем адрес для доставки
                    if (currentOrderType === 'delivery') {
                        const address = document.getElementById('delivery-address').value.trim();
                        if (!address) {
                            showNotification('Будь ласка, вкажіть адресу доставки', 'error');
                            document.getElementById('delivery-address').focus();
                            return;
                        }
                    }
                    
                    // Проверяем время для запланированной доставки
                    const isScheduled = document.getElementById('delivery-scheduled').checked;
                    if (isScheduled) {
                        const deliveryDate = document.getElementById('delivery-date').value;
                        const deliveryTime = document.getElementById('delivery-time-input').value;
                        
                        if (!deliveryDate || !deliveryTime) {
                            showNotification('Будь ласка, оберіть дату та час доставки', 'error');
                            return;
                        }
                        
                        // Проверяем, что время в будущем
                        const selectedDateTime = new Date(deliveryDate + 'T' + deliveryTime);
                        const now = new Date();
                        
                        if (selectedDateTime <= now) {
                            showNotification('Будь ласка, оберіть майбутню дату та час', 'error');
                            return;
                        }
                    }
                    
                    const submitBtn = document.getElementById('submit-order-btn');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Надсилання...';
                    
                    // Собираем данные заказа
                    const orderData = {
                        customer_name: document.getElementById('customer-name').value,
                        phone: document.getElementById('customer-phone').value,
                        email: document.getElementById('customer-email').value || '',
                        type: currentOrderType,
                        address: currentOrderType === 'delivery' 
                            ? document.getElementById('delivery-address').value 
                            : '<?php echo SITE_ADDRESS; ?>',
                        delivery_details: currentOrderType === 'delivery' ? {
                            entrance: document.getElementById('address-entrance').value || '',
                            floor: document.getElementById('address-floor').value || '',
                            intercom: document.getElementById('address-intercom').value || ''
                        } : {},
                        delivery_time: isScheduled ? 'scheduled' : 'asap',
                        delivery_schedule: isScheduled ? {
                            date: document.getElementById('delivery-date').value,
                            time: document.getElementById('delivery-time-input').value
                        } : {},
                        payment_method: document.querySelector('input[name="payment-method"]:checked').value,
                        comment: document.getElementById('order-comment').value || ''
                    };
                    
                    // Отправляем заказ
                    fetch('/api/order/create.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(orderData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            checkoutModal.hide();
                            showNotification(data.message || 'Замовлення успішно оформлено!', 'success');
                            
                            // Очищаем корзину и перенаправляем
                            setTimeout(() => {
                                clearCart();
                            }, 2000);
                        } else {
                            showNotification(data.message || 'Помилка при оформленні замовлення', 'error');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Надіслати замовлення';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Помилка з\'єднання', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Надіслати замовлення';
                    });
                }
                
                // Вспомогательная функция форматирования цены
                function formatPrice(price) {
                    return new Intl.NumberFormat('uk-UA').format(Math.round(price));
                }
                
                // Функция показа уведомлений
                function showNotification(message, type = 'success') {
                    // Убираем старые уведомления
                    const oldAlerts = document.querySelectorAll('.notification-alert');
                    oldAlerts.forEach(alert => alert.remove());
                    
                    const alert = document.createElement('div');
                    alert.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed notification-alert`;
                    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
                    alert.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
                            <div>${message}</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(alert);
                    
                    setTimeout(() => {
                        if (alert.parentElement) {
                            alert.remove();
                        }
                    }, 3000);
                }
                
                // Инициализация при открытии модального окна
                document.getElementById('checkoutModal').addEventListener('show.bs.modal', function() {
                    toggleAddressFields();
                    toggleTimeSelection();
                    
                    // Если Google Maps загрузился после загрузки страницы
                    if (!googleMapsLoaded && typeof google !== 'undefined') {
                        googleMapsLoaded = true;
                        initGoogleMapsAutocomplete();
                    }
                });
            });
        </script>

<?php require_once __DIR__ . '/footer.php'; ?>