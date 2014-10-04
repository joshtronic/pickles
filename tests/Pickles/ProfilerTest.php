<?php

class ProfilerTest extends PHPUnit_Framework_TestCase
{
    public function testProfiler()
    {
        // Clears out any previous logging
        Pickles\Profiler::report();

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SERVER_NAME']    = '127.0.0.1';

        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "profiler" => true,
                ],
            ];
        ');

        new Pickles\Config('/tmp/pickles.php');

        Pickles\Profiler::log('i am a string');
        Pickles\Profiler::log(['foo' => 'bar']);
        Pickles\Profiler::log($this, 'testProfiler');
        Pickles\Profiler::timer('swatch');
        Pickles\Profiler::query('SELECT', ['foo' => 'bar'], ['results'], 1, 'EXPLAIN');
        Pickles\Profiler::timer('swatch');
        Pickles\Profiler::query('SELECT', ['foo' => 'bar'], ['results'], 1);

        $report = Pickles\Profiler::report();

        $this->assertEquals(7, count($report));
        $this->assertEquals(7, count($report['logs']));
        $this->assertEquals(5, count($report['logs'][0]));
        $this->assertEquals('string', $report['logs'][0]['type']);
        $this->assertEquals('i am a string', $report['logs'][0]['details']);
        $this->assertEquals('array', $report['logs'][1]['type']);
        $this->assertEquals(['foo' => 'bar'], $report['logs'][1]['details']);
        $this->assertEquals('object', $report['logs'][2]['type']);
        $this->assertEquals(['class' => 'ProfilerTest', 'method' => 'testProfiler()'], $report['logs'][2]['details']);
        $this->assertEquals('timer', $report['logs'][3]['type']);
        $this->assertEquals('swatch', $report['logs'][3]['details']['name']);
        $this->assertEquals('start', $report['logs'][3]['details']['action']);
        $this->assertEquals('database', $report['logs'][4]['type']);
        $this->assertEquals('SELECT', $report['logs'][4]['details']['query']);
        $this->assertEquals(['foo' => 'bar'], $report['logs'][4]['details']['parameters']);
        $this->assertEquals(['results'], $report['logs'][4]['details']['results']);
        $this->assertEquals(1, $report['logs'][4]['details']['execution_time']);
        $this->assertEquals('EXPLAIN', $report['logs'][4]['details']['explain']);
        $this->assertEquals('timer', $report['logs'][5]['type']);
        $this->assertEquals('swatch', $report['logs'][5]['details']['name']);
        $this->assertEquals('stop', $report['logs'][5]['details']['action']);
        $this->assertEquals('database', $report['logs'][6]['type']);
        $this->assertEquals('SELECT', $report['logs'][6]['details']['query']);
        $this->assertEquals(['foo' => 'bar'], $report['logs'][6]['details']['parameters']);
        $this->assertEquals(['results'], $report['logs'][6]['details']['results']);
        $this->assertEquals(1, $report['logs'][6]['details']['execution_time']);
        $this->assertFalse(isset($report['logs'][6]['details']['explain']));
    }
}

