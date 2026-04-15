<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('APP_TIMEZONE_SET')) {
    date_default_timezone_set('Europe/Riga');
    define('APP_TIMEZONE_SET', true);
}

if (!function_exists('h')) {
    function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect_to')) {
    function redirect_to($url)
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in()
    {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('is_admin')) {
    function is_admin()
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}

if (!function_exists('require_login')) {
    function require_login()
    {
        if (!is_logged_in()) {
            redirect_to('login.php');
        }
    }
}

if (!function_exists('require_admin')) {
    function require_admin()
    {
        if (!is_logged_in() || !is_admin()) {
            http_response_code(403);
            exit('Доступ запрещён. <a href="login.php">Войти</a>');
        }
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_input')) {
    function csrf_input()
    {
        return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
    }
}

if (!function_exists('verify_csrf_or_die')) {
    function verify_csrf_or_die()
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $formToken = $_POST['csrf_token'] ?? '';

        if (!$sessionToken || !$formToken || !hash_equals($sessionToken, $formToken)) {
            http_response_code(400);
            exit('Ошибка безопасности: неверный CSRF-токен.');
        }
    }
}

if (!function_exists('set_flash')) {
    function set_flash($type, $message)
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}

if (!function_exists('get_flash')) {
    function get_flash()
    {
        if (!isset($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }
}
