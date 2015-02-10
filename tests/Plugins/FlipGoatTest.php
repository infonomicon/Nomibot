<?php

namespace Nomibot\Plugins;

use Mockery as m;
use Phergie\Irc\Bot\React\AbstractPlugin;

class FlipGoatTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testConstructor()
    {
        $plugin = new FlipGoat;
        $this->assertTrue($plugin instanceof AbstractPlugin);
    }

    public function testGetSubscribedEvents()
    {
        $plugin = new FlipGoat;

        $events = $plugin->getSubscribedEvents();

        $this->assertArrayHasKey('command.flipgoat', $events);
        $this->assertArrayHasKey('command.flipgoat.help', $events);
        $this->assertEquals('handle', $events['command.flipgoat']);
        $this->assertEquals('help', $events['command.flipgoat.help']);
    }

    public function testHandle()
    {
        $plugin = new FlipGoat;

        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $event->shouldReceive('getSource')->andReturn('#test');
        $queue->shouldReceive('ircPrivmsg')->times(4)->with('#test', m::type('string'));

        $plugin->handle($event, $queue);
    }

    public function testHelp()
    {
        $plugin = new FlipGoat;

        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $event->shouldReceive('getSource')->andReturn('#test');
        $queue->shouldReceive('ircPrivmsg')->times(3)->with('#test', m::type('string'));

        $plugin->help($event, $queue);
    }
}
