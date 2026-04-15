<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$date = trim($_GET['date'] ?? date('Y-m-d'));
$dateObj = DateTime::createFromFormat('Y-m-d', $date);
if (!$dateObj) {
    $date = date('Y-m-d');
    $dateObj = new DateTime($date);
}

$boxes = $pdo->query('SELECT * FROM service_boxes WHERE is_active = 1 ORDER BY id')->fetchAll();

$sql = "
    SELECT a.id, a.box_id, a.appointment_datetime, a.status, s.title, s.duration_minutes
    FROM appointments a
    JOIN services s ON s.id = a.service_id
    WHERE DATE(a.appointment_datetime) = ?
      AND a.status IN ('new', 'confirmed', 'in_progress')
    ORDER BY a.appointment_datetime ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$date]);
$appointments = $stmt->fetchAll();

$appointmentsByBox = [];
foreach ($appointments as $appointment) {
    $appointmentsByBox[$appointment['box_id']][] = $appointment;
}

$hours = [];
for ($hour = 9; $hour <= 18; $hour++) {
    $hours[] = sprintf('%02d:00', $hour);
}

function slot_busy(array $appointments, $date, $hour)
{
    $slotStart = new DateTime($date . ' ' . $hour . ':00');
    $slotEnd = clone $slotStart;
    $slotEnd->modify('+1 hour');

    foreach ($appointments as $appointment) {
        $start = new DateTime($appointment['appointment_datetime']);
        $end = clone $start;
        $end->modify('+' . (int) $appointment['duration_minutes'] . ' minutes');

        if ($start < $slotEnd && $end > $slotStart) {
            return true;
        }
    }

    return false;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Календарь занятости боксов</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .slot-busy { background: #f8d7da; }
        .slot-free { background: #d1e7dd; }
    </style>
</head>
<body class="bg-light">
<?php require __DIR__ . '/nav.php'; ?>
<div class="container pb-5">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white"><h1 class="h4 mb-0">Календарь занятости боксов</h1></div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Дата</label>
                    <input type="date" name="date" class="form-control" value="<?= h($date) ?>">
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary">Показать</button>
                </div>
            </form>
        </div>
    </div>

    <?php foreach ($boxes as $box): ?>
        <?php $boxAppointments = $appointmentsByBox[$box['id']] ?? []; ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0"><?= h($box['name']) ?></h2>
                <span class="text-muted small">Дата: <?= h(date('d.m.Y', strtotime($date))) ?></span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead>
                            <tr>
                                <?php foreach ($hours as $hour): ?>
                                    <th><?= h($hour) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php foreach ($hours as $hour): ?>
                                    <?php $busy = slot_busy($boxAppointments, $date, (int) substr($hour, 0, 2)); ?>
                                    <td class="<?= $busy ? 'slot-busy' : 'slot-free' ?>">
                                        <?= $busy ? 'Занято' : 'Свободно' ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3 class="h6 mt-4">Записи по боксу</h3>
                <?php if ($boxAppointments): ?>
                    <div class="list-group">
                        <?php foreach ($boxAppointments as $appointment): ?>
                            <?php
                                $start = new DateTime($appointment['appointment_datetime']);
                                $end = clone $start;
                                $end->modify('+' . (int) $appointment['duration_minutes'] . ' minutes');
                            ?>
                            <div class="list-group-item">
                                <strong><?= h($appointment['title']) ?></strong>
                                <div class="text-muted small">
                                    <?= h($start->format('H:i')) ?> — <?= h($end->format('H:i')) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success mb-0">На выбранную дату записей в этом боксе нет.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
