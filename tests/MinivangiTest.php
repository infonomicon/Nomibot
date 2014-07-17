<?php

namespace Infonomicon\IrcBot;

use Mockery as m;
use Phergie\Irc\Bot\React\AbstractPlugin;

class MinivangiTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testConstructor()
    {
        $plugin = new Minivangi;
        $this->assertTrue($plugin instanceof AbstractPlugin);
    }

    public function testGetSubscribedEvents()
    {
        $plugin = new Minivangi;

        $this->assertEquals($plugin->getSubscribedEvents(), [
            'irc.received.privmsg' => 'handle',
        ]);
    }

    public function testHandle()
    {
        $plugin = new Minivangi;

        $event = m::mock('Phergie\Irc\Event\UserEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $event->shouldReceive('getSource')->andReturn('#test');
        $event->shouldReceive('getNick')->once()->andReturn('tester');
        $queue->shouldReceive('ircPrivmsg')->never();

        $plugin->handle($event, $queue);

        $event->shouldReceive('getNick')->once()->andReturn('mirovengi');
        $event->shouldReceive('getParams')->once()->andReturn(['text' => 'hello']);
        $queue->shouldReceive('ircPrivmsg')->never();

        $plugin->handle($event, $queue);
        
        $event->shouldReceive('getNick')->once()->andReturn('mirovengi');
        $event->shouldReceive('getParams')->once()->andReturn(['text' => 'good morning']);
        $queue->shouldReceive('ircPrivmsg')->once()->with($event->getSource(), "morning, minivangi");

        $plugin->handle($event, $queue);
    }
}
