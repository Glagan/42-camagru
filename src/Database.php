<?php

class Database
{
	private static $instance = null;
	private $connection = null;

	public function __construct()
	{
		[
			'host' => $host,
			'port' => $port,
			'username' => $username,
			'password' => $password,
			'db' => $db,
			'charset' => $charset,
		] = Env::getNamespace('MySQL');
		$this->connection = new \PDO(
			"mysql:host={$host};port={$port};dbname={$db};charset={$charset};",
			$username, $password,
			[
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_EMULATE_PREPARES => false,
			]
		);
	}

	/**
	 * Create or return the existing MySQL connection trough PDO.
	 * @return \PDO
	 */
	public static function connection(): \PDO
	{
		if (Database::$instance == null) {
			Database::$instance = new Database;
		}
		return Database::$instance->connection;
	}

	/**
	 * Last inserted ID.
	 * @return integer
	 */
	public static function lastId(): int
	{
		if (Database::$instance == null) {
			return -1;
		}
		return Database::$instance->connection->lastInsertId();
	}

	public function __destruct()
	{
		if (static::$instance->connection) {
			static::$instance->connection = null;
		}
	}
}
