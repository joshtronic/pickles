<?php

class ProfilerTest extends PHPUnit_Framework_TestCase
{
    public function testReport()
    {
        $this->expectOutputRegex('//');

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

        Pickles\Profiler::query('SELECT * FROM table;');
        Pickles\Profiler::query('SELECT * FROM table WHERE column = ?;', ['foo']);
        Pickles\Profiler::query('SELECT * FROM table;', false, $explain);
    }

    public function testTimer()
    {
        Pickles\Profiler::timer('timer-two');
        Pickles\Profiler::timer('timer-two');
    }
}

