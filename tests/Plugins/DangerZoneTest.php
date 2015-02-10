<?php

namespace Nomibot\Plugins;

use Mockery as m;
use Phergie\Irc\Bot\React\AbstractPlugin;

class DangerZoneTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testConstructor()
    {
        $plugin = new DangerZone;
        $this->assertInstanceOf('Phergie\Irc\Bot\React\AbstractPlugin', $plugin);
    }

    public function testGetSubscribedEvents()
    {
        $plugin = new DangerZone;

        $events = $plugin->getSubscribedEvents();

        $this->assertArrayHasKey('irc.received.privmsg', $events);
        $this->assertEquals('handle', $events['irc.received.privmsg']);
    }

    public function testHandleNoDanger()
    {
        $plugin = new DangerZone;

        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $event->shouldReceive('getParams')->once()->andReturn(['text' => 'this is no danger']);
        $queue->shouldReceive('ircPrivmsg')->never();

        $plugin->handle($event, $queue);
    }

    public function testHandleForkBomb()
    {
        $plugin = new DangerZone;

        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $event->shouldReceive('getParams')->once()->andReturn(['text' => 'try running this… :() { :|: & }; :']);
        $event->shouldReceive('getSource')->once()->andReturn('#test');
        $queue->shouldReceive('ircPrivmsg')->once()->with('#test', m::type('string'));

        $plugin->handle($event, $queue);
    }

    public function testHandleRm()
    {
        $plugin = new DangerZone;

        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $event->shouldReceive('getParams')->once()->andReturn(['text' => "this'll fix it: 'rm -rf / --no-preserve-root'"]);
        $event->shouldReceive('getSource')->once()->andReturn('#test');
        $queue->shouldReceive('ircPrivmsg')->once()->with('#test', m::type('string'));

        $plugin->handle($event, $queue);
    }
}
