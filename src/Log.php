<?php

class Log
{
	/**
	 * @var Log
	 */
	private static $instance = null;
	/**
	 * @var string
	 */
	private $day = null;
	/**
	 * @var string
	 */
	private $file = null;

	private function __construct()
	{
		$this->checkFile(new \DateTime);
	}

	/**
	 * Create the file if it doesn't exists
	 * 	Or if the Date changed
	 * @param \DateTIme $date
	 * @return void
	 */
	private function checkFile(\DateTime $date)
	{
		$day = $date->format('Y_m_d');
		if ($this->file == null || $day != $this->day) {
			if ($this->file != null) {
				\fclose($this->file);
			}
			$day = $date->format('Y_m_d');
			$folder = Env::get('Camagru', 'root') . '/' . Env::get('Log', 'folder', '/storage/logs/');
			$this->file = \fopen($folder . $day . '.log', 'a');
			$this->day = $day;
		}
	}

	/**
	 * @return Log
	 */
	private static function getInstance(): Log
	{
		if (static::$instance == null) {
			static::$instance = new Log;
		}
		return static::$instance;
	}

	/**
	 * Write a line to the log file.
	 * @param string $prefix
	 * @param string|array $line
	 * @return void
	 */
	private function addLine(string $prefix, $line): void
	{
		if (\is_array($line)) {
			// substr remove "Array\n" and line ending
			// Avoid removing prefix line ending if there is none
			$line = \substr(\print_r($line, true), $prefix ? 5 : 6, -1);
		}
		$line = $prefix . $line . "\n";
		\fwrite(static::$instance->file, $line);
	}

	/**
	 * Check if the opened file is the correct one and call addLine.
	 * @param string|array $line
	 * @return void
	 */
	private function line($line): void
	{
		$date = (new DateTime());
		$this->checkFile($date);
		$this->addLine($date->format('Y-m-d H:i:s.v') . ' ', $line);
	}

	/**
	 * Check if the opened file is the correct one and call addLine without a prefix.
	 * @param string|array $line
	 * @return void
	 */
	private function continueLine($line): void
	{
		$this->checkFile(new DateTime());
		$this->addLine('', $line);
	}

	/**
	 * Log all the given values.
	 * @param (string|array)[] $values
	 * @return void
	 */
	public static function debug(...$values): void
	{
		if (Env::get('Camagru', 'mode') == 'debug') {
			$log = static::getInstance();
			$i = 0;
			foreach ($values as $value) {
				if ($i == 0) {
					$log->line($value);
					$i++;
				} else {
					$log->continueLine($value);
				}
			}
		}
	}

	/**
	 * Log all the given Exceptions.
	 * @param \Throwable[] $values
	 * @return void
	 */
	public static function error(...$exceptions)
	{
		$log = static::getInstance();
		$i = 0;
		foreach ($exceptions as $exception) {
			$values = [
				'message' => $exception->getMessage(),
				'code' => $exception->getCode(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'trace' => $exception->getTrace(),
			];
			if ($i == 0) {
				$log->line($values);
				$i++;
			} else {
				$log->continueLine($values);
			}
		}
	}

	public function __destruct()
	{
		\fclose($this->file);
	}
}
