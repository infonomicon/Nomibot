<?php

namespace Nomibot\Plugins\TimeBomb;

class BombTest extends \PHPUnit_Framework_TestCase
{
    public function testBomb()
    {
        $wires = ['Red', 'Green', 'Blue'];
        $fuse = 'Green';
        $bomb = new Bomb($wires, $fuse);

        $this->assertEquals(3, $bomb->countWires());
        $this->assertEquals($wires, $bomb->getWires());
        $this->assertTrue($bomb->hasWire('Blue'));
        $this->assertTrue($bomb->hasWire('blue'));
        $this->assertTrue($bomb->hasWire('green'));
        $this->assertTrue($bomb->hasWire('red'));
        $this->assertFalse($bomb->hasWire('yellow'));
        $this->assertTrue($bomb->isFuse('Green'));
        $this->assertTrue($bomb->isFuse('green'));
        $this->assertFalse($bomb->isFuse('red'));
    }

    public function testBombCreate()
    {
        $bomb = Bomb::create([
            'min_wires' => 1,
            'max_wires' => 3,
        ]);

        $this->assertGreaterThanOrEqual(1, $bomb->countWires());
        $this->assertLessThanOrEqual(3, $bomb->countWires());
    }
}
