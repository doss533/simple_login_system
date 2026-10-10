-- ============================================================
--  Login System - database setup
--  Import this file in phpMyAdmin OR run:
--      mysql -u root < setup.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `login_system`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE `login_system`;

-- ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(50)  NOT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('user','admin') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Demo accounts (passwords are hashed with PASSWORD_DEFAULT)
--   admin / admin123   -> administrator
--   juan  / user123    -> regular user
-- ------------------------------------------------------------

INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$qm5EOLVoi/IpCFiNl9iTmOwSnGxTglJJqdK1zoYgoAwX5hGNs07Oq', 'admin'),
('juan',  '$2y$10$q5zzIJO7diCa3tbcOPJNDedq8Ft9qb0ULbWl27p/kvpW8TbVUlBqS', 'user');
