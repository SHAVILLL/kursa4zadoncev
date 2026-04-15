<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_admin();

$q = trim($_GET['q'] ?? '');
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$limit = 10;
$offset = ($page - 1) * $limit;
$flash = get_flash();

if ($q !== '') {
    $like = '%' . $q . '%';

    $countSql = "
        SELECT COUNT(*)
        FROM appointments a
        JOIN users u ON u.id = a.user_id
        JOIN cars c ON c.id = a.car_id
        JOIN services s ON s.id = a.service_id
        WHERE u.email LIKE ? OR c.plate_number LIKE ? OR s.title LIKE ?
    ";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$like, $like, $like]);
    $totalRows = (int) $countStmt->fetchColumn();
} else {
    $totalRows = (int) $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
}

$totalPages = max(1, (int) ceil($totalRows / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$limit = (int) $limit;
$offset = (int) $offset;

if ($q !== '') {
    $like = '%' . $q . '%';

    $sql = "
        SELECT
            a.id,
            a.appointment_datetime,
            a.status,
            a.total_price,
            u.email,
            c.brand,
            c.model,
            c.plate_number,
            s.title AS service_title,
            b.name AS box_name
        FROM appointments a
        JOIN users u ON u.id = a.user_id
        JOIN cars c ON c.id = a.car_id
        JOIN services s ON s.id = a.service_id
        JOIN service_boxes b ON b.id = a.box_id
        WHERE u.email LIKE ? OR c.plate_number LIKE ? OR s.title LIKE ?
        ORDER BY a.appointment_datetime DESC
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$like, $like, $like]);
} else {
    $sql = "
        SELECT
            a.id,
            a.appointment_datetime,
            a.status,
            a.total_price,
            u.email,
            c.brand,
            c.model,
            c.plate_number,
            s.title AS service_title,
            b.name AS box_name
        FROM appointments a
        JOIN users u ON u.id = a.user_id
        JOIN cars c ON c.id = a.car_id
        JOIN services s ON s.id = a.service_id
        JOIN service_boxes b ON b.id = a.box_id
        ORDER BY a.appointment_datetime DESC
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $pdo->query($sql);
}

$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Все записи</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/nav.php'; ?>

<div class="container pb-5">
    <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?>"><?= $flash['message'] ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h1 class="h4 mb-0">Все записи клиентов</h1>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-9">
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        placeholder="Поиск по email, номеру авто или услуге"
                        value="<?= h($q) ?>"
                    >
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-outline-primary">Найти</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Дата и время</th>
                            <th>Клиент</th>
                            <th>Автомобиль</th>
                            <th>Услуга</th>
                            <th>Бокс</th>
                            <th>Стоимость</th>
                            <th>Статус</th>
                            <th>Изменить статус</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td>
                                <a href="appointment_details.php?id=<?= (int) $appointment['id'] ?>">
                                    #<?= (int) $appointment['id'] ?>
                                </a>
                            </td>
                            <td><?= h(date('d.m.Y H:i', strtotime($appointment['appointment_datetime']))) ?></td>
                            <td><?= h($appointment['email']) ?></td>
                            <td><?= h($appointment['brand'] . ' ' . $appointment['model'] . ' (' . $appointment['plate_number'] . ')') ?></td>
                            <td><?= h($appointment['service_title']) ?></td>
                            <td><?= h($appointment['box_name']) ?></td>
                            <td><?= number_format((float) $appointment['total_price'], 2, '.', ' ') ?> ₽</td>
                            <td><?= h($appointment['status']) ?></td>
                            <td>
                                <form action="update_appointment_status.php" method="POST" class="d-flex gap-2">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= (int) $appointment['id'] ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php foreach (['new', 'confirmed', 'in_progress', 'done', 'canceled'] as $status): ?>
                                            <option value="<?= h($status) ?>" <?= $appointment['status'] === $status ? 'selected' : '' ?>>
                                                <?= h($status) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-primary" type="submit">OK</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$appointments): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">Записей пока нет.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&q=<?= urlencode($q) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>