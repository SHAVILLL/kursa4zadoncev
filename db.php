<?php
/*
 * Конфигурация подключения к MySQL для Beget.
 * ВАЖНО: замените имя базы и пароль на свои.
 */

$host = 'localhost';
$db   = 'n91371fv_shiva';
$user = 'n91371fv_shiva';
$pass = 'parol0660parol!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('Ошибка подключения к базе данных. Проверьте настройки в db.php');
}
