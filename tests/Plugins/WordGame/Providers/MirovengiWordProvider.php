<?php

namespace Nomibot\Plugins\WordGame\Providers;

use Nomibot\Plugins\WordGame\Word;
use Nomibot\Plugins\WordGame\WordProvider;

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
