<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/libs/Database.php';
require_once __DIR__ . '/../app/libs/Cart.php';

session_start();

$db = new Database();
$cart = new Cart();

// Получаем категории для меню
$categories = $db->getCategories();

// Получаем популярные товары
$popular_products = $db->getProducts(null, true, 6);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>One Chef - Доставка піци, суші, бургерів у Дніпрі</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #ff6b35;
            --secondary: #ffa500;
            --dark: #333333;
            --light: #f8f9fa;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            padding-top: 76px;
        }
        
        .navbar-brand h1 {
            color: var(--primary);
            font-weight: 700;
            margin: 0;
        }
        
        .promo-banner {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            margin-top: -1px;
        }
        
        .category-card {
            transition: transform 0.3s;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
        }
        
        .product-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
            height: 100%;
        }
        
        .product-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .badge-new {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--primary);
        }
        
        .cart-badge {
            font-size: 0.7em;
            padding: 2px 6px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: #e55a2b;
            border-color: #e55a2b;
        }
        
        .footer {
            background-color: var(--dark);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">
                <h1><i class="fas fa-utensils"></i> One Chef</h1>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="/">Головна</a></li>
                    <li class="nav-item"><a class="nav-link" href="/menu.php">Меню</a></li>
                    <li class="nav-item"><a class="nav-link" href="/delivery.php">Доставка</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contacts.php">Контакти</a></li>
                </ul>
                
                <div class="d-flex">
                    <a href="tel:+380737001987" class="btn btn-outline-primary me-2">
                        <i class="fas fa-phone"></i> +380 73 700 1987
                    </a>
                    <a href="/cart.php" class="btn btn-primary position-relative">
                        <i class="fas fa-shopping-cart"></i> Корзина
                        <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge">
                            <?php echo $cart->getTotalItems(); ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Промо-баннер -->
    <section class="promo-banner py-4 text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2"><i class="fas fa-gift"></i> Самовивіз зі знижкою 10%!</h2>
                    <p class="mb-0">Заберіть замовлення за адресою: проспект Науки, 57а, Дніпро</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="https://www.instagram.com/one_chef_pizza" target="_blank" class="btn btn-light">
                        <i class="fab fa-instagram"></i> @one_chef_pizza
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Категории -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Оберіть категорію</h2>
            <div class="row g-4">
                <?php foreach($categories as $category): ?>
                <div class="col-md-4 col-lg-2">
                    <a href="/menu.php?category=<?php echo $category['id']; ?>" class="category-card text-decoration-none">
                        <div class="card border-0 shadow-sm h-100 text-center">
                            <div class="card-body py-4">
                                <div class="display-4 mb-3"><?php echo htmlspecialchars($category['icon']); ?></div>
                                <h5 class="card-title text-dark"><?php echo htmlspecialchars($category['name']); ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Популярные товары -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Популярні позиції</h2>
            <div class="row g-4">
                <?php foreach($popular_products as $product): ?>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="product-card bg-white">
                        <?php if($product['is_new']): ?>
                        <span class="badge badge-new">Новинка</span>
                        <?php endif; ?>
                        
                        <img src="<?php echo htmlspecialchars($product['photo'] ?: 'https://via.placeholder.com/300x200'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-img">
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text text-muted small"><?php echo htmlspecialchars($product['weight']); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <span class="h5 text-primary"><?php echo number_format($product['price'], 0); ?> грн</span>
                                    <?php if($product['old_price']): ?>
                                    <small class="text-muted text-decoration-line-through ms-2"><?php echo number_format($product['old_price'], 0); ?> грн</small>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-sm btn-outline-primary add-to-cart" 
                                        data-id="<?php echo $product['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                        data-price="<?php echo $product['price']; ?>">
                                    <i class="fas fa-plus"></i> В кошик
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="/menu.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-utensils"></i> Перейти до всього меню
                </a>
            </div>
        </div>
    </section>
    
    <!-- Контакты -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="mb-4">Наші контакти</h3>
                    <div class="mb-3">
                        <h5><i class="fas fa-map-marker-alt text-primary"></i> Адреса</h5>
                        <p>проспект Науки, 57а, Дніпро, Дніпропетровська область</p>
                    </div>
                    <div class="mb-3">
                        <h5><i class="fas fa-phone text-primary"></i> Телефон</h5>
                        <p><a href="tel:+380737001987" class="text-decoration-none">+380 73 700 1987</a></p>
                    </div>
                    <div class="mb-3">
                        <h5><i class="fas fa-clock text-primary"></i> Години роботи</h5>
                        <p>Щодня: 10:00 - 23:00</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="map" style="height: 300px; border-radius: 10px; overflow: hidden;"></div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Футер -->
    <footer class="footer py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h4>One Chef</h4>
                    <p>Доставка смачної їзи у Дніпрі. Піца, суші, бургери та багато іншого.</p>
                </div>
                <div class="col-md-4">
                    <h4>Меню</h4>
                    <ul class="list-unstyled">
                        <li><a href="/menu.php?category=1" class="text-white-50 text-decoration-none">Піца</a></li>
                        <li><a href="/menu.php?category=2" class="text-white-50 text-decoration-none">Суші</a></li>
                        <li><a href="/menu.php?category=3" class="text-white-50 text-decoration-none">Бургери</a></li>
                        <li><a href="/menu.php?category=4" class="text-white-50 text-decoration-none">Салати</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h4>Ми в соцмережах</h4>
                    <div class="mt-3">
                        <a href="https://www.instagram.com/one_chef_pizza" target="_blank" class="text-white me-3">
                            <i class="fab fa-instagram fa-2x"></i>
                        </a>
                        <a href="#" class="text-white me-3">
                            <i class="fab fa-facebook fa-2x"></i>
                        </a>
                        <a href="#" class="text-white">
                            <i class="fab fa-telegram fa-2x"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="bg-white-50">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> One Chef. Всі права захищені.</p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
    
    <script>
        // Инициализация карты
        function initMap() {
            const location = { lat: 48.4647, lng: 35.0462 }; // Координаты Днепра
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: location,
            });
            new google.maps.Marker({
                position: location,
                map: map,
                title: "One Chef - проспект Науки, 57а",
            });
        }
        
        // Добавление в корзину
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.id;
                const productName = this.dataset.name;
                const productPrice = this.dataset.price;
                
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
                    if(data.success) {
                        // Обновляем счетчик корзины
                        document.getElementById('cart-count').textContent = data.total_items;
                        
                        // Показываем уведомление
                        showNotification(`"${productName}" додано до кошика!`, 'success');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Помилка при додаванні до кошика', 'error');
                });
            });
        });
        
        // Функция показа уведомления
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
        
        // Если нет API ключа Google Maps, показываем статичную картинку
        if(!window.google) {
            document.getElementById('map').innerHTML = 
                '<img src="https://maps.googleapis.com/maps/api/staticmap?center=48.4647,35.0462&zoom=15&size=600x300&markers=color:red%7C48.4647,35.0462&key=YOUR_API_KEY" style="width:100%;height:100%;object-fit:cover;">';
        }
    </script>
</body>
</html>