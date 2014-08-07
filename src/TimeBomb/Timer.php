<?php

namespace Infonomicon\IrcBot\TimeBomb;

/**
 * Timer class
 */
class Timer
{
    /**
     * @var int
     */
    private $seconds;

    /**
     * @var float
     */
    private $startTime;

    /**
     * @param int
     */
    public function __construct($seconds)
    {
        $this->seconds = $seconds;
        $this->startTime = microtime(true);
    }

    /**
     * Get the total number of seconds
     *
     * @return int
     */
    public function getSeconds()
    {
        return $this->seconds;
    }

    /**
     * Get the remaining seconds until expiry
     *
     * @return int
     */
    public function getSecondsLeft()
    {
        $elapsed = microtime(true) - $this->startTime;

        return floor($this->seconds - $elapsed);
    }
}