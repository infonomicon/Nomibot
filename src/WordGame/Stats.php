<?php

namespace Infonomicon\IrcBot\WordGame;

/**
 * Stats value object
 */
class Stats
{
    /**
     * @var string
     */
    private $nick;

    /**
     * @var integer
     */
    private $total;

    /**
     * @var integer
     */
    private $today;

    /**
     * @param string  $nick
     * @param integer $total
     * @param integer $today
     */
    public function __construct($nick, $total, $today)
    {
        $this->nick = $nick;
        $this->total = $total;
        $this->today = $today;
    }

    /**
     * Get the nick
     *
     * @return string
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * Get the total score
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Get the score for today
     *
     * @return integer
     */
    public function getToday()
    {
        return $this->today;
    }
}
