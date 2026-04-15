ПРОЕКТ: Веб-сервис для автосервиса (запись и учет)

1. Что внутри
- register.php / login.php / logout.php — регистрация и вход
- profile.php — личный кабинет клиента с его записями
- add_car.php — добавление автомобилей клиента
- book_service.php — запись на услугу
- appointment_details.php — детали записи с защитой от IDOR
- cancel_appointment.php — отмена записи только своей и только более чем за 24 часа
- appointment_slots.php — календарь занятости боксов
- admin_panel.php — главная админка
- admin_services.php / add_service.php / edit_service.php / delete_service.php — CRUD услуг
- admin_appointments.php / update_appointment_status.php — просмотр записей и смена статуса
- auth.php — сессии, CSRF, защитные функции
- db.php — подключение к MySQL
- schema.sql — создание БД и тестовых данных

2. Как запустить на Beget
Шаг 1. Создайте базу данных в панели Beget.
Шаг 2. Откройте phpMyAdmin и выполните файл schema.sql.
Шаг 3. Откройте db.php и замените:
    YOUR_LOGIN_autoservice
    YOUR_DATABASE_PASSWORD
Шаг 4. Загрузите все PHP-файлы в папку public_html.
Шаг 5. Откройте index.php в браузере.

3. Тестовые пользователи
- admin@autoservice.local / admin123
- client@autoservice.local / admin123

4. Что реализовано по требованиям
- RBAC: роли admin/client и защита check_admin.php
- CRUD услуг: создание, просмотр, редактирование, удаление/деактивация
- Пагинация: главная, услуги в админке, список записей
- Личный кабинет: только свои записи
- Anti-IDOR: пользователь не может открыть чужую запись по id
- Anti-CSRF: формы изменения данных защищены токеном
- XSS-защита: весь пользовательский вывод экранируется через h()
- Календарь занятости боксов: appointment_slots.php
- Расчет стоимости: цена берется с сервера из таблицы services
- Временная логика: отмена записи только больше чем за 24 часа

5. Важные замечания
- Удаление услуги, которая уже используется в записи, превращается в деактивацию.
- Для создания записи клиент должен сначала добавить хотя бы один автомобиль.
- В проекте используется только PDO Prepared Statements.

6. Что показать преподавателю
- Страницу profile.php с записями клиента
- Страницу admin_services.php с CRUD услуг
- Страницу admin_appointments.php с JOIN-выводом
- Страницу appointment_slots.php с занятостью боксов
