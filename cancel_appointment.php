<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('profile.php');
}

verify_csrf_or_die();
$appointmentId = (int) ($_POST['id'] ?? 0);

if ($appointmentId <= 0) {
    set_flash('danger', 'Некорректный идентификатор записи.');
    redirect_to('profile.php');
}

$sql = "
    UPDATE appointments
    SET status = 'canceled'
    WHERE id = ?
      AND user_id = ?
      AND status IN ('new', 'confirmed')
      AND appointment_datetime > DATE_ADD(NOW(), INTERVAL 24 HOUR)
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$appointmentId, $_SESSION['user_id']]);

if ($stmt->rowCount() > 0) {
    set_flash('success', 'Запись успешно отменена.');
} else {
    set_flash('warning', 'Нельзя отменить эту запись: либо она не ваша, либо до неё осталось меньше 24 часов, либо статус уже изменен.');
}

redirect_to('profile.php');
