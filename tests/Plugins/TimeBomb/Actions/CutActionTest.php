<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

use Mockery as m;
use Nomibot\Plugins\TimeBomb\Bomb;
use Nomibot\Plugins\TimeBomb\Player;

class CutActionTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testInvokeGameNotRunning()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(false);

        $action = new CutAction($event, $queue, $game);
        $action();
    }

    public function testInvokeNotBombHolder()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('NotHolder');

        $action = new CutAction($event, $queue, $game);
        $action();
    }

    public function testInvokeWrongChannel()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('notchannel');

        $action = new CutAction($event, $queue, $game);
        $action();
    }

    public function testInvokeNoWire()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $event->shouldReceive('getCustomParams')->andReturn([]);

        $action = new CutAction($event, $queue, $game);
        $action();
    }

    public function testInvokeNotValidWire()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $event->shouldReceive('getCustomParams')->andReturn(['Orange']);
        $game->shouldReceive('getBomb')->andReturn(new Bomb(['Red', 'Green', 'Blue'], 'Green'));

        $action = new CutAction($event, $queue, $game);
        $action();
    }

    public function testInvokeWrongWire()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $event->shouldReceive('getCustomParams')->andReturn(['Red']);
        $game->shouldReceive('getBomb')->andReturn(new Bomb(['Red', 'Green', 'Blue'], 'Green'));
        $queue->shouldReceive('ircKick')->withArgs(['channel', 'Holder', "\x02snip...*BOOM!*\x02"]);
        $game->shouldReceive('end');

        $action = new CutAction($event, $queue, $game);
        $action();
    }

    public function testInvokeTrollWire()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $event->shouldReceive('getCustomParams')->andReturn(['Red']);
        $game->shouldReceive('getBomb')->andReturn(new Bomb(['Red'], null));
        $queue->shouldReceive('ircKick')->withArgs(['channel', 'Holder', "\x02...*trollface.jpg*\x02"]);
        $game->shouldReceive('end');

        $action = new CutAction($event, $queue, $game);
        $action();
    }

    public function testInvokeCorrectWire()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $timer = m::mock('Nomibot\Plugins\TimeBomb\TimerInterface');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $event->shouldReceive('getCustomParams')->andReturn(['Red']);
        $game->shouldReceive('getBomb')->andReturn(new Bomb(['Red'], 'Red'));
        $game->shouldReceive('getTimer')->andReturn($timer);
        $timer->shouldReceive('remaining')->andReturn('42');
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "Holder cut the Red wire.  This has defused the bomb with [\x0242\x02] seconds to spare!"]);
        $game->shouldReceive('end');

        $action = new CutAction($event, $queue, $game);
        $action();
    }
}
