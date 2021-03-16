<?php

abstract class Database
{
	private static $_connection = null;

	/**
	 * Create or return the existing MySQL connection trough PDO.
	 *
	 * @return \PDO
	 */
	public static function connection(): \PDO
	{
		if (Database::$_connection == null) {
			[
				'host' => $host,
				'port' => $port,
				'username' => $username,
				'password' => $password,
				'db' => $db,
			] = Env::$config['mysql'];
			Database::$_connection = new PDO("mysql:host={$host};port={$port};dbname={$db}", $username, $password);
		}
		return Database::$_connection;
	}

	/**
	 * Last inserted ID.
	 *
	 * @return integer
	 */
	public static function lastId(): int
	{
		if (Database::$_connection == null) {
			return -1;
		}
		return Database::$_connection->lastInsertId();
	}
}
