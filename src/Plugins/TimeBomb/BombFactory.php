<?php

namespace Nomibot\Plugins\TimeBomb;

class BombFactory
{
    /**
     * @var array
     */
    private $wires = [
        'Red',
        'Orange',
        'Yellow',
        'Green',
        'Blue',
        'Indigo',
        'Violet',
        'Black',
        'White',
        'Grey',
        'Brown',
        'Pink',
        'Mauve',
        'Beige',
        'Aquamarine',
        'Chartreuse',
        'Bisque',
        'Crimson',
        'Fuchsia',
        'Gold',
        'Ivory',
        'Khaki',
        'Lavender',
        'Lime',
        'Magenta',
        'Maroon',
        'Navy',
        'Olive',
        'Plum',
        'Silver',
        'Tan',
        'Teal',
        'Turquoise',
    ];

    /**
     * Build a bomb
     *
     * @return Bomb
     */
    public function build()
    {
        $timer = new Timer(rand(120, 240));
        $wires = $this->getRandomWires();

        return new Bomb($timer, $wires);
    }

    /**
     * Get random wires for the bomb's wiring
     *
     * @return array
     */
    private function getRandomWires()
    {
        shuffle($this->wires);

        return array_slice($this->wires, 0, rand(1, 3));
    }
}
