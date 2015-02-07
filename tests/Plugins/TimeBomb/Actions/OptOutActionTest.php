<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

use Mockery as m;

class OptOutActionTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testInvokeGameRunning()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $event
            ->shouldReceive('getNick')
            ->andReturn('Tester');
        $game
            ->shouldReceive('isRunning')
            ->andReturn(true);
        $event
            ->shouldReceive('getSource')
            ->andReturn('channel');
        $queue
            ->shouldReceive('ircPrivmsg')
            ->withArgs(['channel', "Tester: you coward! No opting out during a game!"]);

        $action = new OptOutAction($event, $queue, $game);
        $action();
    }

    public function testInvokeOptedOut()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');

        $game
            ->shouldReceive('isRunning')
            ->andReturn(false);
        $game
            ->shouldReceive('getOptOuts')
            ->andReturn($optouts);
        $event
            ->shouldReceive('getNick')
            ->andReturn('OptedOut');
        $event
            ->shouldReceive('getSource')
            ->andReturn('channel');
        $optouts
            ->shouldReceive('contains')
            ->with('OptedOut')
            ->andReturn(true);
        $queue
            ->shouldReceive('ircPrivmsg')
            ->withArgs(['channel', "OptedOut: You're already opted out."]);

        $action = new OptOutAction($event, $queue, $game);
        $action();
    }

    public function testInvokeOptedIn()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');

        $game
            ->shouldReceive('isRunning')
            ->andReturn(false);
        $game
            ->shouldReceive('getOptOuts')
            ->andReturn($optouts);
        $event
            ->shouldReceive('getNick')
            ->andReturn('Tester');
        $event
            ->shouldReceive('getSource')
            ->andReturn('channel');
        $optouts
            ->shouldReceive('contains')
            ->with('Tester')
            ->andReturn(false);
        $optouts
            ->shouldReceive('add')
            ->with('Tester');
        $queue
            ->shouldReceive('ircPrivmsg')
            ->withArgs(['channel', "Tester: You've opted out of the timebomb game."]);

        $action = new OptOutAction($event, $queue, $game);
        $action();
    }
}
