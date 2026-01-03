<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Cart.php';

$db = Database::getInstance();
$cart = new Cart();

// Получаем параметры фильтрации
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$category = null;

// Получаем категории для меню
$categories = $db->getCategories();

// Если выбрана категория, получаем её данные
if ($category_id) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $category = $cat;
            break;
        }
    }
}

// Получаем товары
$products = $db->getProducts($category_id);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Меню - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .category-filter {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 10px;
        }
        
        .category-filter .btn {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .product-card {
            height: 100%;
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-img {
            height: 200px;
            object-fit: cover;
        }
        
        .topping-option {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
        }
        
        .topping-option:hover {
            background-color: #f8f9fa;
        }
        
        .topping-option.selected {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
    </style>
</head>
<body>
    <!-- Навигация (можно вынести в отдельный файл) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <h1 class="h4 mb-0"><i class="fas fa-utensils"></i> One Chef</h1>
            </a>
            
            <div class="d-flex">
                <a href="/" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-home"></i> На головну
                </a>
                <a href="/cart.php" class="btn btn-primary position-relative">
                    <i class="fas fa-shopping-cart"></i> Кошик
                    <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $cart->getTotalItems(); ?>
                    </span>
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container py-4">
        <!-- Заголовок и фильтры -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>
                    <?php if($category): ?>
                        <?php echo htmlspecialchars($category['name']); ?>
                        <?php if($category['description']): ?>
                            <small class="text-muted d-block mt-2"><?php echo htmlspecialchars($category['description']); ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        Всі страви
                    <?php endif; ?>
                </h1>
            </div>
            <div class="col-md-4 text-end">
                <div class="input-group">
                    <input type="text" id="search-input" class="form-control" placeholder="Пошук страв...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Фильтр по категориям -->
        <div class="category-filter mb-4">
            <a href="/menu.php" class="btn <?php echo !$category_id ? 'btn-primary' : 'btn-outline-primary'; ?>">
                <i class="fas fa-utensils"></i> Всі
            </a>
            <?php foreach($categories as $cat): ?>
            <a href="/menu.php?category=<?php echo $cat['id']; ?>" 
               class="btn <?php echo $category_id == $cat['id'] ? 'btn-primary' : 'btn-outline-primary'; ?>">
                <?php echo htmlspecialchars($cat['icon']); ?> <?php echo htmlspecialchars($cat['name']); ?>
            </a>
            <?php endforeach; ?>
        </div>
        
        <!-- Список товаров -->
        <div class="row" id="products-container">
            <?php if(empty($products)): ?>
            <div class="col-12 text-center py-5">
                <h3 class="text-muted">Товари не знайдені</h3>
                <p>Спробуйте обрати іншу категорію</p>
            </div>
            <?php endif; ?>
            
            <?php foreach($products as $product): ?>
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4 product-item" 
                 data-name="<?php echo strtolower(htmlspecialchars($product['name'])); ?>"
                 data-category="<?php echo $product['category_id']; ?>">
                <div class="card product-card shadow-sm">
                    <!-- Бейдж новинки -->
                    <?php if($product['is_new']): ?>
                    <span class="position-absolute top-0 start-0 m-2 badge bg-success">Новинка</span>
                    <?php endif; ?>
                    
                    <!-- Фото товара -->
                    <img src="<?php echo htmlspecialchars($product['photo'] ?: 'https://via.placeholder.com/300x200/FF6B35/ffffff?text=' . urlencode($product['name'])); ?>" 
                         class="card-img-top product-img" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         onerror="this.src='https://via.placeholder.com/300x200/cccccc/666666?text=No+Image'">
                    
                    <div class="card-body">
                        <!-- Название и описание -->
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text text-muted small">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                        
                        <!-- Вес и время приготовления -->
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span><i class="fas fa-weight"></i> <?php echo htmlspecialchars($product['weight']); ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo $product['prep_time']; ?> хв</span>
                        </div>
                        
                        <!-- Цена и кнопка -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="h5 text-primary"><?php echo number_format($product['price'], 0); ?> грн</span>
                                <?php if($product['old_price']): ?>
                                <small class="text-muted text-decoration-line-through ms-2">
                                    <?php echo number_format($product['old_price'], 0); ?> грн
                                </small>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-sm btn-primary add-to-cart-btn"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-product-price="<?php echo $product['price']; ?>"
                                    data-category-id="<?php echo $product['category_id']; ?>">
                                <i class="fas fa-plus"></i> В кошик
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Модальное окно добавок -->
    <div class="modal fade" id="toppingsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProductName"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="toppingsContainer">
                        <!-- Добавки будут загружены через AJAX -->
                    </div>
                    <div class="mb-3">
                        <label for="itemComment" class="form-label">Коментар до страви (необов'язково)</label>
                        <textarea class="form-control" id="itemComment" rows="2" placeholder="Наприклад: без цибулі, додати більше соусу..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="me-auto">
                        <h5>Разом: <span id="modalTotalPrice">0</span> грн</h5>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="button" class="btn btn-primary" id="addToCartModalBtn">
                        <i class="fas fa-cart-plus"></i> Додати в кошик
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Текущий выбранный товар
        let currentProduct = null;
        let selectedToppings = [];
        let toppingsModal = null;
        
        // Инициализация модального окна
        document.addEventListener('DOMContentLoaded', function() {
            toppingsModal = new bootstrap.Modal(document.getElementById('toppingsModal'));
            
            // Поиск товаров
            document.getElementById('search-input').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const productItems = document.querySelectorAll('.product-item');
                
                productItems.forEach(item => {
                    const productName = item.dataset.name;
                    if (productName.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
            
            // Обработчики кнопок "В корзину"
            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentProduct = {
                        id: this.dataset.productId,
                        name: this.dataset.productName,
                        price: parseFloat(this.dataset.productPrice),
                        category_id: this.dataset.categoryId
                    };
                    
                    // Загружаем добавки для этой категории
                    loadToppings(currentProduct.category_id);
                    
                    // Показываем модальное окно
                    document.getElementById('modalProductName').textContent = currentProduct.name;
                    toppingsModal.show();
                });
            });
            
            // Кнопка добавления в корзину в модальном окне
            document.getElementById('addToCartModalBtn').addEventListener('click', function() {
                if (!currentProduct) return;
                
                const comment = document.getElementById('itemComment').value;
                
                // Отправляем запрос на сервер
                fetch('/api/cart/add.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: currentProduct.id,
                        name: currentProduct.name,
                        price: currentProduct.price,
                        quantity: 1,
                        toppings: selectedToppings,
                        comment: comment
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Обновляем счетчик в корзине
                        updateCartCount(data.total_items);
                        
                        // Закрываем модальное окно
                        toppingsModal.hide();
                        
                        // Сбрасываем форму
                        resetToppingsForm();
                        
                        // Показываем уведомление
                        showNotification(`"${currentProduct.name}" додано до кошика!`, 'success');
                    } else {
                        showNotification('Помилка: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Помилка при додаванні до кошика', 'error');
                });
            });
            
            // Событие закрытия модального окна
            document.getElementById('toppingsModal').addEventListener('hidden.bs.modal', function() {
                resetToppingsForm();
            });
        });
        
        // Загрузка добавок
        function loadToppings(category_id) {
            fetch(`/api/toppings/get.php?category_id=${category_id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderToppings(data.toppings);
                    } else {
                        document.getElementById('toppingsContainer').innerHTML = 
                            '<p class="text-muted">Немає доступних добавок</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('toppingsContainer').innerHTML = 
                        '<p class="text-danger">Помилка завантаження добавок</p>';
                });
        }
        
        // Отрисовка добавок
        function renderToppings(toppingGroups) {
            const container = document.getElementById('toppingsContainer');
            container.innerHTML = '';
            selectedToppings = [];
            
            if (!toppingGroups || toppingGroups.length === 0) {
                container.innerHTML = '<p class="text-muted">Немає доступних добавок</p>';
                updateTotalPrice();
                return;
            }
            
            toppingGroups.forEach(group => {
                const groupElement = document.createElement('div');
                groupElement.className = 'mb-4';
                groupElement.innerHTML = `
                    <h6>${group.name}</h6>
                    <small class="text-muted d-block mb-2">
                        ${group.type === 'single' ? 'Виберіть один варіант' : 
                          group.type === 'required' ? 'Обов\'язково виберіть' : 
                          'Можна вибрати кілька'} 
                        ${group.min_selection > 0 ? ` (мінімум ${group.min_selection})` : ''}
                        ${group.max_selection > 0 ? ` (максимум ${group.max_selection})` : ''}
                    </small>
                    <div id="group-${group.id}" class="toppings-group"></div>
                `;
                container.appendChild(groupElement);
                
                // Добавляем варианты добавок
                const groupContainer = document.getElementById(`group-${group.id}`);
                group.toppings.forEach(topping => {
                    const toppingElement = document.createElement('div');
                    toppingElement.className = 'topping-option';
                    toppingElement.innerHTML = `
                        <div class="form-check">
                            <input class="form-check-input" type="${group.type === 'single' ? 'radio' : 'checkbox'}" 
                                   name="topping-group-${group.id}" 
                                   id="topping-${topping.id}" 
                                   value="${topping.id}"
                                   data-price="${topping.price}">
                            <label class="form-check-label w-100" for="topping-${topping.id}">
                                <div class="d-flex justify-content-between">
                                    <span>${topping.name}</span>
                                    <span class="text-primary">+${topping.price} грн</span>
                                </div>
                                ${topping.weight ? `<small class="text-muted">${topping.weight}</small>` : ''}
                            </label>
                        </div>
                    `;
                    
                    // Обработчик выбора добавки
                    const input = toppingElement.querySelector('input');
                    input.addEventListener('change', function() {
                        if (this.checked) {
                            // Для radio кнопок убираем предыдущий выбор в этой группе
                            if (group.type === 'single') {
                                selectedToppings = selectedToppings.filter(t => {
                                    const toppingGroup = group.toppings.find(g => g.id === t.topping_id);
                                    return !toppingGroup || toppingGroup.group_id !== group.id;
                                });
                            }
                            
                            selectedToppings.push({
                                topping_id: topping.id,
                                name: topping.name,
                                price: parseFloat(topping.price),
                                quantity: 1,
                                group_id: group.id,
                                group_type: group.type
                            });
                        } else {
                            selectedToppings = selectedToppings.filter(t => t.topping_id !== topping.id);
                        }
                        
                        updateTotalPrice();
                    });
                    
                    groupContainer.appendChild(toppingElement);
                });
            });
            
            updateTotalPrice();
        }
        
        // Обновление общей цены
        function updateTotalPrice() {
            let total = currentProduct ? currentProduct.price : 0;
            
            selectedToppings.forEach(topping => {
                total += topping.price * topping.quantity;
            });
            
            document.getElementById('modalTotalPrice').textContent = total.toFixed(2);
        }
        
        // Сброс формы добавок
        function resetToppingsForm() {
            currentProduct = null;
            selectedToppings = [];
            document.getElementById('itemComment').value = '';
            document.getElementById('modalTotalPrice').textContent = '0';
            document.getElementById('toppingsContainer').innerHTML = '';
        }
        
        // Обновление счетчика корзины
        function updateCartCount(count) {
            document.getElementById('cart-count').textContent = count;
        }
        
        // Показ уведомления
        function showNotification(message, type = 'info') {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
            alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 3000);
        }
    </script>
</body>
</html>