<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

use Mockery as m;
use Nomibot\Plugins\TimeBomb\Player;

class TossActionTest extends \PHPUnit_Framework_TestCase
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

        $action = new TossAction($event, $queue, $game);
        $action();
    }

    public function testInvokeWrongChannel()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('notchannel');

        $action = new TossAction($event, $queue, $game);
        $action();
    }

    public function testInvokeWrongPlayer()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('NotHolder');
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "Hey! No groping Holder while they have the bomb!"]);

        $action = new TossAction($event, $queue, $game);
        $action();
    }

    public function testInvokeNoNickPassed()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $event->shouldReceive('getCustomParams')->andReturn([]);

        $action = new TossAction($event, $queue, $game);
        $action();
    }

    public function testInvokeNickIsHolder()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $event->shouldReceive('getCustomParams')->andReturn(['Holder']);
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "Holder: You already have the bomb. Dumb ass."]);

        $action = new TossAction($event, $queue, $game);
        $action();
    }

    public function testInvokeNickIsBot()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $connection = m::mock('Phergie\Irc\ConnectionInterface');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $event->shouldReceive('getCustomParams')->andReturn(['TheBot']);
        $event->shouldReceive('getConnection')->andReturn($connection);
        $connection->shouldReceive('getNickname')->andReturn('TheBot');
        $queue->shouldReceive('ircKick')->withArgs(['channel', 'Holder', "I will not tolerate this!"]);
        $game->shouldReceive('end');

        $action = new TossAction($event, $queue, $game);
        $action();
    }

    public function testInvokeNickIsOptedOut()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $connection = m::mock('Phergie\Irc\ConnectionInterface');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $event->shouldReceive('getCustomParams')->andReturn(['OptedOut']);
        $event->shouldReceive('getConnection')->andReturn($connection);
        $connection->shouldReceive('getNickname')->andReturn('TheBot');
        $game->shouldReceive('getOptOuts')->andReturn($optouts);
        $optouts->shouldReceive('contains')->with('OptedOut')->andReturn(true);
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "Sorry. It looks like OptedOut doesn't want the bomb."]);

        $action = new TossAction($event, $queue, $game);
        $action();
    }

    public function testInvokeNickIsInvalid()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $connection = m::mock('Phergie\Irc\ConnectionInterface');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $event->shouldReceive('getCustomParams')->andReturn(['P@#%(F']);
        $event->shouldReceive('getConnection')->andReturn($connection);
        $connection->shouldReceive('getNickname')->andReturn('TheBot');
        $game->shouldReceive('getOptOuts')->andReturn($optouts);
        $optouts->shouldReceive('contains')->with('P@#%(F')->andReturn(false);
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "What kind of nick is P@#%(F!?"]);

        $action = new TossAction($event, $queue, $game);
        $action();
    }

    public function testInvokeBombDisarms()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $connection = m::mock('Phergie\Irc\ConnectionInterface');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $event->shouldReceive('getCustomParams')->andReturn(['Receiver']);
        $event->shouldReceive('getConnection')->andReturn($connection);
        $connection->shouldReceive('getNickname')->andReturn('TheBot');
        $game->shouldReceive('getOptOuts')->andReturn($optouts);
        $optouts->shouldReceive('contains')->with('Receiver')->andReturn(false);
        $game->shouldReceive('disarmOnToss')->andReturn(true);
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "As Holder was tossing the bomb to Receiver, it disarmed!  Everybody wins!"]);
        $game->shouldReceive('end');

        $action = new TossAction($event, $queue, $game);
        $action();
    }

    public function testInvokeBombExplodes()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $connection = m::mock('Phergie\Irc\ConnectionInterface');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $event->shouldReceive('getCustomParams')->andReturn(['Receiver']);
        $event->shouldReceive('getConnection')->andReturn($connection);
        $connection->shouldReceive('getNickname')->andReturn('TheBot');
        $game->shouldReceive('getOptOuts')->andReturn($optouts);
        $optouts->shouldReceive('contains')->with('Receiver')->andReturn(false);
        $game->shouldReceive('disarmOnToss')->andReturn(false);
        $game->shouldReceive('setBombHolder')->with('Receiver');
        $game->shouldReceive('explodeOnToss')->andReturn(true);
        $game->shouldReceive('getPlayerList')->andReturn(['Starter', 'Holder', 'Receiver']);
        $queue->shouldReceive('ircKick')->withArgs(['channel', 'Starter', "\x02The bomb is fragile...*BOOM!*\x02"]);
        $queue->shouldReceive('ircKick')->withArgs(['channel', 'Holder', "\x02The bomb is fragile...*BOOM!*\x02"]);
        $queue->shouldReceive('ircKick')->withArgs(['channel', 'Receiver', "\x02The bomb is fragile...*BOOM!*\x02"]);
        $game->shouldReceive('end');

        $action = new TossAction($event, $queue, $game);
        $action();
    }

    public function testInvokeSuccess()
    {
        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $game = m::mock('Nomibot\Plugins\TimeBomb\Game');
        $connection = m::mock('Phergie\Irc\ConnectionInterface');
        $optouts = m::mock('Nomibot\Plugins\TimeBomb\OptOutManager');
        $timer = m::mock('Nomibot\Plugins\TimeBomb\TimerInterface');

        $game->shouldReceive('isRunning')->andReturn(true);
        $game->shouldReceive('getChannel')->andReturn('channel');
        $event->shouldReceive('getSource')->andReturn('channel');
        $game->shouldReceive('getBombHolder')->andReturn(new Player('Holder'));
        $event->shouldReceive('getNick')->andReturn('Holder');
        $event->shouldReceive('getCustomParams')->andReturn(['Receiver']);
        $event->shouldReceive('getConnection')->andReturn($connection);
        $connection->shouldReceive('getNickname')->andReturn('TheBot');
        $game->shouldReceive('getOptOuts')->andReturn($optouts);
        $optouts->shouldReceive('contains')->with('Receiver')->andReturn(false);
        $game->shouldReceive('disarmOnToss')->andReturn(false);
        $game->shouldReceive('setBombHolder')->with('Receiver');
        $game->shouldReceive('explodeOnToss')->andReturn(false);
        $game->shouldReceive('getTimer')->andReturn($timer);
        $timer->shouldReceive('remaining')->andReturn(42);
        $queue->shouldReceive('ircPrivmsg')->withArgs(['channel', "Receiver: Holder set you up the bomb. You have [\x0242\x02] seconds left!"]);

        $action = new TossAction($event, $queue, $game);
        $action();
    }
}
