SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `cars`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `service_boxes`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `username` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('admin', 'client') NOT NULL DEFAULT 'client',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `service_boxes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `box_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `services` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `base_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `duration_minutes` INT NOT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cars` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `brand` VARCHAR(100) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `year` INT NOT NULL,
  `plate_number` VARCHAR(30) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate_number_unique` (`plate_number`),
  KEY `cars_user_idx` (`user_id`),
  CONSTRAINT `cars_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `appointments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `car_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `box_id` INT NOT NULL,
  `appointment_datetime` DATETIME NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('new', 'confirmed', 'in_progress', 'done', 'canceled') NOT NULL DEFAULT 'new',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `appointments_user_idx` (`user_id`),
  KEY `appointments_car_idx` (`car_id`),
  KEY `appointments_service_idx` (`service_id`),
  KEY `appointments_box_idx` (`box_id`),
  KEY `appointments_date_idx` (`appointment_datetime`),
  CONSTRAINT `appointments_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `appointments_car_fk` FOREIGN KEY (`car_id`) REFERENCES `cars`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `appointments_service_fk` FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `appointments_box_fk` FOREIGN KEY (`box_id`) REFERENCES `service_boxes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`email`, `password_hash`, `username`, `phone`, `role`) VALUES
('admin@autoservice.local', '$2y$12$r6l50Vxo59GPmQ73D.BuluRxwfGLI9Wr7ncX1c/I0ec3tDD.E5b8u', 'Главный администратор', '+37100000001', 'admin'),
('client@autoservice.local', '$2y$12$r6l50Vxo59GPmQ73D.BuluRxwfGLI9Wr7ncX1c/I0ec3tDD.E5b8u', 'Тестовый клиент', '+37100000002', 'client');

INSERT INTO `service_boxes` (`name`, `is_active`) VALUES
('Бокс 1', 1),
('Бокс 2', 1),
('Бокс 3', 1);

INSERT INTO `services` (`title`, `description`, `base_price`, `duration_minutes`, `image_url`, `is_active`) VALUES
('Диагностика двигателя', 'Компьютерная диагностика систем двигателя и чтение ошибок.', 35.00, 60, NULL, 1),
('Замена масла и фильтра', 'Замена моторного масла и масляного фильтра с базовой проверкой уровней.', 55.00, 45, NULL, 1),
('Шиномонтаж', 'Снятие, установка, балансировка и контроль давления.', 40.00, 50, NULL, 1),
('Проверка тормозной системы', 'Осмотр тормозных дисков, колодок и гидравлики.', 45.00, 60, NULL, 1);

INSERT INTO `cars` (`user_id`, `brand`, `model`, `year`, `plate_number`) VALUES
((SELECT id FROM users WHERE email = 'client@autoservice.local' LIMIT 1), 'Toyota', 'Corolla', 2016, 'AB-1234'),
((SELECT id FROM users WHERE email = 'client@autoservice.local' LIMIT 1), 'Volkswagen', 'Golf', 2019, 'CD-5678');

INSERT INTO `appointments` (`user_id`, `car_id`, `service_id`, `box_id`, `appointment_datetime`, `total_price`, `status`, `notes`)
SELECT
    u.id,
    c.id,
    s.id,
    b.id,
    '2030-01-15 10:00:00',
    s.base_price,
    'confirmed',
    'Проверить двигатель на наличие ошибок'
FROM users u
JOIN cars c ON c.user_id = u.id
JOIN services s ON s.title = 'Диагностика двигателя'
JOIN service_boxes b ON b.name = 'Бокс 1'
WHERE u.email = 'client@autoservice.local'
LIMIT 1;
