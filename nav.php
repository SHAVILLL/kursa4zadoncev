<?php
require_once __DIR__ . '/auth.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Автосервис</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Переключить навигацию">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Главная</a></li>
                <?php if (is_logged_in()): ?>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Профиль</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_car.php">Мои автомобили</a></li>
                    <li class="nav-item"><a class="nav-link" href="book_service.php">Записаться</a></li>
                    <?php if (is_admin()): ?>
                        <li class="nav-item"><a class="nav-link" href="admin_panel.php">Админка</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Вход</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Регистрация</a></li>
                <?php endif; ?>
            </ul>
            <?php if (is_logged_in()): ?>
                <div class="d-flex align-items-center gap-2 text-white small">
                    <span><?= h($_SESSION['user_email'] ?? '') ?></span>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">Выйти</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
