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
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM services WHERE title LIKE :q OR description LIKE :q');
    $countStmt->execute([':q' => '%' . $q . '%']);
    $totalRows = (int) $countStmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT * FROM services WHERE title LIKE :q OR description LIKE :q ORDER BY id DESC LIMIT :limit OFFSET :offset');
    $stmt->bindValue(':q', '%' . $q . '%', PDO::PARAM_STR);
} else {
    $totalRows = (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
    $stmt = $pdo->prepare('SELECT * FROM services ORDER BY id DESC LIMIT :limit OFFSET :offset');
}

$totalPages = max(1, (int) ceil($totalRows / $limit));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление услугами</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/nav.php'; ?>
<div class="container pb-5">
    <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?>"><?= $flash['message'] ?></div>
    <?php endif; ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h1 class="h4 mb-0">Управление услугами</h1>
            <a href="add_service.php" class="btn btn-success btn-sm">Добавить услугу</a>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-9">
                    <input type="text" name="q" class="form-control" placeholder="Поиск по названию или описанию" value="<?= h($q) ?>">
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
                            <th>Название</th>
                            <th>Цена</th>
                            <th>Длительность</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?= (int) $service['id'] ?></td>
                            <td><?= h($service['title']) ?></td>
                            <td><?= number_format((float) $service['base_price'], 2, '.', ' ') ?> ₽</td>
                            <td><?= (int) $service['duration_minutes'] ?> мин.</td>
                            <td>
                                <span class="badge bg-<?= $service['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $service['is_active'] ? 'активна' : 'скрыта' ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="edit_service.php?id=<?= (int) $service['id'] ?>" class="btn btn-warning btn-sm">✏️</a>
                                    <form method="POST" action="delete_service.php" onsubmit="return confirm('Удалить или деактивировать услугу?');">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="id" value="<?= (int) $service['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$services): ?>
                        <tr><td colspan="6" class="text-center text-muted">Ничего не найдено.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&q=<?= urlencode($q) ?>"><?= $i ?></a>
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
