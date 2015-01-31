<?php

namespace Nomibot\Plugins\WordGame\Providers;

use Nomibot\Plugins\WordGame\WordProvider;
use Nomibot\Plugins\WordGame\Word;

class MirovengiWordProvider implements WordProvider
{
    public function getWord()
    {
        return new Word('minivan', 'mirovengi');
    }
}
