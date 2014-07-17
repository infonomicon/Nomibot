<?php

namespace Infonomicon\IrcBot\WordGame\Providers;

use Infonomicon\IrcBot\WordGame\WordProvider;
use Infonomicon\IrcBot\WordGame\Word;

class MirovengiWordProvider implements WordProvider
{
    public function getWord()
    {
        return new Word('minivan', 'mirovengi');
    }
}
