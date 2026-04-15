<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('admin_appointments.php');
}

verify_csrf_or_die();
$id = (int) ($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$allowedStatuses = ['new', 'confirmed', 'in_progress', 'done', 'canceled'];

if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
    set_flash('danger', 'Некорректные данные для обновления статуса.');
    redirect_to('admin_appointments.php');
}

$stmt = $pdo->prepare('UPDATE appointments SET status = ? WHERE id = ?');
$stmt->execute([$status, $id]);
set_flash('success', 'Статус записи обновлен.');
redirect_to('admin_appointments.php');
