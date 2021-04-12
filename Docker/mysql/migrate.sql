-- Adminer 4.8.0 MySQL 8.0.23 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

-- CREATE DATABASE IF NOT EXISTS camagru;
-- USE camagru;

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
	`id` bigint unsigned NOT NULL AUTO_INCREMENT,
	`image` int unsigned NOT NULL,
	`user` int unsigned NOT NULL,
	`message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	`at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`edited` datetime DEFAULT NULL,
	`deleted` tinyint NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `image` (`image`),
	KEY `user` (`user`),
	CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`image`) REFERENCES `creations` (`id`) ON DELETE CASCADE,
	CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `creations`;
CREATE TABLE `creations` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`user` int unsigned NOT NULL,
	`name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	`animated` tinyint unsigned NOT NULL DEFAULT '0',
	`private` tinyint NOT NULL DEFAULT '0',
	`at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `user` (`user`),
	CONSTRAINT `creations_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `decorations`;
CREATE TABLE `decorations` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	`animated` tinyint unsigned NOT NULL DEFAULT '0',
	`public` tinyint(1) NOT NULL DEFAULT '1',
	`position` enum('top-left','top-center','top-right','center-left','center-center','center-right','bottom-left','bottom-center','bottom-right') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'top-left',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
	`id` bigint unsigned NOT NULL AUTO_INCREMENT,
	`user` int unsigned NOT NULL,
	`image` int unsigned NOT NULL,
	`at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `image` (`image`),
	KEY `user` (`user`),
	CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`image`) REFERENCES `creations` (`id`) ON DELETE CASCADE,
	CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`username` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	`email` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	`password` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	`verified` tinyint unsigned NOT NULL DEFAULT '0',
	`receiveComments` tinyint unsigned NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`),
	UNIQUE KEY `username` (`username`),
	UNIQUE KEY `mail` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `usersessions`;
CREATE TABLE `usersessions` (
	`id` bigint unsigned NOT NULL AUTO_INCREMENT,
	`user` int unsigned NOT NULL,
	`session` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	`issued` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`rememberMe` tinyint unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `user` (`user`),
	CONSTRAINT `usersessions_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `usertokens`;
CREATE TABLE `usertokens` (
	`id` bigint unsigned NOT NULL AUTO_INCREMENT,
	`user` int unsigned NOT NULL,
	`token` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	`scope` enum('verification','password') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
	`issued` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `user` (`user`),
	CONSTRAINT `usertokens_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2021-04-12 15:07:51
