-- Import through http://localhost/phpmyadmin with XAMPP MySQL running.
-- This script creates the schema without deleting existing accounts.
CREATE DATABASE IF NOT EXISTS salone_studio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE salone_studio;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    goal VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL,
    last_login DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS progress (
    user_id BIGINT UNSIGNED NOT NULL,
    lesson_id VARCHAR(50) NOT NULL,
    completed_at DATETIME NOT NULL,
    PRIMARY KEY (user_id, lesson_id),
    CONSTRAINT fk_progress_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_activity_user_id (user_id, id),
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attempts (
    bucket CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY,
    failures INT UNSIGNED NOT NULL DEFAULT 0,
    started_at BIGINT NOT NULL,
    INDEX idx_attempts_started (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
