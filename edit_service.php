<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT * FROM services WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) {
    exit('Услуга не найдена.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $basePrice = (float) ($_POST['base_price'] ?? 0);
    $durationMinutes = (int) ($_POST['duration_minutes'] ?? 0);
    $imageUrl = trim($_POST['image_url'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($title === '' || $basePrice < 0 || $durationMinutes <= 0) {
        $message = '<div class="alert alert-danger">Заполните форму корректно.</div>';
    } else {
        $update = $pdo->prepare('UPDATE services SET title = ?, description = ?, base_price = ?, duration_minutes = ?, image_url = ?, is_active = ? WHERE id = ?');
        $update->execute([
            $title,
            $description !== '' ? $description : null,
            $basePrice,
            $durationMinutes,
            $imageUrl !== '' ? $imageUrl : null,
            $isActive,
            $id,
        ]);
        set_flash('success', 'Услуга обновлена.');
        redirect_to('admin_services.php');
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование услуги</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/nav.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning"><h1 class="h4 mb-0">Редактирование услуги</h1></div>
                <div class="card-body">
                    <?= $message ?>
                    <form method="POST">
                        <?= csrf_input() ?>
                        <div class="mb-3">
                            <label class="form-label">Название</label>
                            <input type="text" name="title" class="form-control" required value="<?= h($_POST['title'] ?? $service['title']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Описание</label>
                            <textarea name="description" class="form-control" rows="4"><?= h($_POST['description'] ?? $service['description']) ?></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Базовая цена</label>
                                <input type="number" name="base_price" class="form-control" required min="0" step="0.01" value="<?= h($_POST['base_price'] ?? $service['base_price']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Длительность, минут</label>
                                <input type="number" name="duration_minutes" class="form-control" required min="1" step="1" value="<?= h($_POST['duration_minutes'] ?? $service['duration_minutes']) ?>">
                            </div>
                        </div>
                        <div class="mt-3 mb-3">
                            <label class="form-label">URL изображения</label>
                            <input type="text" name="image_url" class="form-control" value="<?= h($_POST['image_url'] ?? $service['image_url']) ?>">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= (isset($_POST['is_active']) || (!$_POST && $service['is_active'])) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Показывать услугу на главной</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">Обновить</button>
                            <a href="admin_services.php" class="btn btn-outline-secondary">Назад</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
