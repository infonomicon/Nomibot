<?php

namespace Nomibot\Plugins\TimeBomb;

class Bomb
{
    /**
     * @var \Nomibot\Plugins\TimeBomb\Timer
     */
    private $timer;

    /**
     * @var array
     */
    private $wires;

    /**
     * @var string
     */
    private $correctWire;

    /**
     * @param Timer $timer
     * @param array $wires
     */
    public function __construct(Timer $timer, array $wires)
    {
        $this->timer = $timer;
        $this->wires = $wires;
        $this->correctWire = $this->wires[array_rand($this->wires)];
    }

    /**
     * Get the timer
     *
     * @return Timer
     */
    public function getTimer()
    {
        return $this->timer;
    }

    /**
     * Get the wire options
     *
     * @return array
     */
    public function getWires()
    {
        return $this->wires;
    }

    /**
     * Get the number of wires
     *
     * @return int
     */
    public function countWires()
    {
        return count($this->wires);
    }

    /**
     * Check if the bomb has a wire
     *
     * @param string $wire
     * @return boolean
     */
    public function hasWire($wire)
    {
        return in_array(strtolower($wire), array_map('strtolower', $this->wires));
    }

    /**
     * Check if the chosen wire is correct for defusing the bomb
     *
     * @throws \InvalidArgumentException
     *
     * @param string $wire
     * @return boolean
     */
    public function checkWire($wire)
    {
        if (!$this->hasWire($wire)) {
            throw new \InvalidArgumentException('Wire not found');
        }

        return strtolower($wire) === strtolower($this->correctWire);
    }
}
