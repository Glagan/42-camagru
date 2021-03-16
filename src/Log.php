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
			$this->file = \fopen(Env::$config['log']['folder'] . $day . '.log', 'a');
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

	private function line(string $line)
	{
		$date = (new DateTime());
		$this->checkFile($date);

		// Add Date and line ending
		$line = $date->format('Y-m-d H:i:s.v') . ' ' . $line . "\n";
		\fwrite(static::$instance->file, $line);
	}

	public static function debug($value)
	{
		if (Env::$config['camagru']['mode'] == 'debug') {
			$log = static::getInstance();
			if (\is_array($value)) {
				$log->line(\trim(\print_r($value, true)));
			} else {
				$log->line($value);
			}
		}
	}

	public function __destruct()
	{
		\fclose($this->file);
	}
}
