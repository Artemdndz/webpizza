</main> <!-- ЗАКРЫВАЕМ main контейнер здесь -->
    
    <footer class="footer py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h4 class="h5 mb-2"><?php echo SITE_NAME; ?></h4>
                    <p class="text-secondary mb-0">Доставка смачної їжі у Дніпрі. Піца, суші, бургери та багато іншого.</p>
                </div>
                
                <div class="col-md-6 text-md-end">
                    <div class="mb-3">
                        <a href="https://www.instagram.com/one_chef_pizza" target="_blank" class="social-icon" title="Instagram">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                        <a href="#" class="social-icon" title="Facebook">
                            <i class="fab fa-facebook fa-lg"></i>
                        </a>
                        <a href="#" class="social-icon" title="Telegram">
                            <i class="fab fa-telegram fa-lg"></i>
                        </a>
                    </div>
                    <p class="text-secondary mb-0 small">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Всі права захищені.</p>
                </div>
            </div>
        </div>
    </footer>

    
	
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if (defined('GMAPS_API_KEY') && !empty(GMAPS_API_KEY)): ?>
    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GMAPS_API_KEY; ?>&libraries=places&language=uk&region=UA&callback=initMaps" async defer></script>
    <?php endif; ?>
    
    <script>
        // Устанавливаем тёмную тему навсегда
        document.documentElement.setAttribute('data-theme', 'dark');
        
        // Инициализация Google Maps (для геокодирования)
        function initMaps() {
            console.log('Google Maps API завантажено');
            // Автозаполнение будет инициализировано в cart.php при необходимости
        }
        
// Функция для добавления в корзину (без добавок)
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
    .then(response => {
        if (!response.ok) {
            throw new Error('Помилка сервера: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if(data.success) {
            // Обновляем счетчик корзины
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                cartCount.textContent = data.total_items;
            }
            
            // Показываем уведомление
            showNotification(`"${productName}" додано до кошика!`, 'success');
        } else {
            showNotification('Помилка: ' + (data.message || 'невідома помилка'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Помилка при додаванні до кошика: ' + error.message, 'error');
    });
}
        
        // Глобальная функция уведомлений
        function showNotification(message, type = 'success') {
            // Создаем контейнер для уведомлений, если его нет
            let container = document.getElementById('notifications-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'notifications-container';
                container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
                document.body.appendChild(container);
            }
            
            const alert = document.createElement('div');
            alert.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show`;
            alert.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
                    <div>${message}</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            `;
            container.appendChild(alert);
            
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 3000);
        }
        
        // Инициализация карты (если есть элемент с id="map")
        function initMap() {
            const mapElement = document.getElementById('map');
            if (!mapElement || !window.google) return;
            
            const location = { lat: 48.4647, lng: 35.0462 }; // Координаты Днепра
            const map = new google.maps.Map(mapElement, {
                zoom: 15,
                center: location,
                styles: [
                    { elementType: "geometry", stylers: [{ color: "#1a1a1a" }] },
                    { elementType: "labels.text.stroke", stylers: [{ color: "#1a1a1a" }] },
                    { elementType: "labels.text.fill", stylers: [{ color: "#757575" }] },
                    { elementType: "labels.icon", stylers: [{ visibility: "off" }] },
                    {
                        featureType: "water",
                        elementType: "geometry",
                        stylers: [{ color: "#17263c" }]
                    }
                ]
            });
            
            new google.maps.Marker({
                position: location,
                map: map,
                title: "<?php echo SITE_NAME; ?> - <?php echo SITE_ADDRESS; ?>",
                icon: {
                    url: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
                }
            });
        }
        
        // Если нет Google Maps API, показываем статичную информацию
        document.addEventListener('DOMContentLoaded', function() {
            const mapElement = document.getElementById('map');
            if (mapElement && !window.google) {
                mapElement.innerHTML = `
                    <div class="h-100 w-100 bg-dark d-flex flex-column justify-content-center align-items-center text-center p-3">
                        <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                        <h5 class="mb-2"><?php echo SITE_NAME; ?></h5>
                        <p class="mb-0"><?php echo SITE_ADDRESS; ?></p>
                        <p class="text-muted small mt-2">Дніпро</p>
                    </div>
                `;
            }
            
            // Инициализация счетчика корзины
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                // Можно добавить AJAX запрос для получения актуального количества
                // Или оставить как есть, если счетчик обновляется через addToCart
            }
        });
        
        // Плавная прокрутка для якорей (опционально)
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId !== '#' && targetId.startsWith('#')) {
                    e.preventDefault();
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });
    </script>
	
	
</body>
</html>