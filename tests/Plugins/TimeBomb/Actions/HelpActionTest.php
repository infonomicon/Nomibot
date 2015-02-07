<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

use Mockery as m;

class HelpActionTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testInvokeHelpTimebomb()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $event
            ->shouldReceive('getCustomCommand')
            ->once()
            ->andReturn('help');
        $event
            ->shouldReceive('getCustomParams')
            ->once()
            ->andReturn(['timebomb']);
        $event
            ->shouldReceive('getSource')
            ->times(3)
            ->andReturn('channel');
        $queue
            ->shouldReceive('ircPrivmsg')
            ->times(3);

        $action = new HelpAction($event, $queue, $game);
        $action();
    }

    public function testInvokeTimebombHelp()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $event
            ->shouldReceive('getCustomCommand')
            ->once()
            ->andReturn('timebomb.help');
        $event
            ->shouldReceive('getSource')
            ->times(3)
            ->andReturn('channel');
        $queue
            ->shouldReceive('ircPrivmsg')
            ->times(3);

        $action = new HelpAction($event, $queue, $game);
        $action();
    }


    public function testInvokeInvalid()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $event
            ->shouldReceive('getCustomCommand')
            ->once()
            ->andReturn('invalid.help');
        $queue
            ->shouldReceive('ircPrivmsg')
            ->never();

        $action = new HelpAction($event, $queue, $game);
        $action();
    }
}
