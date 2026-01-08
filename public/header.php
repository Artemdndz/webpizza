<?php
require_once __DIR__ . '/../src/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Cart.php';

$db = Database::getInstance();
$cart = new Cart();
$categories = $db->getCategories();
?>
<!DOCTYPE html>
<html lang="uk" data-theme="dark">
<head>
<script>
    // Константы сайта для использования в JavaScript
    const SITE_NAME = '<?php echo SITE_NAME; ?>';
    const SITE_URL = '<?php echo SITE_URL; ?>';
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Доставка піци, суші, бургерів у Дніпрі</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    <style>
        /* Стили для эмодзи в навигации */
        .nav-emoji {
            font-size: 1.2em;
            line-height: 1;
            display: inline-block;
            min-width: 24px;
            text-align: center;
        }
        
        /* На десктопе эмодзи поменьше */
        @media (min-width: 992px) {
            .nav-emoji {
                font-size: 1em;
                min-width: 20px;
            }
        }
        
        /* Стили для логотипа */
        .navbar-logo {
            height: 45px;
            width: auto;
            max-height: 45px;
            object-fit: contain;
            transition: var(--transition);
        }
        
        .navbar-logo:hover {
            transform: scale(1.05);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            padding: 0;
        }
        
        /* На мобильных уменьшаем лого */
        @media (max-width: 768px) {
            .navbar-logo {
                height: 38px;
                max-height: 38px;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-logo {
                height: 35px;
                max-height: 35px;
            }
        }
        
        /* Делаем логотип более заметным на темном фоне */
        .navbar-logo {
            filter: brightness(1.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <!-- Бренд - ТОЛЬКО ЛОГОТИП -->
            <a class="navbar-brand" href="/" title="<?php echo SITE_NAME; ?>">
                <!-- Логотип -->
                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/logo.webp')): ?>
                <img src="/logo.webp" alt="<?php echo SITE_NAME; ?>" class="navbar-logo">
                <?php else: ?>
                <!-- Запасной вариант если лого нет -->
                <span class="navbar-brand-text">
                    <?php echo SITE_NAME; ?>
                </span>
                <?php endif; ?>
            </a>
            
            <!-- Кнопка для мобильных -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCategories">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Контакты и корзина (справа) - ВНЕ коллапсируемой части -->
            <div class="d-flex align-items-center order-lg-3 ms-auto ms-lg-0">
                <!-- Телефон на десктопе -->
                <a href="tel:<?php echo SITE_PHONE; ?>" class="btn btn-outline-primary me-2 d-none d-md-inline-flex">
                    <i class="fas fa-phone"></i>
                    <span class="ms-2 d-none d-lg-inline"><?php echo SITE_PHONE; ?></span>
                </a>
                
                <!-- Телефон на мобильных (только иконка) -->
                <a href="tel:<?php echo SITE_PHONE; ?>" class="btn btn-outline-primary me-2 d-md-none" title="Зателефонувати">
                    <i class="fas fa-phone"></i>
                </a>
                
                <!-- Корзина (всегда видима) -->
                <a href="/cart.php" class="btn btn-primary position-relative">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill"><?php echo $cart->getTotalItems(); ?></span>
                </a>
            </div>
            
            <!-- КАТЕГОРИИ МЕНЮ (только категории внутри коллапса) -->
            <div class="collapse navbar-collapse order-lg-2" id="navbarCategories">
                <ul class="navbar-nav mx-auto">
                    <?php foreach($categories as $category): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center" href="/menu.php?category=<?php echo $category['id']; ?>" 
                           title="<?php echo htmlspecialchars($category['name']); ?>">
                            <!-- Эмодзи категории из базы данных -->
                            <?php if (!empty($category['icon'])): ?>
                            <span class="nav-emoji me-2">
                                <?php echo htmlspecialchars($category['icon']); ?>
                            </span>
                            <?php else: ?>
                            <!-- Эмодзи по умолчанию если нет в базе -->
                            <span class="nav-emoji me-2">
                                🍽️
                            </span>
                            <?php endif; ?>
                            
                            <!-- Текст категории (на десктопе) -->
                            <span class="d-none d-lg-inline">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </span>
                            
                            <!-- Текст категории (на мобильных) -->
                            <span class="d-inline d-lg-none">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    
                    <!-- Дополнительные ссылки (только на мобильных) -->
                    <li class="nav-item d-lg-none">
                        <hr class="dropdown-divider my-2">
                    </li>
                    <li class="nav-item d-lg-none">
                        <a class="nav-link d-flex align-items-center" href="/delivery.php">
                            <i class="fas fa-truck me-2"></i> Доставка та оплата
                        </a>
                    </li>
                    <li class="nav-item d-lg-none">
                        <a class="nav-link d-flex align-items-center" href="/contacts.php">
                            <i class="fas fa-map-marker-alt me-2"></i> Контакти
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Промо-баннер -->
    <section class="promo-banner py-3 text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="h4 mb-1"><i class="fas fa-gift me-2"></i>Самовивіз зі знижкою <?php echo PICKUP_DISCOUNT; ?>%!</h2>
                    <p class="mb-0 opacity-75"><?php echo SITE_ADDRESS; ?></p>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <a href="https://www.instagram.com/one_chef_pizza" target="_blank" class="btn btn-outline-light btn-sm">
                        <i class="fab fa-instagram me-1"></i> @one_chef_pizza
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- ОСНОВНОЙ КОНТЕНТ -->
    <main class="container py-5 fade-in">