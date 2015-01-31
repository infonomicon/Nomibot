<?php

namespace Nomibot\Plugins\WordGame;

interface WordProvider
{
    /**
     * Return a word to use in the word game
     *
     * @return Word
     */
    public function getWord();
}
