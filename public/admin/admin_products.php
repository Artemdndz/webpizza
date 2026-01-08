<?php
session_start();
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/Database.php';


$db = Database::getInstance();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 
              (isset($_GET['product_id']) ? intval($_GET['product_id']) : 0);

// Функция для безопасного вывода
function safe_html($value) {
    if ($value === null) {
        return '';
    }
    return htmlspecialchars($value);
}

// Загрузка товара для редактирования
$edit_product = null;
if ($action === 'edit' && $product_id > 0) {
    $edit_product = $db->getProductById($product_id);
}

// Обработка добавления/редактирования товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add_product', 'edit_product'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $old_price = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;
    $weight = trim($_POST['weight'] ?? '');
    $prep_time = intval($_POST['prep_time'] ?? 30);
    $category_id = intval($_POST['category_id'] ?? 1);
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    $active = isset($_POST['active']) ? 1 : 0;
    $pos_id = trim($_POST['pos_id'] ?? '');
    $priority = intval($_POST['priority'] ?? 100);
    
    $photo = null;
    
    // Обработка загрузки фото
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_extensions)) {
            // Публичная директория для загрузки
            $upload_dir = __DIR__ . '/../../public/uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $filepath)) {
                $photo = '/uploads/products/' . $filename;
                // Удаляем старое фото если оно есть и было загружено новое
                if ($action === 'edit_product' && !empty($_POST['old_photo'])) {
                    $old_photo_path = __DIR__ . '/../../public' . $_POST['old_photo'];
                    if (file_exists($old_photo_path) && is_file($old_photo_path)) {
                        unlink($old_photo_path);
                    }
                }
            }
        }
    } elseif ($action === 'edit_product' && !empty($_POST['old_photo'])) {
        // Сохраняем старое фото если новое не загружено
        $photo = $_POST['old_photo'];
    }
    
    // Обработка удаления фото через чекбокс
    if ($action === 'edit_product' && isset($_POST['delete_photo']) && $_POST['delete_photo'] == 1) {
        if (!empty($_POST['old_photo'])) {
            $old_photo_path = __DIR__ . '/../../public' . $_POST['old_photo'];
            if (file_exists($old_photo_path) && is_file($old_photo_path)) {
                unlink($old_photo_path);
            }
        }
        $photo = null;
    }
    
    // Валидация
    $errors = [];
    if (empty($name)) {
        $errors[] = "Назва товару обов'язкова";
    }
    if ($price <= 0) {
        $errors[] = "Ціна повинна бути більше 0";
    }
    
    if (empty($errors)) {
        $product_data = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'old_price' => $old_price,
            'weight' => $weight,
            'prep_time' => $prep_time,
            'category_id' => $category_id,
            'is_new' => $is_new,
            'is_popular' => $is_popular,
            'active' => $active,
            'pos_id' => $pos_id,
            'priority' => $priority,
            'photo' => $photo
        ];
        
        if ($action === 'add_product') {
            $result = $db->addProduct($product_data);
            if ($result) {
                $success = "Товар успішно додано!";
                $_POST = [];
            } else {
                $error = "Помилка при додаванні товару";
            }
        } elseif ($action === 'edit_product') {
            $result = $db->updateProduct($product_id, $product_data);
            if ($result) {
                $success = "Товар успішно оновлено!";
                $edit_product = $db->getProductById($product_id); // Обновляем данные
            } else {
                $error = "Помилка при оновленні товару";
            }
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// Обработка удаления товара
if ($action === 'delete' && $product_id > 0) {
    if ($db->deleteProduct($product_id)) {
        $success = "Товар успішно видалено!";
    } else {
        $error = "Помилка при видаленні товару";
    }
}

// Получаем данные
$categories = $db->getCategories();
$products = $db->getAllProducts();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управління товарами - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #1a1a1a; color: #fff; }
        .admin-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .product-img-preview { max-width: 200px; max-height: 200px; object-fit: cover; }
        .table-dark { --bs-table-bg: #2a2a2a; }
        .form-control, .form-select { background-color: #333; color: #fff; border-color: #555; }
        .form-control:focus, .form-select:focus { background-color: #444; color: #fff; border-color: #ff6b35; }
        .badge { font-size: 0.85em; }
        .action-buttons { white-space: nowrap; }
        .modal-content { background-color: #2a2a2a; }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1 class="mb-4"><i class="fas fa-hamburger"></i> Управління товарами</h1>
        
        <!-- Уведомления -->
        <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $success; ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error; ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Форма добавления/редактирования товара -->
            <div class="col-md-5 mb-4">
                <div class="card bg-dark border-secondary sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary">
                        <h5 class="mb-0">
                            <i class="fas <?php echo $edit_product ? 'fa-edit' : 'fa-plus-circle'; ?>"></i> 
                            <?php echo $edit_product ? 'Редагувати товар' : 'Додати новий товар'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="productForm">
                            <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit_product' : 'add_product'; ?>">
                            <?php if ($edit_product): ?>
                            <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                            <input type="hidden" name="old_photo" value="<?php echo safe_html($edit_product['photo'] ?? ''); ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Назва товару *</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?php echo safe_html($edit_product['name'] ?? $_POST['name'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">POS ID (ID в касі)</label>
                                <input type="text" name="pos_id" class="form-control" 
                                       placeholder="Наприклад: PIZZA_001"
                                       value="<?php echo safe_html($edit_product['pos_id'] ?? $_POST['pos_id'] ?? ''); ?>">
                                <small class="text-muted">Ідентифікатор товару в касовій системі (необов'язково)</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Опис</label>
                                <textarea name="description" class="form-control" rows="2"><?php echo safe_html($edit_product['description'] ?? $_POST['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ціна (грн) *</label>
                                    <input type="number" name="price" class="form-control" step="0.01" min="0.01" required
                                           value="<?php echo safe_html($edit_product['price'] ?? $_POST['price'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Стара ціна (грн)</label>
                                    <input type="number" name="old_price" class="form-control" step="0.01" min="0"
                                           value="<?php echo safe_html($edit_product['old_price'] ?? $_POST['old_price'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Вага</label>
                                    <input type="text" name="weight" class="form-control" placeholder="300 г, 500 мл"
                                           value="<?php echo safe_html($edit_product['weight'] ?? $_POST['weight'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Час приготування (хв)</label>
                                    <input type="number" name="prep_time" class="form-control" 
                                           value="<?php echo safe_html($edit_product['prep_time'] ?? $_POST['prep_time'] ?? '30'); ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Категорія</label>
                                    <select name="category_id" class="form-select">
                                        <?php foreach($categories as $category): 
                                            $selected = ($edit_product['category_id'] ?? $_POST['category_id'] ?? 1) == $category['id'];
                                        ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $selected ? 'selected' : ''; ?>>
                                            <?php echo safe_html($category['icon'] . ' ' . $category['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Пріоритет</label>
                                    <input type="number" name="priority" class="form-control" 
                                           value="<?php echo safe_html($edit_product['priority'] ?? $_POST['priority'] ?? '100'); ?>">
                                    <small class="text-muted">Менше число = вище в списку</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Фото товару</label>
                                <?php if ($edit_product && !empty($edit_product['photo'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo safe_html($edit_product['photo']); ?>" 
                                         class="product-img-preview rounded border border-secondary"
                                         alt="Поточне фото">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="delete_photo" id="delete_photo" value="1">
                                        <label class="form-check-label" for="delete_photo">Видалити фото</label>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <input type="file" name="photo" class="form-control" accept="image/*">
                                <small class="text-muted">Допустимі формати: JPG, PNG, GIF, WebP</small>
                                <div id="imagePreview" class="mt-2"></div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_new" id="is_new" value="1"
                                                   <?php echo ($edit_product['is_new'] ?? $_POST['is_new'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_new">Новинка</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_popular" id="is_popular" value="1"
                                                   <?php echo ($edit_product['is_popular'] ?? $_POST['is_popular'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_popular">Популярне</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                                                   <?php echo ($edit_product['active'] ?? $_POST['active'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="active">Активний</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> 
                                    <?php echo $edit_product ? 'Оновити товар' : 'Додати товар'; ?>
                                </button>
                                
                                <?php if ($edit_product): ?>
                                <a href="admin_products.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Скасувати редагування
                                </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Список товаров -->
            <div class="col-md-7">
                <div class="card bg-dark border-secondary">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Всі товари (<?php echo count($products); ?>)</h5>
                        <a href="admin_products.php?action=export" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-download"></i> Експорт
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-dark table-hover mb-0">
                                <thead class="sticky-top" style="top: 0; z-index: 1;">
                                    <tr>
                                        <th>ID</th>
                                        <th>Фото</th>
                                        <th>Назва</th>
                                        <th>Ціна</th>
                                        <th>POS ID</th>
                                        <th>Статус</th>
                                        <th class="text-end">Дії</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">
                                            <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                            Товари відсутні
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach($products as $product): ?>
                                    <tr class="<?php echo $edit_product && $edit_product['id'] == $product['id'] ? 'table-info' : ''; ?>">
                                        <td><?php echo $product['id']; ?></td>
                                        <td>
                                            <?php if(!empty($product['photo'])): ?>
                                            <img src="<?php echo safe_html($product['photo']); ?>" 
                                                 alt="<?php echo safe_html($product['name']); ?>" 
                                                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                            <i class="fas fa-image text-muted"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo safe_html($product['name']); ?></strong>
                                                <div class="small text-muted">
                                                    <?php echo safe_html($product['description']); ?>
                                                </div>
                                            </div>
                                            <div class="mt-1">
                                                <?php if($product['is_new']): ?>
                                                <span class="badge bg-danger me-1">Новинка</span>
                                                <?php endif; ?>
                                                <?php if($product['is_popular']): ?>
                                                <span class="badge bg-warning me-1">Популярне</span>
                                                <?php endif; ?>
                                                <span class="badge bg-secondary"><?php echo $product['priority']; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-primary fw-bold"><?php echo number_format($product['price'], 0); ?> грн</div>
                                            <?php if($product['old_price']): ?>
                                            <div class="small text-decoration-line-through text-muted">
                                                <?php echo number_format($product['old_price'], 0); ?> грн
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!empty($product['pos_id'])): ?>
                                            <code class="bg-dark px-2 py-1 rounded"><?php echo safe_html($product['pos_id']); ?></code>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($product['active']): ?>
                                            <span class="badge bg-success">Активний</span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Неактивний</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end action-buttons">
                                            <a href="admin_products.php?action=edit&product_id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-outline-warning me-1"
                                               title="Редагувати">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal"
                                                    data-product-id="<?php echo $product['id']; ?>"
                                                    data-product-name="<?php echo safe_html($product['name']); ?>"
                                                    title="Видалити">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <a href="/" class="btn btn-outline-secondary me-2">
                <i class="fas fa-home"></i> На сайт
            </a>
            <a href="logout.php" class="btn btn-outline-danger">
                <i class="fas fa-sign-out-alt"></i> Вийти
            </a>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div class="modal fade" id="deleteModal" tabindex="-1" data-bs-theme="dark">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger border-danger">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Підтвердження видалення</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Ви дійсно хочете видалити товар "<span id="deleteProductName"></span>"?</p>
                    <p class="text-warning"><small>Цю дію неможливо скасувати!</small></p>
                </div>
                <div class="modal-footer border-danger">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" id="deleteProductId">
                        <button type="submit" class="btn btn-danger">Видалити</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Предпросмотр изображения
        document.querySelector('input[name="photo"]')?.addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            if (!preview) return;
            
            preview.innerHTML = '';
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'product-img-preview rounded border border-secondary';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Модальное окно удаления
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const productId = button.getAttribute('data-product-id');
                const productName = button.getAttribute('data-product-name');
                
                document.getElementById('deleteProductId').value = productId;
                document.getElementById('deleteProductName').textContent = productName;
            });
        }
        
        // Прокрутка к форме при редактировании
        <?php if ($edit_product): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const formCard = document.querySelector('.card.bg-dark');
            if (formCard) {
                formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
        <?php endif; ?>
        
        // Подтверждение перед удалением фото
        document.querySelector('input[name="delete_photo"]')?.addEventListener('change', function() {
            if (this.checked) {
                if (!confirm('Ви дійсно хочете видалити фото товару?')) {
                    this.checked = false;
                }
            }
        });
    </script>
</body>
</html>