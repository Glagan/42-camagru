<?php

class AllTablesMigration
{
	public function run()
	{
		$queries = [];

		$queries[] = "CREATE TABLE `users` (
			`id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`username` varchar(250) NOT NULL,
			`email` varchar(250) NOT NULL,
			`password` varchar(250) NOT NULL,
			`verified` tinyint unsigned NOT NULL DEFAULT '0',
			`theme` enum('light','dark') NOT NULL DEFAULT 'light',
			`receiveComments` tinyint unsigned NOT NULL DEFAULT '1',
			UNIQUE KEY `username` (`username`),
			UNIQUE KEY `mail` (`mail`)
		) ENGINE='InnoDB';";

		$queries[] = "CREATE TABLE `images` (
			`id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`user` int unsigned NOT NULL,
			`path` varchar(250) NOT NULL,
			`private` tinyint NOT NULL DEFAULT '0',
			`at` datetime NOT NULL DEFAULT NOW(),
			FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE
		) ENGINE='InnoDB';";

		$queries[] = "CREATE TABLE `likes` (
			`id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`user` int unsigned NOT NULL,
			`image` int unsigned NOT NULL,
			`at` datetime NOT NULL DEFAULT NOW(),
			FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
			FOREIGN KEY (`image`) REFERENCES `images` (`id`) ON DELETE CASCADE
		) ENGINE='InnoDB';";

		$queries[] = "CREATE TABLE `comments` (
			`id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`image` int unsigned NOT NULL,
			`user` int unsigned NOT NULL,
			`message` text NOT NULL,
			`at` datetime NOT NULL DEFAULT NOW(),
			`edited` datetime NULL,
			`deleted` tinyint NOT NULL DEFAULT '0',
			FOREIGN KEY (`image`) REFERENCES `images` (`id`) ON DELETE CASCADE,
			FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE
		) ENGINE='InnoDB';";

		// TODO
	}
}
