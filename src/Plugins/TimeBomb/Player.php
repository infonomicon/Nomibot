<?php

namespace Nomibot\Plugins\TimeBomb;

class Player
{
    /**
     * @var string
     */
    private $nick;

    /**
     * @param string $nick The player's nick
     */
    public function __construct($nick)
    {
        $this->setNick($nick);
    }

    /**
     * Set the player's nick
     *
     * @param string nick The new nick
     */
    public function setNick($nick)
    {
        $this->nick = trim($nick);
    }

    /**
     * Get the player's nick
     *
     * @return string
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * Check if the nick matches the player
     *
     * @param  string $nick The nick to check
     * @return boolean
     */
    public function is($nick)
    {
        return strtolower($this->nick) === strtolower(trim($nick));
    }
}
