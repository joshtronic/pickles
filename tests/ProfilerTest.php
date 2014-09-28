<?php

class ProfilerTest extends PHPUnit_Framework_TestCase
{
    public function testReport()
    {
        $this->expectOutputRegex('//');

        Pickles\Profiler::report();
    }

    public function testDisabledType()
    {
        $config = Pickles\Config::getInstance();
        $config->data['pickles']['profiler'] = false;

        $this->assertFalse(Pickles\Profiler::enabled('timers'));
    }

    public function testTimerDisabled()
    {
        $this->assertFalse(Pickles\Profiler::timer('disabled'));
    }

    public function testReportNothing()
    {
        $this->expectOutputRegex('/There is nothing to profile/');

        Pickles\Profiler::report();
    }

    public function testEnabled()
    {
        $config = Pickles\Config::getInstance();
        $config->data['pickles']['profiler'] = true;

        $this->assertTrue(Pickles\Profiler::enabled());
    }

    public function testEnabledType()
    {
        $config = Pickles\Config::getInstance();
        $config->data['pickles']['profiler'] = 'timers';

        $this->assertTrue(Pickles\Profiler::enabled('timers'));
    }

    public function testLogAndTimer()
    {
        Pickles\Profiler::log('timer', 'timer-one');
        Pickles\Profiler::log(['foo' => 'bar']);
        Pickles\Profiler::log(new Pickles\Object);
        Pickles\Profiler::log('string');
        Pickles\Profiler::log(3.14, 'method', true);
        Pickles\Profiler::log('timer', 'timer-one');
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

        Pickles\Profiler::logQuery('SELECT * FROM table;');
        Pickles\Profiler::logQuery('SELECT * FROM table WHERE column = ?;', ['foo']);
        Pickles\Profiler::logQuery('SELECT * FROM table;', false, $explain);
    }

    public function testTimer()
    {
        Pickles\Profiler::timer('timer-two');
        Pickles\Profiler::timer('timer-two');
    }
}

