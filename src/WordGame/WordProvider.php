<?php

namespace Infonomicon\IrcBot\WordGame;

/**
 * Word provider interface
 */
interface WordProvider
{
    /**
     * Return a word to use in the word game
     *
     * @return Word
     */
    public function getWord();
}
