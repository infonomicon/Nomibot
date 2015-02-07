<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

use Mockery as m;

class OptInActionTest extends \PHPUnit_Framework_TestCase
{
    public function testInvokeOptedOut()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');

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
        $optouts
            ->shouldReceive('remove')
            ->with('OptedOut');

        $queue
            ->shouldReceive('ircPrivmsg')
            ->withArgs(['channel', "OptedOut: You've opted back in to the timebomb game."]);

        $action = new OptInAction($event, $queue, $game);

        $action();
    }

    public function testInvokeOptedIn()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');

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

        $queue
            ->shouldReceive('ircPrivmsg')
            ->withArgs(['channel', "Tester: You're already opted in."]);

        $action = new OptInAction($event, $queue, $game);

        $action();
    }
}
