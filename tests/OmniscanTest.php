<?php

namespace Infonomicon\IrcBot;

use Mockery as m;
use Phergie\Irc\Bot\React\AbstractPlugin;

class OmniscanTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testConstructor()
    {
        $plugin = new Omniscan;
        $this->assertTrue($plugin instanceof AbstractPlugin);
    }

    public function testGetSubscribedEvents()
    {
        $plugin = new Omniscan;

        $this->assertEquals($plugin->getSubscribedEvents(), [
            'command.omniscan' => 'handle',
        ]);
    }

    public function testHandle()
    {
        $plugin = new Omniscan;

        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $event->shouldReceive('getSource')->andReturn('#test');
        $event->shouldReceive('getCustomParams')->once()->andReturn([]);
        $event->shouldReceive('getNick')->once()->andReturn('tester');
        $queue->shouldReceive('ircPrivmsg')->once()->with($event->getSource(), "Omniscan, tester!");

        $plugin->handle($event, $queue);

        $event->shouldReceive('getCustomParams')->once()->andReturn(['fool']);
        $queue->shouldReceive('ircPrivmsg')->once()->with($event->getSource(), "Omniscan, fool!");

        $plugin->handle($event, $queue);
    }
}
