<?php

namespace Nomibot\Plugins\WordGame\Providers;

use Nomibot\Plugins\WordGame\Word;
use Nomibot\Plugins\WordGame\WordProvider;

/**
 * Provide words from a file list
 *
 * File must be in the format below:
 *
 * word:hint
 */
class FileWordProvider implements WordProvider
{
    /**
     * @var array
     */
    private $words = [];

    /**
     * @var integer
     */
    private $index;

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        if (!is_readable($filename)) {
            throw new \RuntimeException('File cannot be read');
        }

        $fh = fopen($filename, 'r');

        while ($line = fgets($fh)) {
            try {
                $this->words[] = $this->parseWordFromLine($line);
            } catch (\InvalidArgumentException $e) {}
        }

        fclose($fh);

        $this->index = rand(0, count($this->words) - 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getWord()
    {
        $word = $this->words[$this->index];

        $this->index++;

        if ($this->index >= count($this->words)) {
            $this->index = 0;
        }

        return $word;
    }

    /**
     * Create a word object from a line in the file
     *
     * @param string $line
     * @return \Nomibot\Plugins\WordGame\Word
     */
    private function parseWordFromLine($line)
    {
        $parts = explode(':', $line);

        $word = trim($parts[0]);
        $hint = trim($parts[1]);

        return new Word($word, $hint);
    }
}
