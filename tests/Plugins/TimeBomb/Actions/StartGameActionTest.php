<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

use Mockery as m;
use Nomibot\Plugins\TimeBomb\Bomb;
use Nomibot\Plugins\TimeBomb\Player;

class StartGameActionTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testInvokeIsRunningInChannel()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getChannel')->andReturn('channel');
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Tester'));
        $event->shouldReceive('getSource')->andReturn('channel');
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "\x01ACTION points at the bulge in the back of Tester's pants.\x01"]);

        $action = new StartGameAction($event, $queue, $game);
        $action();
    }

    public function testInvokeIsRunningNotChannel()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('notchannel');
        $queue->shouldReceive('ircPrivmsg')->withArgs(['notchannel', "I don't have a single bomb to spare. :-("]);

        $action = new StartGameAction($event, $queue, $game);
        $action();
    }

    public function testInvokeStarterOptedOut()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');

        $game->shouldReceive('isRunning')->andReturn(false);
        $event->shouldReceive('getNick')->andReturn('OptedOut');
        $game->shouldReceive('getOptOuts')->andReturn($optouts);
        $optouts->shouldReceive('contains')->with('OptedOut')->andReturn(true);
        $event->shouldReceive('getSource')->andReturn('channel');
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "You can't bomb anyone if you're not opted in!"]);

        $action = new StartGameAction($event, $queue, $game);
        $action();
    }

    public function testInvokeNickIsBot()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');
        $connection = m::mock('Phergie\Irc\ConnectionInterface');

        $game->shouldReceive('isRunning')->andReturn(false);
        $event->shouldReceive('getNick')->andReturn('Tester');
        $game->shouldReceive('getOptOuts')->andReturn($optouts);
        $optouts->shouldReceive('contains')->with('Tester')->andReturn(false);
        $event->shouldReceive('getCustomParams')->andReturn(['TheBot']);
        $event->shouldReceive('getConnection')->andReturn($connection);
        $connection->shouldReceive('getNickname')->andReturn('TheBot');
        $event->shouldReceive('getSource')->andReturn('channel');
        $queue->shouldReceive('ircKick')->withArgs(['channel', 'Tester', "I will not tolerate this!"]);

        $action = new StartGameAction($event, $queue, $game);
        $action();
    }

    public function testInvokeNickIsOptedOut()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');
        $connection = m::mock('Phergie\Irc\ConnectionInterface');
        $timer = m::mock('Nomibot\Plugins\TimeBomb\TimerInterface');

        $game->shouldReceive('isRunning')->andReturn(false);
        $event->shouldReceive('getNick')->andReturn('Tester');
        $game->shouldReceive('getOptOuts')->andReturn($optouts);
        $optouts->shouldReceive('contains')->with('Tester')->andReturn(false);
        $event->shouldReceive('getCustomParams')->andReturn(['OptedOut']);
        $event->shouldReceive('getConnection')->andReturn($connection);
        $connection->shouldReceive('getNickname')->andReturn('TheBot');
        $optouts->shouldReceive('contains')->with('OptedOut')->andReturn(true);
        $event->shouldReceive('getSource')->andReturn('channel');
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "OptedOut isn't playing..."]);
        $game->shouldReceive('setChannel')->with('channel');
        $game->shouldReceive('start')->with('Tester')->with('Tester')->withAnyArgs();
        $game->shouldReceive('getTimer')->andReturn($timer);
        $timer->shouldReceive('total')->andReturn(120);
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Tester'));
        $game->shouldReceive('getBomb')->andReturn(new Bomb(['Red', 'Green', 'Blue'], 'Green'));
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "\x01ACTION stuffs the bomb into Tester's pants.  The display reads [\x02120\x02] seconds.\x01"]);
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "Defuse the bomb by cutting the correct wire. There are three wires. They are Red, Green, and Blue.  Use !cut <color>"]);

        $action = new StartGameAction($event, $queue, $game);
        $action();
    }

    public function testInvokeNickIsInvalid()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');
        $connection = m::mock('Phergie\Irc\ConnectionInterface');
        $timer = m::mock('Nomibot\Plugins\TimeBomb\TimerInterface');

        $game->shouldReceive('isRunning')->andReturn(false);
        $event->shouldReceive('getNick')->andReturn('Tester');
        $game->shouldReceive('getOptOuts')->andReturn($optouts);
        $optouts->shouldReceive('contains')->with('Tester')->andReturn(false);
        $event->shouldReceive('getCustomParams')->andReturn(['P$@#()v']);
        $event->shouldReceive('getConnection')->andReturn($connection);
        $connection->shouldReceive('getNickname')->andReturn('TheBot');
        $optouts->shouldReceive('contains')->with('P$@#()v')->andReturn(false);
        $event->shouldReceive('getSource')->andReturn('channel');
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "What kind of nick is P$@#()v!?"]);
        $game->shouldReceive('setChannel')->with('channel');
        $game->shouldReceive('start')->with('Tester')->with('Tester')->withAnyArgs();
        $game->shouldReceive('getTimer')->andReturn($timer);
        $timer->shouldReceive('total')->andReturn(120);
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Tester'));
        $game->shouldReceive('getBomb')->andReturn(new Bomb(['Green', 'Blue'], 'Green'));
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "\x01ACTION stuffs the bomb into Tester's pants.  The display reads [\x02120\x02] seconds.\x01"]);
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "Defuse the bomb by cutting the correct wire. There are two wires. They are Green and Blue.  Use !cut <color>"]);

        $action = new StartGameAction($event, $queue, $game);
        $action();
    }

    public function testInvokeWithNickOption()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');
        $connection = m::mock('Phergie\Irc\ConnectionInterface');
        $timer = m::mock('Nomibot\Plugins\TimeBomb\TimerInterface');

        $game->shouldReceive('isRunning')->andReturn(false);
        $event->shouldReceive('getNick')->andReturn('Tester');
        $game->shouldReceive('getOptOuts')->andReturn($optouts);
        $optouts->shouldReceive('contains')->with('Tester')->andReturn(false);
        $event->shouldReceive('getCustomParams')->andReturn(['Receiver']);
        $event->shouldReceive('getConnection')->andReturn($connection);
        $connection->shouldReceive('getNickname')->andReturn('TheBot');
        $optouts->shouldReceive('contains')->with('Receiver')->andReturn(false);
        $game->shouldReceive('setChannel')->with('channel');
        $game->shouldReceive('start')->with('Tester')->with('Receiver')->withAnyArgs();
        $game->shouldReceive('getTimer')->andReturn($timer);
        $timer->shouldReceive('total')->andReturn(120);
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Receiver'));
        $game->shouldReceive('getBomb')->andReturn(new Bomb(['Green'], 'Green'));
        $event->shouldReceive('getSource')->andReturn('channel');
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "\x01ACTION stuffs the bomb into Receiver's pants.  The display reads [\x02120\x02] seconds.\x01"]);
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "Defuse the bomb by cutting the correct wire. There is one wire. It is Green.  Use !cut <color>"]);

        $action = new StartGameAction($event, $queue, $game);
        $action();
    }
}
