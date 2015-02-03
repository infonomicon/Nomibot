<?php

namespace Nomibot\Plugins\WordGame;

use Mockery as m;
use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Client\React\LoopAwareInterface;
use Phergie\Irc\Event\EventInterface as Event;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as CommandEvent;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testConstructor()
    {
        $provider = m::mock('Nomibot\Plugins\WordGame\WordProvider');
        $sb = m::mock('Nomibot\Plugins\WordGame\Scoreboard');

        $game = new Plugin($provider, $sb);

        $this->assertTrue($game instanceof AbstractPlugin);
        $this->assertTrue($game instanceof LoopAwareInterface);
    }

    public function testGetSubscribedEvents()
    {
        $provider = m::mock('Nomibot\Plugins\WordGame\WordProvider');
        $sb = m::mock('Nomibot\Plugins\WordGame\Scoreboard');

        $game = new Plugin($provider, $sb);

        $events = array_keys($game->getSubscribedEvents());

        $this->assertContains('command.word', $events);
        $this->assertContains('command.score', $events);
        $this->assertContains('command.word.help', $events);
        $this->assertContains('command.score.help', $events);
        $this->assertContains('irc.received.privmsg', $events);
    }

    public function testStartGame()
    {
        $provider = m::mock('Nomibot\Plugins\WordGame\WordProvider');
        $word = m::mock('Nomibot\Plugins\WordGame\Word');
        $word->shouldReceive('getScrambled')->andReturn('sett');
        $word->shouldReceive('__toString')->andReturn('test');
        $word->shouldReceive('getHint')->andReturn('this is only a...');
        $word->shouldReceive('getFirst')->with(1)->andReturn('t');
        $word->shouldReceive('getFirst')->with(2)->andReturn('te');
        $provider->shouldReceive('getWord')->andReturn($word);
        $sb = m::mock('Nomibot\Plugins\WordGame\Scoreboard');
        $stats = m::mock('Nomibot\Plugins\WordGame\Stats');
        $stats->shouldReceive('getNick')->andReturn('tester');
        $stats->shouldReceive('getTotal')->andReturn(2);
        $stats->shouldReceive('getToday')->andReturn(1);
        $sb->shouldReceive('getStats')->andReturn($stats);
        $sb->shouldReceive('getTopTen')->andReturn([$stats]);
        $sb->shouldReceive('addWin');
        $game = new Plugin($provider, $sb);
        $loop = m::mock('React\EventLoop\LoopInterface');
        $loop->shouldReceive('addPeriodicTimer')->with(15, [$game, 'sendNextUpdate'])->andReturn(m::mock('React\EventLoop\Timer\TimerInterface'));
        $loop->shouldReceive('cancelTimer');
        $game->setLoop($loop);

        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $event->shouldReceive('getSource')->andReturn('#test');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $queue->shouldReceive('ircPrivmsg')->once()->with($event->getSource(), "Unscramble --->  \x036sett");

        $game->startGame($event, $queue);

        $this->assertTrue($game->isRunning());

        $queue->shouldReceive('ircPrivmsg')->once()->with($event->getSource(), "HEY! One word at a time!");
        $game->startGame($event, $queue);

        $this->assertTrue($game->isRunning());
    }

    public function testGameTimeUp()
    {
        $provider = m::mock('Nomibot\Plugins\WordGame\WordProvider');
        $word = m::mock('Nomibot\Plugins\WordGame\Word');
        $word->shouldReceive('getScrambled')->andReturn('sett');
        $word->shouldReceive('__toString')->andReturn('test');
        $word->shouldReceive('getHint')->andReturn('this is only a...');
        $word->shouldReceive('getFirst')->with(1)->andReturn('t');
        $word->shouldReceive('getFirst')->with(2)->andReturn('te');
        $provider->shouldReceive('getWord')->andReturn($word);
        $sb = m::mock('Nomibot\Plugins\WordGame\Scoreboard');
        $stats = m::mock('Nomibot\Plugins\WordGame\Stats');
        $stats->shouldReceive('getNick')->andReturn('tester');
        $stats->shouldReceive('getTotal')->andReturn(2);
        $stats->shouldReceive('getToday')->andReturn(1);
        $sb->shouldReceive('getStats')->andReturn($stats);
        $sb->shouldReceive('getTopTen')->andReturn([$stats]);
        $sb->shouldReceive('addWin');
        $game = new Plugin($provider, $sb);
        $loop = m::mock('React\EventLoop\LoopInterface');
        $loop->shouldReceive('addPeriodicTimer')->with(15, [$game, 'sendNextUpdate'])->andReturn(m::mock('React\EventLoop\Timer\TimerInterface'));
        $loop->shouldReceive('cancelTimer');
        $game->setLoop($loop);

        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $event->shouldReceive('getSource')->andReturn('#test');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $queue->shouldReceive('ircPrivmsg')->once()->with($event->getSource(), "Unscramble --->  \x036sett");

        $game->startGame($event, $queue);

        $queue->shouldReceive('ircPrivmsg')->once()->with($event->getSource(), "Clue --->  \x0312this is only a...");
        $game->sendNextUpdate();
        $this->assertTrue($game->isRunning());
        $queue->shouldReceive('ircPrivmsg')->once()->with($event->getSource(), "First letter --->  \x0312t");
        $game->sendNextUpdate();
        $this->assertTrue($game->isRunning());
        $queue->shouldReceive('ircPrivmsg')->once()->with($event->getSource(), "First two letters --->  \x0312te");
        $game->sendNextUpdate();
        $this->assertTrue($game->isRunning());
        $queue->shouldReceive('ircPrivmsg')->once()->with($event->getSource(), "Nobody got it...it's \x034test");
        $game->sendNextUpdate();

        $this->assertFalse($game->isRunning());
    }

    public function testGameGuesses()
    {
        $provider = m::mock('Nomibot\Plugins\WordGame\WordProvider');
        $word = m::mock('Nomibot\Plugins\WordGame\Word');
        $word->shouldReceive('getScrambled')->andReturn('sett');
        $word->shouldReceive('__toString')->andReturn('test');
        $word->shouldReceive('getHint')->andReturn('this is only a...');
        $word->shouldReceive('getFirst')->with(1)->andReturn('t');
        $word->shouldReceive('getFirst')->with(2)->andReturn('te');
        $provider->shouldReceive('getWord')->andReturn($word);
        $sb = m::mock('Nomibot\Plugins\WordGame\Scoreboard');
        $stats = m::mock('Nomibot\Plugins\WordGame\Stats');
        $stats->shouldReceive('getNick')->andReturn('tester');
        $stats->shouldReceive('getTotal')->andReturn(2);
        $stats->shouldReceive('getToday')->andReturn(1);
        $sb->shouldReceive('getStats')->andReturn($stats);
        $sb->shouldReceive('getTopTen')->andReturn([$stats]);
        $sb->shouldReceive('addWin')->once()->with('tester');
        $game = new Plugin($provider, $sb);
        $loop = m::mock('React\EventLoop\LoopInterface');
        $loop->shouldReceive('addPeriodicTimer')->with(15, [$game, 'sendNextUpdate'])->andReturn(m::mock('React\EventLoop\Timer\TimerInterface'));
        $loop->shouldReceive('cancelTimer');
        $game->setLoop($loop);

        $event = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $event->shouldReceive('getSource')->andReturn('#test');
        $queue = m::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $queue->shouldReceive('ircPrivmsg')->once()->with($event->getSource(), "Unscramble --->  \x036sett");

        $game->startGame($event, $queue);

        $wordEvent = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $wordEvent->shouldReceive('getSource')->andReturn('#test');
        $wordEvent->shouldReceive('getParams')->andReturn(['text' => 'fail']);

        $game->checkWord($wordEvent);

        $this->assertTrue($game->isRunning());

        $wordEvent = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $wordEvent->shouldReceive('getSource')->andReturn('#wrongchan');
        $wordEvent->shouldReceive('getParams')->andReturn(['text' => 'fail']);

        $game->checkWord($wordEvent);

        $this->assertTrue($game->isRunning());

        $wordEvent = m::mock('Phergie\Irc\Plugin\React\Command\CommandEventInterface');
        $wordEvent->shouldReceive('getSource')->andReturn('#test');
        $wordEvent->shouldReceive('getParams')->andReturn(['text' => 'test']);
        $wordEvent->shouldReceive('getNick')->andReturn('tester');

        $queue->shouldReceive('ircPrivmsg')->once()->with($event->getSource(), "Woohoo tester!! You got it...\x034test");
        $queue->shouldReceive('ircPrivmsg')->once()->withAnyArgs();
        $game->checkWord($wordEvent);
        $this->assertFalse($game->isRunning());
    }
}
