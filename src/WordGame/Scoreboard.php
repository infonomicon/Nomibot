<?php

namespace Infonomicon\IrcBot\WordGame;

/**
 * Scoreboard interface
 */
interface Scoreboard
{
    /**
     * Track a win
     *
     * @param string $nick
     */
    public function addWin($nick);

    /**
     * Get the stats for a nick
     *
     * @param string $nick
     * @return Stats
     */
    public function getStats($nick);

    /**
     * Get the top ten stats
     *
     * @return Stats[]
     */
    public function getTopTen();
}
