<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$appointmentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($appointmentId <= 0) {
    exit('Запись не найдена или недоступна.');
}

if (is_admin()) {
    $sql = "
        SELECT a.*, u.email, c.brand, c.model, c.year, c.plate_number, s.title AS service_title,
               s.description AS service_description, s.duration_minutes, b.name AS box_name
        FROM appointments a
        JOIN users u ON u.id = a.user_id
        JOIN cars c ON c.id = a.car_id
        JOIN services s ON s.id = a.service_id
        JOIN service_boxes b ON b.id = a.box_id
        WHERE a.id = ?
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$appointmentId]);
} else {
    $sql = "
        SELECT a.*, u.email, c.brand, c.model, c.year, c.plate_number, s.title AS service_title,
               s.description AS service_description, s.duration_minutes, b.name AS box_name
        FROM appointments a
        JOIN users u ON u.id = a.user_id
        JOIN cars c ON c.id = a.car_id
        JOIN services s ON s.id = a.service_id
        JOIN service_boxes b ON b.id = a.box_id
        WHERE a.id = ? AND a.user_id = ?
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$appointmentId, $_SESSION['user_id']]);
}

$appointment = $stmt->fetch();
if (!$appointment) {
    exit('Запись не найдена или недоступна.');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали записи</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/nav.php'; ?>
<div class="container pb-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">Запись #<?= (int) $appointment['id'] ?></h1>
            <a href="<?= is_admin() ? 'admin_appointments.php' : 'profile.php' ?>" class="btn btn-outline-secondary btn-sm">Назад</a>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <h2 class="h5">Основная информация</h2>
                    <p><strong>Клиент:</strong> <?= h($appointment['email']) ?></p>
                    <p><strong>Дата записи:</strong> <?= h(date('d.m.Y H:i', strtotime($appointment['appointment_datetime']))) ?></p>
                    <p><strong>Статус:</strong> <?= h($appointment['status']) ?></p>
                    <p><strong>Стоимость:</strong> <?= number_format((float) $appointment['total_price'], 2, '.', ' ') ?> ₽</p>
                    <p><strong>Бокс:</strong> <?= h($appointment['box_name']) ?></p>
                </div>
                <div class="col-md-6">
                    <h2 class="h5">Автомобиль и услуга</h2>
                    <p><strong>Автомобиль:</strong> <?= h($appointment['brand'] . ' ' . $appointment['model']) ?></p>
                    <p><strong>Год:</strong> <?= (int) $appointment['year'] ?></p>
                    <p><strong>Номер:</strong> <?= h($appointment['plate_number']) ?></p>
                    <p><strong>Услуга:</strong> <?= h($appointment['service_title']) ?></p>
                    <p><strong>Длительность:</strong> <?= (int) $appointment['duration_minutes'] ?> мин.</p>
                </div>
            </div>
            <hr>
            <h2 class="h5">Описание услуги</h2>
            <p><?= nl2br(h($appointment['service_description'])) ?></p>
            <h2 class="h5 mt-4">Комментарий клиента</h2>
            <p><?= $appointment['notes'] ? nl2br(h($appointment['notes'])) : 'Комментарий не указан.' ?></p>
        </div>
    </div>
</div>
</body>
</html>
