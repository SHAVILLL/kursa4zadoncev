<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$limit = 6;
$offset = ($page - 1) * $limit;

$totalStmt = $pdo->query("SELECT COUNT(*) FROM services WHERE is_active = 1");
$totalRows = (int) $totalStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$stmt = $pdo->prepare("SELECT * FROM services WHERE is_active = 1 ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$services = $stmt->fetchAll();

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Автосервис — услуги и запись</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/nav.php'; ?>

<div class="container pb-5">
    <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?>"><?= $flash['message'] ?></div>
    <?php endif; ?>

    <div class="p-4 p-md-5 mb-4 bg-white rounded-4 shadow-sm border">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-6 fw-bold">Запись в автосервис онлайн</h1>
                <p class="lead mb-3">Выберите услугу, автомобиль, удобное время и свободный бокс. Стоимость рассчитывается на сервере по данным из базы.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="book_service.php" class="btn btn-primary">Записаться</a>
                    <a href="appointment_slots.php" class="btn btn-outline-secondary">Календарь боксов</a>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Услуги автосервиса</h2>
        <span class="text-muted">Всего услуг: <?= $totalRows ?></span>
    </div>

    <div class="row g-4">
        <?php if ($services): ?>
            <?php foreach ($services as $service): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 shadow-sm border-0">
                        <?php if (!empty($service['image_url'])): ?>
                            <img src="<?= h($service['image_url']) ?>" class="card-img-top" alt="<?= h($service['title']) ?>" style="height: 220px; object-fit: cover;">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center bg-secondary-subtle text-secondary" style="height: 220px;">
                                <div class="text-center">
                                    <div class="fs-1">🚗</div>
                                    <div>Фото услуги не добавлено</div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h3 class="h5 card-title"><?= h($service['title']) ?></h3>
                            <p class="card-text text-muted flex-grow-1"><?= nl2br(h($service['description'])) ?></p>
                            <div class="mb-3">
                                <div><strong><?= number_format((float) $service['base_price'], 2, '.', ' ') ?> ₽</strong></div>
                                <div class="small text-muted">Длительность: <?= (int) $service['duration_minutes'] ?> мин.</div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="book_service.php?service_id=<?= (int) $service['id'] ?>" class="btn btn-primary w-100">Записаться</a>
                                <a href="appointment_slots.php" class="btn btn-outline-secondary">Слоты</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">Пока нет активных услуг. Зайдите под админом и добавьте их в админке.</div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="mt-4" aria-label="Навигация по страницам">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
