<?php
session_start();
require_once '../../src/config.php';
require_once '../../src/Database.php';

$db = Database::getInstance();

// Загрузка фото
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $category_id = $_POST['category_id'];
    $upload_dir = '../uploads/categories/';
    
    // Создаем папку если не существует
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($_FILES['photo']['name']);
    $target_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
        // Сохраняем путь в БД
        $stmt = $db->getConnection()->prepare(
            "UPDATE categories SET photo = ? WHERE id = ?"
        );
        $stmt->execute(['/uploads/categories/' . $file_name, $category_id]);
        
        $message = "Фото успішно завантажено!";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <title>Завантаження фото категорій</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Завантаження фото для категорій</h2>
        
        <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Категорія:</label>
                <select name="category_id" class="form-control" required>
                    <option value="">Оберіть категорію</option>
                    <?php 
                    $categories = $db->getConnection()->query(
                        "SELECT id, name FROM categories ORDER BY name"
                    )->fetchAll();
                    
                    foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label>Фото (рекомендовано 400x400px):</label>
                <input type="file" name="photo" class="form-control" accept="image/*" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Завантажити</button>
        </form>
    </div>
</body>
</html>