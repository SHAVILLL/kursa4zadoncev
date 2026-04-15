<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$limit = 10;
$offset = ($page - 1) * $limit;

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE user_id = ?');
$countStmt->execute([$_SESSION['user_id']]);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $limit));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$sql = "
    SELECT
        a.id,
        a.appointment_datetime,
        a.total_price,
        a.status,
        a.notes,
        a.created_at,
        c.brand,
        c.model,
        c.plate_number,
        s.title AS service_title,
        s.duration_minutes,
        b.name AS box_name
    FROM appointments a
    JOIN cars c ON c.id = a.car_id
    JOIN services s ON s.id = a.service_id
    JOIN service_boxes b ON b.id = a.box_id
    WHERE a.user_id = :user_id
    ORDER BY a.appointment_datetime DESC
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$appointments = $stmt->fetchAll();

$userStmt = $pdo->prepare('SELECT username, email, phone, created_at FROM users WHERE id = ? LIMIT 1');
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

$carsStmt = $pdo->prepare('SELECT * FROM cars WHERE user_id = ? ORDER BY id DESC');
$carsStmt->execute([$_SESSION['user_id']]);
$cars = $carsStmt->fetchAll();

$flash = get_flash();

function appointment_badge_class($status)
{
    switch ($status) {
        case 'new':
            return 'primary';
        case 'confirmed':
            return 'info';
        case 'in_progress':
            return 'warning';
        case 'done':
            return 'success';
        case 'canceled':
            return 'secondary';
        default:
            return 'dark';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/nav.php'; ?>
<div class="container pb-5">
    <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?>"><?= $flash['message'] ?></div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white"><h1 class="h5 mb-0">Данные профиля</h1></div>
                <div class="card-body">
                    <p><strong>Имя:</strong> <?= h($user['username'] ?: 'Не указано') ?></p>
                    <p><strong>Email:</strong> <?= h($user['email']) ?></p>
                    <p><strong>Телефон:</strong> <?= h($user['phone'] ?: 'Не указан') ?></p>
                    <p class="mb-4"><strong>Регистрация:</strong> <?= h(date('d.m.Y H:i', strtotime($user['created_at']))) ?></p>
                    <div class="d-grid gap-2">
                        <a href="update_profile.php" class="btn btn-outline-primary">Редактировать профиль</a>
                        <a href="change_password.php" class="btn btn-outline-dark">Сменить пароль</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Мои автомобили</h2>
                    <a href="add_car.php" class="btn btn-sm btn-primary">Добавить автомобиль</a>
                </div>
                <div class="card-body">
                    <?php if ($cars): ?>
                        <div class="row g-3">
                            <?php foreach ($cars as $car): ?>
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                        <div class="fw-semibold"><?= h($car['brand'] . ' ' . $car['model']) ?></div>
                                        <div class="text-muted">Год: <?= (int) $car['year'] ?></div>
                                        <div class="text-muted">Номер: <?= h($car['plate_number']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-0">Вы еще не добавили автомобили.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Мои записи</h2>
            <a href="book_service.php" class="btn btn-primary btn-sm">Новая запись</a>
        </div>
        <div class="card-body">
            <?php if ($appointments): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Дата и время</th>
                                <th>Автомобиль</th>
                                <th>Услуга</th>
                                <th>Бокс</th>
                                <th>Стоимость</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <?php $canCancel = strtotime($appointment['appointment_datetime']) > strtotime('+24 hours') && in_array($appointment['status'], ['new', 'confirmed'], true); ?>
                            <tr>
                                <td>#<?= (int) $appointment['id'] ?></td>
                                <td><?= h(date('d.m.Y H:i', strtotime($appointment['appointment_datetime']))) ?></td>
                                <td><?= h($appointment['brand'] . ' ' . $appointment['model'] . ' (' . $appointment['plate_number'] . ')') ?></td>
                                <td><?= h($appointment['service_title']) ?></td>
                                <td><?= h($appointment['box_name']) ?></td>
                                <td><?= number_format((float) $appointment['total_price'], 2, '.', ' ') ?> ₽</td>
                                <td><span class="badge bg-<?= appointment_badge_class($appointment['status']) ?>"><?= h($appointment['status']) ?></span></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="appointment_details.php?id=<?= (int) $appointment['id'] ?>" class="btn btn-outline-primary btn-sm">Подробнее</a>
                                        <?php if ($canCancel): ?>
                                            <form method="POST" action="cancel_appointment.php" onsubmit="return confirm('Отменить запись?');">
                                                <?= csrf_input() ?>
                                                <input type="hidden" name="id" value="<?= (int) $appointment['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Отменить</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center mb-0">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <h3 class="h5 text-muted">У вас пока нет записей.</h3>
                    <a href="book_service.php" class="btn btn-primary mt-3">Записаться на обслуживание</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
