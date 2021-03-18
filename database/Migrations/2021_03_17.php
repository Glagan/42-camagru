<?php

class UserSessionMigration
{
	public function run()
	{
		$query = "CREATE TABLE `usersessions` (
			`id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`user` int unsigned NOT NULL,
			`session` varchar(250) NOT NULL,
			`issued` datetime NOT NULL DEFAULT NOW(),
			FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE
		) ENGINE='InnoDB';";

		// TODO
	}
}
