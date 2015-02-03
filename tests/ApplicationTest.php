<?php

namespace Nomibot;

use Mockery as m;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testRun()
    {
        $app = new Application;

        $bot = m::mock('Phergie\Irc\Bot\React\Bot');
        $logger = m::mock('Monolog\Logger');

        $bot->shouldReceive('getLogger')->andReturn($logger);
        $bot->shouldReceive('setConfig')->once();
        $bot->shouldReceive('run')->once();

        $logger->shouldReceive('popHandler');
        $logger->shouldReceive('pushHandler');

        $app['irc.bot'] = $bot;
        $app['irc.connection'] = null;
        $app['logger.handler'] = new \Monolog\Handler\NullHandler();
        $app['config'] = [
            'enabled_plugins' => [],
        ];

        $app->run();
    }
}
