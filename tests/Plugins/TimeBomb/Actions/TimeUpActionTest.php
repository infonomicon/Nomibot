<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

use Mockery as m;
use Nomibot\Plugins\TimeBomb\Player;

class TimeUpActionTest extends \PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game
            ->shouldReceive('getBombHolder')
            ->andReturn(new Player('holder'));

        $event
            ->shouldReceive('getSource')
            ->andReturn('channel');

        $queue
            ->shouldReceive('ircKick')
            ->withArgs(['channel', 'holder', "\x02*BOOM!*\x02"]);

        $game->shouldReceive('end');

        $action = new TimeUpAction($event, $queue, $game);

        $action();
    }
}
