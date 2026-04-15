<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('admin_services.php');
}

verify_csrf_or_die();
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'Некорректный идентификатор услуги.');
    redirect_to('admin_services.php');
}

$check = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE service_id = ?');
$check->execute([$id]);
$usedCount = (int) $check->fetchColumn();

if ($usedCount > 0) {
    $stmt = $pdo->prepare('UPDATE services SET is_active = 0 WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('warning', 'Услуга уже используется в записях, поэтому она не удалена физически, а деактивирована.');
} else {
    $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('success', 'Услуга удалена.');
}

redirect_to('admin_services.php');
