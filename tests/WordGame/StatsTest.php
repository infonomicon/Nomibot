<?php

namespace Infonomicon\IrcBot\WordGame;

class StatsTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $stats = new Stats('tester', 2, 1);
        
        $this->assertEquals('tester', $stats->getNick());
        $this->assertEquals(2, $stats->getTotal());
        $this->assertEquals(1, $stats->getToday());
    }
}
