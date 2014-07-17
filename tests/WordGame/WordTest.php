<?php

namespace Infonomicon\IrcBot\WordGame;

class WordTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $word = new Word('test', 'a hint');
        
        $this->assertEquals('test', $word);
        $this->assertEquals('a hint', $word->getHint());
    }

    public function testConstructorFails_wordTooSmall()
    {
        $this->setExpectedException('InvalidArgumentException');
        $word = new Word('sm', 'a hint');
    }

    public function testConstructorFails_wordContainsInvalidChars()
    {
        $this->setExpectedException('InvalidArgumentException');
        $word = new Word('test ', 'a hint');
    }

    public function testConstructorFails_wordCannotScramble()
    {
        $this->setExpectedException('InvalidArgumentException');
        $word = new Word('aaaa', 'a hint');
    }

    public function testConstructorFails_emptyHint()
    {
        $this->setExpectedException('InvalidArgumentException');
        $word = new Word('test', '');
    }

    public function testGetScrambled()
    {
        $word = new Word('test', 'a hint');

        $this->assertNotEquals('test', $word->getScrambled());
        $this->assertEquals(4, strlen($word->getScrambled()));
    }

    public function testGetFirst()
    {
        $word = new Word('test', 'a hint');

        $this->assertEquals('t', $word->getFirst(1));
        $this->assertEquals('tes', $word->getFirst(3));
    }
}
