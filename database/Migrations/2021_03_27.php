<?php

class UserTokenMigration
{
	public function run()
	{
		$query = "CREATE TABLE `usertokens` (
			`id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`user` int unsigned NOT NULL,
			`token` varchar(50) NOT NULL,
			`scope` enum('verification','password') NOT NULL,
			`issued` datetime NOT NULL DEFAULT NOW(),
			FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE
		);";

		// TODO
	}
}
