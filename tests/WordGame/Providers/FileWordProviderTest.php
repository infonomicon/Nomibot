<?php

namespace Infonomicon\IrcBot\WordGame\Providers;

use Infonomicon\IrcBot\WordGame\Word;
use Infonomicon\IrcBot\WordGame\WordProvider;

class FileWordProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $filename = __DIR__ . '/stubs/wordlist.txt';
        $provider = new FileWordProvider($filename);
        
        $this->assertTrue($provider instanceof WordProvider);
    }

    public function testConstructorFails_wordContainsInvalidChars()
    {
        $this->setExpectedException('RuntimeException');
        new FileWordProvider(__DIR__ . '/stubs/missing.txt');
    }

    public function testGetWord()
    {
        $filename = __DIR__ . '/stubs/wordlist.txt';
        $provider = new FileWordProvider($filename);

        $this->assertTrue($provider->getWord() instanceof Word);
    }

    public function testGetWord_IteratesProperly()
    {
        $filename = __DIR__ . '/stubs/wordlist.txt';
        $provider = new FileWordProvider($filename);

        $word = $provider->getWord();
        $provider->getWord();
        $provider->getWord();

        $this->assertEquals($word, $provider->getWord());
    }
}
