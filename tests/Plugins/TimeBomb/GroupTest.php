<?php

namespace Nomibot\Plugins\TimeBomb;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    public function testGroup()
    {
        $group = new Group;

        $group->add('Test');
        $group->add('Test');
        $group->add('test');
        $group->add('Another');

        $this->assertEquals(['Test', 'Another'], $group->listAll());

        $players = $group->all();

        $this->assertEquals(2, count($players));

        foreach ($players as $player) {
            $this->assertInstanceOf('Nomibot\Plugins\TimeBomb\Player', $player);
        }

        $player_test = $group->find('test');
        $player_Test = $group->find('Test');

        $this->assertSame($player_test, $player_Test);

        $group->remove('test');

        $this->assertEquals(['Another'], $group->listAll());
    }
}
