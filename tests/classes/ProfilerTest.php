<?php

class ProfilerTest extends PHPUnit_Framework_TestCase
{
	public function testReport()
	{
		$this->expectOutputRegex('//');

		Profiler::report();
	}

	public function testDisabledType()
	{
		$config = Config::getInstance();
		$config->data['pickles']['profiler'] = false;

		$this->assertFalse(Profiler::enabled('timers'));
	}

	public function testTimerDisabled()
	{
		$this->assertFalse(Profiler::timer('disabled'));
	}

	public function testReportNothing()
	{
		$this->expectOutputRegex('/There is nothing to profile/');

		Profiler::report();
	}

	public function testEnabled()
	{
		$config = Config::getInstance();
		$config->data['pickles']['profiler'] = true;

		$this->assertTrue(Profiler::enabled());
	}

	public function testEnabledType()
	{
		$config = Config::getInstance();
		$config->data['pickles']['profiler'] = 'timers';

		$this->assertTrue(Profiler::enabled('timers'));
	}

	public function testLogAndTimer()
	{
		Profiler::log('timer', 'timer-one');
		Profiler::log(['foo' => 'bar']);
		Profiler::log(new Object);
		Profiler::log('string');
		Profiler::log(3.14, 'method', true);
		Profiler::log('timer', 'timer-one');
	}

	public function testLogQuery()
	{
		$explain = [
			[
				'key'           => '',
				'possible_keys' => '',
				'type'          => '',
				'rows'          => '',
				'Extra'         => '',
			],
		];

		Profiler::logQuery('SELECT * FROM table;');
		Profiler::logQuery('SELECT * FROM table WHERE column = ?;', ['foo']);
		Profiler::logQuery('SELECT * FROM table;', false, $explain);
	}

	public function testTimer()
	{
		Profiler::timer('timer-two');
		Profiler::timer('timer-two');
	}
}

?>
