<?php

namespace Nomibot\Plugins\TimeBomb;

require __DIR__.'/Actions/TestAction.php';

use Mockery as m;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    private function filePath()
    {
        return __DIR__.'/test_optouts.json';
    }

    private function getPlugin()
    {
        return new Plugin(['optout_file' => $this->filePath()]);
    }

    public function setUp()
    {
        file_put_contents($this->filePath(), '["OptedOut", "Tester"]');
    }

    public function tearDown()
    {
        unlink($this->filePath());
        m::close();
    }

    public function testConstruct()
    {
        $plugin = $this->getPlugin();

        $this->assertInstanceOf('Phergie\Irc\Bot\React\AbstractPlugin', $plugin);
        $this->assertInstanceOf('Phergie\Irc\Client\React\LoopAwareInterface', $plugin);
    }

    public function testGetSubscribedEvents()
    {
        $plugin = $this->getPlugin();

        $events = $plugin->getSubscribedEvents();

        $this->assertArrayHasKey('command.timebomb', $events);
        $this->assertArrayHasKey('command.bombtoss', $events);
        $this->assertArrayHasKey('command.cut', $events);
        $this->assertArrayHasKey('command.bombout', $events);
        $this->assertArrayHasKey('command.bombin', $events);
        $this->assertArrayHasKey('command.timebomb.help', $events);
        $this->assertArrayHasKey('command.bombtoss.help', $events);
        $this->assertArrayHasKey('command.cut.help', $events);
        $this->assertArrayHasKey('command.bombout.help', $events);
        $this->assertArrayHasKey('command.bombin.help', $events);
    }

    public function testMagicCall()
    {
        $plugin = $this->getPlugin();

        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');

        $this->assertEquals('ok', $plugin->test($event, $queue));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMagicCallInvalidArgument()
    {
        $plugin = $this->getPlugin();
        $plugin->test();
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testMagicCallBadMethod()
    {
        $plugin = $this->getPlugin();

        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');

        $plugin->unknown($event, $queue);
    }
}
