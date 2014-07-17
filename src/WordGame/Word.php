<?php

namespace Infonomicon\IrcBot\WordGame;

/**
 * A word for the word game
 */
class Word
{
    /**
     * @var string
     */
    private $word;

    /**
     * @var string
     */
    private $hint;

    /**
     * A word must:
     *  - be greater than 2 characters
     *  - only contain letters
     *  - be able to be scrambled
     *  - have a non-empty hint
     *
     * @param string $word
     * @param string $hint
     */
    public function __construct($word, $hint)
    {
        $word = strtolower($word);

        if (strlen($word) < 3) {
            throw new \InvalidArgumentException('The word provided is too small');
        }

        if (!preg_match('/^[\pL]*$/', $word)) {
            throw new \InvalidArgumentException('The word provided contains invalid characters');
        }

        $letters = str_split($word);

        if (count(array_unique($letters)) === 1) {
            throw new \InvalidArgumentException('The word provided cannot be scrambled');
        }

        if (empty($hint)) {
            throw new \InvalidArgumentException('The hint cannot be empty');
        }

        $this->word = $word;
        $this->hint = $hint;
    }

    /**
     * Get the word string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->word;
    }

    /**
     * Get a scrambled version of the word
     *
     * @return string
     */
    public function getScrambled()
    {
        do {
            $scrambled = str_shuffle($this->word);
        } while ($scrambled === $this->word);

        return $scrambled;
    }

    /**
     * Get the hint for the word
     *
     * @return string
     */
    public function getHint()
    {
        return $this->hint;
    }

    /**
     * Get the first n letters for the word
     *
     * @param integer $int
     * @return string
     */
    public function getFirst($int)
    {
        return substr($this->word, 0, $int);
    }
}
