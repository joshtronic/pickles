<?php

class LogTest extends PHPUnit_Framework_TestCase
{
	private $config;

	public function setUp()
	{
		$this->config = Config::getInstance();
		$this->config->data['pickles']['logging'] = true;
	}

	public static function tearDownAfterClass()
	{
		File::removeDirectory(LOG_PATH);
	}

	public function testInformation()
	{
		Log::information('information');

		$file = LOG_PATH . date('Y/m/d/') . 'information.log';
		$data = file($file);
		$line = $data[count($data) - 1];

		$this->assertRegExp('/^\d{2}:\d{2}:\d{2} .+ information$/', $line);
	}

	public function testWarning()
	{
		Log::warning('warning');

		$file = LOG_PATH . date('Y/m/d/') . 'warning.log';
		$data = file($file);
		$line = $data[count($data) - 1];

		$this->assertRegExp('/^\d{2}:\d{2}:\d{2} .+ warning$/', $line);
	}

	public function testError()
	{
		Log::error('error');

		$file = LOG_PATH . date('Y/m/d/') . 'error.log';
		$data = file($file);
		$line = $data[count($data) - 1];

		$this->assertRegExp('/^\d{2}:\d{2}:\d{2} .+ error$/', $line);
	}

	public function testSlowQuery()
	{
		Log::slowQuery('slow query');

		$file = LOG_PATH . date('Y/m/d/') . 'slow_query.log';
		$data = file($file);
		$line = $data[count($data) - 1];

		$this->assertRegExp('/^\d{2}:\d{2}:\d{2} .+ slow query$/', $line);
	}

	public function testTransaction()
	{
		Log::transaction('transaction');

		$file = LOG_PATH . date('Y/m/d/') . 'transaction.log';
		$data = file($file);
		$line = $data[count($data) - 1];

		$this->assertRegExp('/^\d{2}:\d{2}:\d{2} .+ transaction$/', $line);
	}

	public function testPHPError()
	{
		Log::phperror('php error');

		$file = LOG_PATH . date('Y/m/d/') . 'php_error.log';
		$data = file($file);
		$line = $data[count($data) - 1];

		$this->assertRegExp('/^php error$/', $line);
	}

	public function testQuery()
	{
		Log::query('query');

		$file = LOG_PATH . date('Y/m/d/') . 'query.log';
		$data = file($file);
		$line = $data[count($data) - 1];

		$this->assertRegExp('/^\d{2}:\d{2}:\d{2} .+ query$/', $line);
	}

	public function testLoggingDisabled()
	{
		$this->config->data['pickles']['logging'] = false;

		$this->assertFalse(Log::error('should return false'));
	}
}

?>
