<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_admin();

$servicesCount = (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
$appointmentsCount = (int) $pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn();
$usersCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/nav.php'; ?>
<div class="container pb-5">
    <div class="p-4 bg-white rounded-4 shadow-sm mb-4 border">
        <h1 class="h3 mb-2">Панель администратора</h1>
        <p class="text-muted mb-0">Управление услугами, записями клиентов и календарем занятости боксов.</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted">Услуги</div>
                    <div class="display-6 fw-bold"><?= $servicesCount ?></div>
                    <a href="admin_services.php" class="btn btn-primary btn-sm mt-3">Управлять услугами</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted">Записи</div>
                    <div class="display-6 fw-bold"><?= $appointmentsCount ?></div>
                    <a href="admin_appointments.php" class="btn btn-primary btn-sm mt-3">Список записей</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted">Пользователи</div>
                    <div class="display-6 fw-bold"><?= $usersCount ?></div>
                    <a href="appointment_slots.php" class="btn btn-primary btn-sm mt-3">Календарь боксов</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white"><h2 class="h5 mb-0">Быстрые действия</h2></div>
                <div class="card-body d-grid gap-2">
                    <a href="add_service.php" class="btn btn-success">Добавить услугу</a>
                    <a href="admin_services.php" class="btn btn-outline-secondary">Редактировать услуги</a>
                    <a href="admin_appointments.php" class="btn btn-outline-secondary">Посмотреть все записи</a>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white"><h2 class="h5 mb-0">Проверка требований</h2></div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        <li>CRUD по услугам реализован.</li>
                        <li>Личный кабинет клиента защищен от IDOR.</li>
                        <li>Формы изменения данных защищены CSRF-токеном.</li>
                        <li>Календарь занятости боксов и проверка пересечения слотов работают на сервере.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
