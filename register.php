<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

if (is_logged_in()) {
    redirect_to('index.php');
}

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($email === '' || $password === '' || $passwordConfirm === '') {
        $errorMsg = 'Заполните обязательные поля.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Некорректный формат email.';
    } elseif (mb_strlen($password) < 8) {
        $errorMsg = 'Пароль должен быть не короче 8 символов.';
    } elseif ($password !== $passwordConfirm) {
        $errorMsg = 'Пароли не совпадают.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password_hash, username, phone, role) VALUES (:email, :hash, :username, :phone, 'client')";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':email' => $email,
                ':hash' => $hash,
                ':username' => $username !== '' ? $username : null,
                ':phone' => $phone !== '' ? $phone : null,
            ]);

            $successMsg = 'Регистрация успешна. Теперь можно войти в систему.';
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                $errorMsg = 'Такой email уже зарегистрирован.';
            } else {
                $errorMsg = 'Ошибка базы данных. Попробуйте еще раз.';
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
    <title>Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/nav.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white"><h1 class="h4 mb-0">Регистрация клиента</h1></div>
                <div class="card-body p-4">
                    <?php if ($errorMsg): ?>
                        <div class="alert alert-danger"><?= h($errorMsg) ?></div>
                    <?php endif; ?>
                    <?php if ($successMsg): ?>
                        <div class="alert alert-success"><?= h($successMsg) ?> <a href="login.php">Войти</a></div>
                    <?php endif; ?>

                    <form method="POST" action="register.php" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required value="<?= h($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Имя</label>
                            <input type="text" name="username" class="form-control" value="<?= h($_POST['username'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Телефон</label>
                            <input type="text" name="phone" class="form-control" value="<?= h($_POST['phone'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Повторите пароль *</label>
                            <input type="password" name="password_confirm" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="login.php">Уже есть аккаунт? Войти</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
