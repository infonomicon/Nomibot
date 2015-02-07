<?php

namespace Nomibot\Plugins\TimeBomb;

class Bomb
{
    /**
     * @var array
     */
    private $wires;

    /**
     * @var string
     */
    private $fuse;

    /**
     * @param array  $wires The wires on the bomb
     * @param string $fuse  The wire where if cut, defuses the bomb
     */
    public function __construct($wires, $fuse)
    {
        $this->wires = $wires;
        $this->fuse = $fuse;
    }

    /**
     * Creates a new bomb from options
     *
     * @param  array $options The options array
     *
     * @return static
     */
    public static function create(array $options = [])
    {
        $options = array_merge([
            'wires' => [
                'Red', 'Orange', 'Yellow', 'Green', 'Blue', 'Indigo', 'Violet', 'Black',
                'White', 'Grey', 'Brown', 'Pink', 'Mauve', 'Beige', 'Aquamarine',
                'Chartreuse', 'Bisque', 'Crimson', 'Fuchsia', 'Gold', 'Ivory', 'Khaki',
                'Lavender', 'Lime', 'Magenta', 'Maroon', 'Navy', 'Olive', 'Plum',
                'Silver', 'Tan', 'Teal', 'Turquoise'
            ],
            'min_wires' => 1,
            'max_wires' => 3,
            'troll_wire' => true,
        ], $options);

        $wireList = $options['wires'];
        shuffle($wireList);

        $wireCount = rand($options['min_wires'], $options['max_wires']);
        $wires = array_slice($wireList, 0, $wireCount);
        $fuse = null;

        if ($wireCount > 1 || !$options['troll_wire'] || rand(0, 1) === 0) {
            $fuse = $wires[array_rand($wires)];
        }

        return new static($wires, $fuse);
    }

    /**
     * Get the wires on the bomb
     *
     * @return array
     */
    public function getWires()
    {
        return $this->wires;
    }

    /**
     * Count the wires on the bomb
     *
     * @return integer
     */
    public function countWires()
    {
        return count($this->wires);
    }

    /**
     * Check if a wire is on the bomb
     *
     * @param  string $wire The wire to test for
     *
     * @return boolean
     */
    public function hasWire($wire)
    {
        $wire = strtolower($wire);

        foreach ($this->wires as $bombWire) {
            if ($wire === strtolower($bombWire)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a wire is the fuse
     *
     * @param  string $wire The wire to test for
     *
     * @return boolean
     */
    public function isFuse($wire)
    {
        return strtolower($wire) === strtolower($this->fuse);
    }
}
