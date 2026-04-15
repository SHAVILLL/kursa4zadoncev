<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$message = '';

$services = $pdo->query('SELECT * FROM services WHERE is_active = 1 ORDER BY title')->fetchAll();
$boxes = $pdo->query('SELECT * FROM service_boxes WHERE is_active = 1 ORDER BY id')->fetchAll();
$carsStmt = $pdo->prepare('SELECT * FROM cars WHERE user_id = ? ORDER BY id DESC');
$carsStmt->execute([$_SESSION['user_id']]);
$cars = $carsStmt->fetchAll();

$selectedServiceId = isset($_GET['service_id']) ? (int) $_GET['service_id'] : (int) ($_POST['service_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();

    $serviceId = (int) ($_POST['service_id'] ?? 0);
    $carId = (int) ($_POST['car_id'] ?? 0);
    $boxId = (int) ($_POST['box_id'] ?? 0);
    $date = trim($_POST['appointment_date'] ?? '');
    $time = trim($_POST['appointment_time'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($serviceId <= 0 || $carId <= 0 || $boxId <= 0 || $date === '' || $time === '') {
        $message = '<div class="alert alert-danger">Заполните все поля формы.</div>';
    } elseif (!$cars) {
        $message = '<div class="alert alert-danger">Сначала добавьте автомобиль.</div>';
    } else {
        $carStmt = $pdo->prepare('SELECT * FROM cars WHERE id = ? AND user_id = ? LIMIT 1');
        $carStmt->execute([$carId, $_SESSION['user_id']]);
        $car = $carStmt->fetch();

        $serviceStmt = $pdo->prepare('SELECT * FROM services WHERE id = ? AND is_active = 1 LIMIT 1');
        $serviceStmt->execute([$serviceId]);
        $service = $serviceStmt->fetch();

        $boxStmt = $pdo->prepare('SELECT * FROM service_boxes WHERE id = ? AND is_active = 1 LIMIT 1');
        $boxStmt->execute([$boxId]);
        $box = $boxStmt->fetch();

        $appointmentDateTime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);

        if (!$car) {
            $message = '<div class="alert alert-danger">Вы не можете использовать чужой автомобиль.</div>';
        } elseif (!$service) {
            $message = '<div class="alert alert-danger">Услуга не найдена.</div>';
        } elseif (!$box) {
            $message = '<div class="alert alert-danger">Бокс не найден.</div>';
        } elseif (!$appointmentDateTime) {
            $message = '<div class="alert alert-danger">Некорректная дата или время.</div>';
        } elseif ($appointmentDateTime <= new DateTime()) {
            $message = '<div class="alert alert-danger">Дата записи должна быть в будущем.</div>';
        } else {
            $start = $appointmentDateTime->format('Y-m-d H:i:s');
            $endDateTime = clone $appointmentDateTime;
            $endDateTime->modify('+' . (int) $service['duration_minutes'] . ' minutes');
            $end = $endDateTime->format('Y-m-d H:i:s');

            $overlapSql = "
                SELECT a.id
                FROM appointments a
                JOIN services s ON s.id = a.service_id
                WHERE a.box_id = :box_id
                  AND a.status IN ('new', 'confirmed', 'in_progress')
                  AND a.appointment_datetime < :new_end
                  AND DATE_ADD(a.appointment_datetime, INTERVAL s.duration_minutes MINUTE) > :new_start
                LIMIT 1
            ";
            $overlapStmt = $pdo->prepare($overlapSql);
            $overlapStmt->execute([
                ':box_id' => $boxId,
                ':new_end' => $end,
                ':new_start' => $start,
            ]);
            $overlap = $overlapStmt->fetch();

            if ($overlap) {
                $message = '<div class="alert alert-danger">Этот бокс уже занят на выбранное время. Выберите другой слот.</div>';
            } else {
                $totalPrice = (float) $service['base_price'];
                $insert = $pdo->prepare("INSERT INTO appointments (user_id, car_id, service_id, box_id, appointment_datetime, total_price, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'new')");
                $insert->execute([
                    $_SESSION['user_id'],
                    $carId,
                    $serviceId,
                    $boxId,
                    $start,
                    $totalPrice,
                    $notes !== '' ? $notes : null,
                ]);

                set_flash('success', 'Запись успешно создана. Стоимость рассчитана на сервере: ' . number_format($totalPrice, 2, '.', ' ') . ' ₽.');
                redirect_to('profile.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запись на обслуживание</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/nav.php'; ?>
<div class="container pb-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white"><h1 class="h4 mb-0">Запись на обслуживание</h1></div>
                <div class="card-body">
                    <?= $message ?>
                    <?php if (!$cars): ?>
                        <div class="alert alert-warning">У вас пока нет автомобилей. <a href="add_car.php">Добавьте автомобиль</a>, чтобы создать запись.</div>
                    <?php endif; ?>
                    <form method="POST">
                        <?= csrf_input() ?>
                        <div class="mb-3">
                            <label class="form-label">Услуга</label>
                            <select name="service_id" class="form-select" required>
                                <option value="">Выберите услугу</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= (int) $service['id'] ?>" <?= $selectedServiceId === (int) $service['id'] ? 'selected' : '' ?>>
                                        <?= h($service['title']) ?> — <?= number_format((float) $service['base_price'], 2, '.', ' ') ?> ₽ (<?= (int) $service['duration_minutes'] ?> мин.)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Автомобиль</label>
                            <select name="car_id" class="form-select" required>
                                <option value="">Выберите автомобиль</option>
                                <?php foreach ($cars as $car): ?>
                                    <option value="<?= (int) $car['id'] ?>" <?= ((int) ($_POST['car_id'] ?? 0) === (int) $car['id']) ? 'selected' : '' ?>>
                                        <?= h($car['brand'] . ' ' . $car['model'] . ' (' . $car['plate_number'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Бокс</label>
                            <select name="box_id" class="form-select" required>
                                <option value="">Выберите бокс</option>
                                <?php foreach ($boxes as $box): ?>
                                    <option value="<?= (int) $box['id'] ?>" <?= ((int) ($_POST['box_id'] ?? 0) === (int) $box['id']) ? 'selected' : '' ?>><?= h($box['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Проверьте занятость боксов в календаре перед записью.</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Дата</label>
                                <input type="date" name="appointment_date" class="form-control" required value="<?= h($_POST['appointment_date'] ?? date('Y-m-d', strtotime('+1 day'))) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Время</label>
                                <input type="time" name="appointment_time" class="form-control" required value="<?= h($_POST['appointment_time'] ?? '10:00') ?>">
                            </div>
                        </div>
                        <div class="mt-3 mb-3">
                            <label class="form-label">Комментарий</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Опишите проблему автомобиля"><?= h($_POST['notes'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Создать запись</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><h2 class="h5 mb-0">Календарь занятости</h2></div>
                <div class="card-body">
                    <p class="text-muted">Перед записью посмотрите, какие боксы заняты на нужную дату.</p>
                    <a href="appointment_slots.php" class="btn btn-outline-primary w-100">Открыть календарь боксов</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
