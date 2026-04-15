<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();

    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = (int) ($_POST['year'] ?? 0);
    $plateNumber = trim($_POST['plate_number'] ?? '');

    if ($brand === '' || $model === '' || $year < 1950 || $year > ((int) date('Y') + 1) || $plateNumber === '') {
        $message = '<div class="alert alert-danger">Заполните все поля корректно.</div>';
    } else {
        $stmt = $pdo->prepare('INSERT INTO cars (user_id, brand, model, year, plate_number) VALUES (?, ?, ?, ?, ?)');
        try {
            $stmt->execute([$_SESSION['user_id'], $brand, $model, $year, $plateNumber]);
            $message = '<div class="alert alert-success">Автомобиль добавлен.</div>';
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                $message = '<div class="alert alert-danger">Автомобиль с таким номером уже существует.</div>';
            } else {
                $message = '<div class="alert alert-danger">Ошибка базы данных.</div>';
            }
        }
    }
}

$carsStmt = $pdo->prepare('SELECT * FROM cars WHERE user_id = ? ORDER BY id DESC');
$carsStmt->execute([$_SESSION['user_id']]);
$cars = $carsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои автомобили</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/nav.php'; ?>
<div class="container pb-5">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white"><h1 class="h4 mb-0">Добавить автомобиль</h1></div>
                <div class="card-body">
                    <?= $message ?>
                    <form method="POST">
                        <?= csrf_input() ?>
                        <div class="mb-3">
                            <label class="form-label">Марка</label>
                            <input type="text" name="brand" class="form-control" required value="<?= h($_POST['brand'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Модель</label>
                            <input type="text" name="model" class="form-control" required value="<?= h($_POST['model'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Год выпуска</label>
                            <input type="number" name="year" class="form-control" required min="1950" max="<?= (int) date('Y') + 1 ?>" value="<?= h($_POST['year'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Гос. номер</label>
                            <input type="text" name="plate_number" class="form-control" required value="<?= h($_POST['plate_number'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Сохранить автомобиль</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><h2 class="h5 mb-0">Ваши автомобили</h2></div>
                <div class="card-body">
                    <?php if ($cars): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Марка</th>
                                        <th>Модель</th>
                                        <th>Год</th>
                                        <th>Номер</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cars as $car): ?>
                                        <tr>
                                            <td><?= h($car['brand']) ?></td>
                                            <td><?= h($car['model']) ?></td>
                                            <td><?= (int) $car['year'] ?></td>
                                            <td><?= h($car['plate_number']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-0">Пока автомобилей нет. Добавьте первый автомобиль, чтобы записаться на обслуживание.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
