<?php

namespace Infonomicon\IrcBot\WordGame\Providers;

use Infonomicon\IrcBot\WordGame\Word;
use Infonomicon\IrcBot\WordGame\WordProvider;

class MirovengiWordProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $provider = new MirovengiWordProvider();
        
        $this->assertTrue($provider instanceof WordProvider);
    }

    public function testGetWord()
    {
        $provider = new MirovengiWordProvider();

        $word = $provider->getWord();
        $this->assertTrue($word instanceof Word);
        $this->assertEquals('minivan', $word);
        $this->assertEquals('mirovengi', $word->getHint());

        // should always be the same
        $word = $provider->getWord();
        $this->assertEquals('minivan', $word);
        $this->assertEquals('mirovengi', $word->getHint());
    }
}
