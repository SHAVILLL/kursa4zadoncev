<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$stmt = $pdo->prepare('SELECT username, phone, email FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();

    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $update = $pdo->prepare('UPDATE users SET username = ?, phone = ? WHERE id = ?');
    $update->execute([
        $username !== '' ? $username : null,
        $phone !== '' ? $phone : null,
        $_SESSION['user_id'],
    ]);

    $_SESSION['user_name'] = $username !== '' ? $username : $user['email'];
    set_flash('success', 'Профиль обновлен.');
    redirect_to('profile.php');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование профиля</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/nav.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><h1 class="h4 mb-0">Редактирование профиля</h1></div>
                <div class="card-body">
                    <?php if ($message): ?><?= $message ?><?php endif; ?>
                    <form method="POST">
                        <?= csrf_input() ?>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= h($user['email']) ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Имя</label>
                            <input type="text" name="username" class="form-control" value="<?= h($user['username']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Телефон</label>
                            <input type="text" name="phone" class="form-control" value="<?= h($user['phone']) ?>">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                            <a href="profile.php" class="btn btn-outline-secondary">Назад</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
