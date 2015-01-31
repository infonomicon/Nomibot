<?php

namespace Nomibot\Plugins\WordGame\Providers;

use Nomibot\Plugins\WordGame\Word;
use Nomibot\Plugins\WordGame\WordProvider;

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
