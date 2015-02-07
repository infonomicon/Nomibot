<?php

namespace Nomibot\Plugins\TimeBomb;

class Group
{
    /**
     * @var \SplObjectStorage
     */
    private $players;

    /**
     * @param array $list An optional list of nicks to add
     */
    public function __construct(array $list = [])
    {
        $this->players = new \SplObjectStorage;

        foreach ($list as $nick) {
            $this->add($nick);
        }
    }

    /**
     * Add a nick to the player repo
     *
     * @param string $nick The nick for the player
     */
    public function add($nick)
    {
        if ($player = $this->find($nick)) {
            return;
        }

        $this->players->attach(new Player($nick));
    }

    /**
     * Remove a player by nick
     *
     * @param string $nick The nick of the player to remove
     */
    public function remove($nick)
    {
        if (!$player = $this->find($nick)) {
            return;
        }

        $this->players->detach($player);
    }

    /**
     * Get all players
     *
     * @return \ArrayAccess
     */
    public function all()
    {
        return $this->players;
    }

    /**
     * List all players
     *
     * @return array
     */
    public function listAll()
    {
        $list = [];

        foreach ($this->players as $player) {
            $list[] = $player->getNick();
        }

        return $list;
    }

    /**
     * Find a player by nick
     *
     * @param  string $nick The player to find
     * @return Player
     */
    public function find($nick)
    {
        foreach ($this->players as $player) {
            if ($player->is($nick)) {
                return $player;
            }
        }
    }
}
