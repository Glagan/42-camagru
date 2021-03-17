<?php

class Log
{
	private static $instance = null;
	private $day = null;
	private $file = null;

	private function __construct()
	{
		$this->checkFile(new \DateTime);
	}

	/**
	 * Create the file if it doesn't exists
	 * 	Or if the Date changed
	 *
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
			$this->file = \fopen(Env::get('log', 'folder', '') . $day . '.log', 'a');
			$this->day = $day;
		}
	}

	private static function getInstance(): Log
	{
		if (static::$instance == null) {
			static::$instance = new Log;
		}
		return static::$instance;
	}

	private function addLine($prefix, $line)
	{
		if (\is_array($line)) {
			// substr remove "Array\n" and line ending
			// Avoid removing prefix line ending if there is none
			$line = \substr(\print_r($line, true), $prefix ? 5 : 6, -1);
		}
		$line = $prefix . $line . "\n";
		\fwrite(static::$instance->file, $line);
	}

	private function line($line)
	{
		$date = (new DateTime());
		$this->checkFile($date);
		$this->addLine($date->format('Y-m-d H:i:s.v') . ' ', $line);
	}

	private function continueLine($line)
	{
		$this->checkFile(new DateTime());
		$this->addLine('', $line);
	}

	public static function debug(...$values)
	{
		if (Env::get('camagru', 'mode') == 'debug') {
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
