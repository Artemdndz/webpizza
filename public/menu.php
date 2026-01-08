<?php
require_once __DIR__ . '/header.php';

// Получаем параметры фильтрации
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$product_id = isset($_GET['product']) ? intval($_GET['product']) : null;
$category = null;

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
if (!is_array($products)) {
    $products = []; // Гарантируем, что $products всегда будет массивом
}

// Если передан параметр product, получаем данные этого товара
$shared_product = null;
if ($product_id) {
    $shared_product = $db->getProduct($product_id);
}
?>
        
        <!-- Заголовок и фильтры -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold">
                    <?php if($category): ?>
                        <?php echo htmlspecialchars($category['name']); ?>
                        <?php if($category['description']): ?>
                            <small class="text-muted d-block mt-2 fs-5 fw-normal"><?php echo htmlspecialchars($category['description']); ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        Всі страви
                    <?php endif; ?>
                </h1>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="input-group">
                    <input type="text" id="search-input" class="form-control bg-dark border-secondary text-white" placeholder="Пошук страв...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Фильтр по категориям -->
        <div class="category-filter mb-5">
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
                <div class="fade-in">
                    <i class="fas fa-utensils fa-4x text-muted mb-3"></i>
                    <h3 class="text-muted">Товари не знайдені</h3>
                    <p class="text-secondary">Спробуйте обрати іншу категорію</p>
                    <a href="/menu.php" class="btn btn-primary mt-3">
                        <i class="fas fa-undo"></i> Повернутися до всіх категорій
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <?php foreach($products as $product): ?>
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4 product-item" 
                 data-name="<?php echo strtolower(htmlspecialchars($product['name'])); ?>"
                 data-category="<?php echo $product['category_id']; ?>"
                 data-product-id="<?php echo $product['id']; ?>">
                <div class="card product-card shadow-sm fade-in" style="cursor: pointer;" 
                     onclick="openProductModal(<?php echo $product['id']; ?>)">
                    <!-- Бейдж новинки -->
                    <?php if($product['is_new']): ?>
                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">Новинка</span>
                    <?php endif; ?>
                    
                    <!-- Бейдж популярного -->
                    <?php if($product['is_popular']): ?>
                    <span class="badge bg-warning position-absolute top-0 end-0 m-2">Популярне</span>
                    <?php endif; ?>
                    
                    <!-- Фото товара -->
                    <div class="product-img-wrapper">
                        <img src="<?php echo htmlspecialchars($product['photo'] ?: '/images/placeholder.jpg'); ?>" 
                             class="product-img w-100" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='https://via.placeholder.com/300x200/333333/888888?text=No+Image'">
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <!-- Название -->
                        <h5 class="card-title mb-2"><?php echo htmlspecialchars($product['name']); ?></h5>
                        
                        <!-- Описание -->
                        <p class="card-text text-secondary small mb-2">
                            <?php 
                            $description = htmlspecialchars($product['description']);
                            if (strlen($description) > 80) {
                                echo substr($description, 0, 80) . '...';
                            } else {
                                echo $description;
                            }
                            ?>
                        </p>
                        
                        <!-- Вес и время приготовления -->
                        <div class="d-flex justify-content-between small text-muted mb-3">
                            <span><i class="fas fa-weight me-1"></i> <?php echo htmlspecialchars($product['weight']); ?></span>
                            <span><i class="fas fa-clock me-1"></i> <?php echo $product['prep_time']; ?> хв</span>
                        </div>
                        
                        <!-- Цена и кнопка -->
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <div>
                                <span class="h5 text-primary mb-0"><?php echo number_format($product['price'], 0); ?> грн</span>
                                <?php if($product['old_price']): ?>
                                <small class="text-muted text-decoration-line-through ms-2 d-block">
                                    <?php echo number_format($product['old_price'], 0); ?> грн
                                </small>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-sm btn-primary add-to-cart-btn"
                                    onclick="event.stopPropagation(); addToCart(
                                        '<?php echo $product['id']; ?>',
                                        '<?php echo htmlspecialchars(addslashes($product['name'])); ?>',
                                        '<?php echo $product['price']; ?>'
                                    )">
                                <i class="fas fa-plus"></i> В кошик
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Кнопка "Все меню" -->
        <?php if(!empty($products) && $category_id): ?>
        <div class="text-center mt-5">
            <a href="/menu.php" class="btn btn-outline-primary px-4">
                <i class="fas fa-arrow-left me-2"></i>Всі категорії
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Модальное окно деталей товара -->
        <div class="modal fade" id="productDetailModal" tabindex="-1" data-bs-theme="dark" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark rounded-lg">
                    <!-- Кнопка закрытия -->
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" 
                            data-bs-dismiss="modal" onclick="closeProductModal()"></button>
                    
                    <!-- Фото товара -->
                    <div class="product-modal-img-container">
                        <img id="detailProductImg" src="" class="img-fluid" alt="">
                    </div>
                    
                    <div class="modal-body p-4">
                        <!-- Название товара -->
                        <h3 id="detailProductName" class="mb-3"></h3>
                        
                        <!-- Цена -->
                        <div class="mb-4">
                            <h4 class="text-primary" id="detailProductPrice"></h4>
                            <p class="text-muted text-decoration-line-through" id="detailProductOldPrice" style="display: none;"></p>
                        </div>
                        
                        <!-- Описание -->
                        <div class="mb-4">
                            <h6 class="text-secondary mb-2">Опис</h6>
                            <p id="detailProductDescription" class="text-light mb-0"></p>
                        </div>
                        
                        <!-- Характеристики -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="product-feature">
                                    <i class="fas fa-weight text-primary me-2"></i>
                                    <span id="detailProductWeight"></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="product-feature">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    <span id="detailProductPrepTime"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Кнопка "Поделиться" -->
                        <div class="d-flex justify-content-center mb-4">
                            <button class="btn btn-outline-secondary w-100" id="shareProductBtn">
                                <i class="fas fa-share-alt me-2"></i> Поділитися
                            </button>
                        </div>
                        
                        <!-- Кнопка добавления в корзину -->
                        <button class="btn btn-primary btn-lg w-100 py-3" id="detailAddToCartBtn">
                            <i class="fas fa-cart-plus me-2"></i> Додати в кошик
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <script>
        // Текущий выбранный товар
        let currentProduct = null;
        let productDetailModal = null;
        
        // Инициализация модальных окон при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            productDetailModal = new bootstrap.Modal(document.getElementById('productDetailModal'));
            
            // Проверяем, есть ли параметр product в URL
            const urlParams = new URLSearchParams(window.location.search);
            const productIdFromUrl = urlParams.get('product');
            
            // Если передан параметр product, открываем модальное окно
            if (productIdFromUrl && !isNaN(productIdFromUrl)) {
                setTimeout(() => {
                    openProductModal(productIdFromUrl, true);
                }, 500); // Небольшая задержка для полной загрузки страницы
            }
            
            // Инициализация нативной кнопки поделиться
            if (navigator.share) {
                document.getElementById('shareProductBtn').addEventListener('click', shareProduct);
            } else {
                // Если нативный Web Share API не поддерживается, показываем альтернативу
                document.getElementById('shareProductBtn').addEventListener('click', function() {
                    showNotification('Функція "Поділитися" доступна тільки на мобільних пристроях', 'info');
                });
            }
            
            // Поиск товаров
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    const productItems = document.querySelectorAll('.product-item');
                    
                    productItems.forEach(item => {
                        const productName = item.dataset.name;
                        if (searchTerm === '' || productName.includes(searchTerm)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
            
            // Кнопка добавления в корзину в детальном модальном окне
            document.getElementById('detailAddToCartBtn').addEventListener('click', function() {
                if (currentProduct) {
                    addToCart(
                        currentProduct.id,
                        currentProduct.name,
                        currentProduct.price
                    );
                    productDetailModal.hide();
                }
            });
            
            // Событие закрытия модального окна деталей товара
            document.getElementById('productDetailModal').addEventListener('hidden.bs.modal', function() {
                // Убираем параметр product из URL при закрытии модального окна
                removeProductParamFromUrl();
                currentProduct = null;
            });
        });
        
        // Открыть модальное окно с деталями товара
        function openProductModal(productId, fromUrl = false) {
            fetch(`/api/product/get.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        currentProduct = {
                            id: product.id,
                            name: product.name,
                            price: parseFloat(product.price)
                        };
                        
                        // Заполняем модальное окно данными
                        const productImg = document.getElementById('detailProductImg');
                        const fallbackImg = '/images/placeholder.jpg';
                        
                        productImg.src = product.photo || fallbackImg;
                        productImg.onerror = function() {
                            this.src = fallbackImg;
                        };
                        
                        document.getElementById('detailProductName').textContent = product.name;
                        document.getElementById('detailProductDescription').textContent = product.description || 'Опис відсутній';
                        document.getElementById('detailProductWeight').textContent = product.weight ? `${product.weight}` : 'Не вказано';
                        document.getElementById('detailProductPrepTime').textContent = product.prep_time ? `${product.prep_time} хв` : 'Не вказано';
                        document.getElementById('detailProductPrice').textContent = `${product.price} грн`;
                        
                        if (product.old_price && parseFloat(product.old_price) > parseFloat(product.price)) {
                            document.getElementById('detailProductOldPrice').style.display = 'block';
                            document.getElementById('detailProductOldPrice').textContent = `${product.old_price} грн`;
                        } else {
                            document.getElementById('detailProductOldPrice').style.display = 'none';
                        }
                        
                        productDetailModal.show();
                        
                        // Если открыто по ссылке, добавляем параметр в URL
                        if (fromUrl) {
                            addProductParamToUrl(productId);
                        }
                        
                        // Выделяем соответствующий товар в списке
                        highlightProductInList(productId);
                        
                    } else {
                        showNotification('Товар не знайдено', 'error');
                        if (fromUrl) {
                            removeProductParamFromUrl();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Помилка завантаження товару', 'error');
                    if (fromUrl) {
                        removeProductParamFromUrl();
                    }
                });
        }
        
        // Закрыть модальное окно товара
        function closeProductModal() {
            productDetailModal.hide();
            removeProductParamFromUrl();
            currentProduct = null;
        }
        
        // Добавить параметр product в URL
        function addProductParamToUrl(productId) {
            const url = new URL(window.location);
            url.searchParams.set('product', productId);
            window.history.replaceState({}, '', url);
        }
        
        // Убрать параметр product из URL
        function removeProductParamFromUrl() {
            const url = new URL(window.location);
            url.searchParams.delete('product');
            window.history.replaceState({}, '', url);
        }
        
        // Выделить товар в списке
        function highlightProductInList(productId) {
            // Убираем выделение со всех товаров
            document.querySelectorAll('.product-card').forEach(card => {
                card.style.boxShadow = '';
                card.style.borderColor = '';
            });
            
            // Находим карточку товара и выделяем её
            const productCard = document.querySelector(`.product-item[data-product-id="${productId}"] .product-card`);
            if (productCard) {
                productCard.style.boxShadow = '0 0 0 3px var(--primary)';
                productCard.style.borderColor = 'var(--primary)';
                
                // Прокручиваем к товару
                productCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        
        // Нативная функция "Поделиться"
        function shareProduct() {
            if (!currentProduct) return;
            
            const shareData = {
                title: currentProduct.name + ' - <?php echo SITE_NAME; ?>',
                text: `Спробуйте "${currentProduct.name}" у <?php echo SITE_NAME; ?>! ${currentProduct.price} грн`,
                url: window.location.origin + '/menu.php?product=' + currentProduct.id
            };
            
            if (navigator.share) {
                navigator.share(shareData)
                    .then(() => {
                        showNotification('Поділено успішно!', 'success');
                    })
                    .catch((error) => {
                        if (error.name !== 'AbortError') {
                            console.error('Помилка при спробі поділитися:', error);
                        }
                    });
            } else {
                // Резервный вариант для десктопов
                const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareData.url)}`;
                window.open(url, '_blank', 'width=600,height=400');
            }
        }
        
        // Функция добавления в корзину
        function addToCart(productId, productName, productPrice) {
            fetch('/api/cart/add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем счетчик в корзине
                    updateCartCount(data.total_items);
                    
                    // Показываем уведомление
                    showNotification(`"${productName}" додано до кошика!`, 'success');
                } else {
                    showNotification('Помилка: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Помилка при додаванні до кошика', 'error');
            });
        }
        
        // Обновление счетчика корзины
        function updateCartCount(count) {
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                cartCount.textContent = count;
            }
        }
        
        // Показ уведомления
        function showNotification(message, type = 'success') {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
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
    </script>

<?php require_once 'footer.php'; ?>