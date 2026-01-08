<?php require_once 'header.php'; ?>

        <!-- Заголовок -->
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold mb-3">Ласкаво просимо до <span class="text-primary"><?php echo SITE_NAME; ?></span></h1>
            <p class="lead text-secondary">Доставка смачної піци, суші та бургерів у Дніпрі</p>
        </div>
        
        <!-- Категории -->
        <section class="mb-5">
            <h2 class="h3 text-center mb-4">Наше меню</h2>
            <div class="row g-4">
                <?php 
                // Выбираем только первые 3 категории для главной
                $main_categories = array_slice($categories, 0, 9); 
                foreach($main_categories as $category): 
                ?>
                <div class="col-md-4">
                    <a href="/menu.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                        <div class="category-card h-100 text-center p-4 fade-in">
                            <div class="category-icon">
    <?php 
    if (!empty($category['photo'])) {
        echo '<img src="' . htmlspecialchars($category['photo']) . '" 
                   alt="' . htmlspecialchars($category['name']) . '"
                   class="category-image">';
    } else {
        echo '<div class="category-emoji">' . htmlspecialchars($category['icon']) . '</div>';
    }
    ?>
</div>
                            <h3 class="h4 mb-2"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="text-secondary mb-3">
                                <?php echo !empty($category['description']) 
                                    ? htmlspecialchars($category['description']) 
                                    : 'Смачні страви з найсвіжішими інгредієнтами'; ?>
                            </p>
                            <span class="btn btn-outline-primary">Дивитись меню</span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="/menu.php" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-utensils me-2"></i>Перейти до всього меню
                </a>
            </div>
        </section>
        
        <!-- Популярные товары -->
        <?php 
        $popular_products = $db->getProducts(null, true, 6);
        if (!empty($popular_products)): 
        ?>
        <section class="mb-5">
            <h2 class="h3 text-center mb-4">Популярні позиції</h2>
            <div class="row g-4">
                <?php foreach($popular_products as $product): ?>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="product-card h-100 position-relative">
                        <?php if($product['is_new']): ?>
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2">Новинка</span>
                        <?php endif; ?>
                        
                        <div class="product-img-wrapper">
                            <img src="<?php echo htmlspecialchars($product['photo'] ?: '/images/placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-img w-100">
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($product['weight']); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div>
                                    <span class="h5 text-primary"><?php echo number_format($product['price'], 0); ?> грн</span>
                                    <?php if($product['old_price']): ?>
                                    <small class="text-muted text-decoration-line-through ms-2"><?php echo number_format($product['old_price'], 0); ?> грн</small>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="addToCart(
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
        </section>
        <?php endif; ?>
        
        <!-- Контакты и информация -->
        <div class="row mt-5">
            <div class="col-lg-6 mb-4">
                <h3 class="h4 mb-3"><i class="fas fa-map-marker-alt text-primary me-2"></i>Контакти</h3>
                <div class="contact-card fade-in">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-map-marker-alt contact-icon"></i>
                        <div>
                            <strong>Адреса</strong>
                            <p class="mb-0"><?php echo SITE_ADDRESS; ?></p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-phone contact-icon"></i>
                        <div>
                            <strong>Телефон</strong>
                            <p class="mb-0">
                                <a href="tel:<?php echo SITE_PHONE; ?>" class="text-decoration-none text-primary">
                                    <?php echo SITE_PHONE; ?>
                                </a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock contact-icon"></i>
                        <div>
                            <strong>Години роботи</strong>
                                    <p class="mb-0">
            <?php echo WORKING_DAYS; ?>: 
            <?php echo WORKING_HOURS_START; ?> - <?php echo WORKING_HOURS_END; ?>
        </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <h3 class="h4 mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Інформація</h3>
                <div class="delivery-info">
                    <div class="d-flex align-items-center mb-3">
                        <span class="discount-badge me-3">-<?php echo PICKUP_DISCOUNT; ?>%</span>
                        <div>
                            <strong>Самовивіз зі знижкою</strong>
                            <p class="mb-0 text-secondary">Заберіть замовлення самостійно та отримайте знижку</p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <p><i class="fas fa-check-circle text-primary me-2"></i>Мінімальне замовлення: <strong>200 грн</strong></p>
                        <p><i class="fas fa-check-circle text-primary me-2"></i>Безкоштовна доставка: <strong>від 400 грн</strong></p>
                        <p><i class="fas fa-check-circle text-primary me-2"></i>Час доставки: <strong>60-90 хв</strong></p>
                        <p><i class="fas fa-check-circle text-primary me-2"></i>Оплата: <strong>готівка/карта</strong></p>
                    </div>
                </div>
            </div>
        </div>

<?php 
// ЗАКРЫВАЕМ main контейнер в footer.php
require_once 'footer.php'; 
?>