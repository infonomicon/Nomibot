<?php

namespace Nomibot\Plugins\TimeBomb;

class PlayerTest extends \PHPUnit_Framework_TestCase
{
    public function testPlayer()
    {
        $player = new Player('Test');

        $this->assertEquals('Test', $player->getNick());
        $this->assertTrue($player->is('Test'));
        $this->assertTrue($player->is('tEsT'));
        $this->assertTrue($player->is(' test '));
        $this->assertTrue($player->is('test'));
    }
}
