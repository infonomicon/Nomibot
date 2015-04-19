<?php

namespace Nomibot\Plugins\TimeBomb;

use Mockery as m;

class GameTest extends \PHPUnit_Framework_TestCase
{
    private function filePath()
    {
        return __DIR__.'/test_optouts.json';
    }

    private function getGame()
    {
        return new Game(['optout_file' => $this->filePath()]);
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

    public function testChannel()
    {
        $game = $this->getGame();

        $game->setChannel('testchan');

        $this->assertEquals('testchan', $game->getChannel());
    }

    public function testOptOuts()
    {
        $game = $this->getGame();
        $optouts = $game->getOptOuts();

        $this->assertInstanceOf('Nomibot\Plugins\TimeBomb\OptOutManager', $optouts);
        $this->assertTrue($optouts->contains('OptedOut'));
        $this->assertTrue($optouts->contains('Tester'));
        $this->assertFalse($optouts->contains('Another'));
    }

    public function testDisarmOnToss()
    {
        $game = $this->getGame();
        $this->assertTrue(is_bool($game->disarmOnToss()));
    }

    public function testExplodeOnToss()
    {
        $game = $this->getGame();
        $this->assertTrue(is_bool($game->explodeOnToss()));
    }

    public function testStart()
    {
        $game = $this->getGame();
        $timer = m::mock('Nomibot\Plugins\TimeBomb\TimerInterface');
        $timer->shouldReceive('start')->with(m::type('int'), m::type('callable'));
        $game->setTimer($timer);
        $game->start('fromnick', 'tonick', function () {});
        $this->assertContains('tonick', $game->getPlayerList());
        $this->assertContains('fromnick', $game->getPlayerList());
        $this->assertTrue($game->getBombHolder()->is('tonick'));

        $timer->shouldReceive('running')->andReturn(true);

        $this->assertTrue($game->isRunning());
        $this->assertInstanceOf('Nomibot\Plugins\TimeBomb\Bomb', $game->getBomb());
        $this->assertInstanceOf('Nomibot\Plugins\TimeBomb\TimerInterface', $game->getTimer());
    }

    public function testBombHolder()
    {
        $game = $this->getGame();
        $timer = m::mock('Nomibot\Plugins\TimeBomb\TimerInterface');
        $timer->shouldReceive('start')->with(m::type('int'), m::type('callable'));
        $game->setTimer($timer);
        $game->start('fromnick', 'tonick', function () {});
        $game->setBombHolder('newnick');
        $this->assertTrue($game->getBombHolder()->is('newnick'));
        $this->assertEquals(3, count($game->getPlayerList()));
    }

    public function testEnd()
    {
        $game = $this->getGame();
        $timer = m::mock('Nomibot\Plugins\TimeBomb\TimerInterface');
        $timer->shouldReceive('start')->with(m::type('int'), m::type('callable'));
        $game->setTimer($timer);
        $game->setChannel('testchan');
        $game->start('fromnick', 'tonick', function () {});

        $timer->shouldReceive('running')->andReturn(true);
        $timer->shouldReceive('cancel');

        $reflection = new \ReflectionClass(Game::class);
        $property = $reflection->getProperty('tossExplosionChance');
        $property->setAccessible(true);
        $property->setValue($game, 10);

        $game->end();

        $this->assertNull($game->getChannel());
        $this->assertNull($game->getBombHolder());
        $this->assertNull($game->getBomb());
        $this->assertEquals(1, $property->getValue($game));
    }
}
