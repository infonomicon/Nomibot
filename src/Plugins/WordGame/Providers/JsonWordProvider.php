<?php

namespace Nomibot\Plugins\WordGame\Providers;

use Nomibot\Plugins\WordGame\Word;
use Nomibot\Plugins\WordGame\WordProvider;

/**
 * Provide words from a JSON file
 *
 * The JSON must be in the format below:
 *
 * {
 *     "current": 0,
 *     "words": [
 *         {
 *             "word": "theword",
 *             "hint": "thehint"
 *         }, {
 *             "word": "anotherword",
 *             "hint": "anotherhint"
 *         }
 *     ]
 * }
 */
class JsonWordProvider implements WordProvider
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        if (!is_readable($filename)) {
            throw new \RuntimeException('File cannot be read');
        }

        $this->filename = $filename;
        $this->data = json_decode(file_get_contents($filename), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getWord()
    {
        $current = $this->data['words'][$this->data['current']];

        $word = new Word($current['word'], $current['hint']);

        $this->data['current']++;

        if ($this->data['current'] >= count($this->data['words'])) {
            $this->data['current'] = 0;
        }

        $this->saveData();

        return $word;
    }

    /**
     * Save the data
     */
    private function saveData()
    {
        file_put_contents(
            $this->filename,
            json_encode($this->data, JSON_PRETTY_PRINT)
        );
    }
}
