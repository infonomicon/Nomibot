<?php

namespace Nomibot\Plugins\TimeBomb;

class Game
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $channel;

    /**
     * @var OptOutManager
     */
    private $optOuts;

    /**
     * @var Bomb
     */
    private $bomb;

    /**
     * @var Player
     */
    private $bombHolder;

    /**
     * @var Group;
     */
    private $players;

    /**
     * @var TimerInterface
     */
    private $timer;

    /**
     * @var integer
     */
    private $tossExplosionChance = 1;

    /**
     * @param array $options The game options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->optOuts = new OptOutManager($options['optout_file']);
    }

    /**
     * Check if the game is currently running
     *
     * @return boolean
     */
    public function isRunning()
    {
        return $this->timer !== null && $this->timer->running();
    }

    /**
     * Set the channel the game is running on
     *
     * @param string $channel The channel name
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get the channel the game is running on
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Get the bomb
     *
     * @return Bomb
     */
    public function getBomb()
    {
        return $this->bomb;
    }

    /**
     * Set the bomb holder
     *
     * @param string $nick
     */
    public function setBombHolder($nick)
    {
        $this->players->add($nick);
        $this->bombHolder = new Player($nick);
    }

    /**
     * Get the bomb holder
     *
     * @return Player
     */
    public function getBombHolder()
    {
        return $this->bombHolder;
    }

    /**
     * Set the timer
     *
     * @param TimerInterface $timer
     */
    public function setTimer(TimerInterface $timer)
    {
        $this->timer = $timer;
    }

    /**
     * Get the timer
     *
     * @return TimerInterface
     */
    public function getTimer()
    {
        return $this->timer;
    }

    /**
     * Get the optout manager
     *
     * @return OptOutManager
     */
    public function getOptOuts()
    {
        return $this->optOuts;
    }

    /**
     * Get a list of all the player nicks
     *
     * @return array
     */
    public function getPlayerList()
    {
        return $this->players->listAll();
    }

    /**
     * Check if the bomb should disarm on toss
     *
     * @return boolean
     */
    public function disarmOnToss()
    {
        return rand(0, 99) === 0;
    }

    /**
     * Check if the bomb should explode on toss
     *
     * @return boolean
     */
    public function explodeOnToss()
    {
        if (rand(1, 100) <= $this->tossExplosionChance) {
            return true;
        }

        $this->tossExplosionChance += rand(0, 10);

        return false;
    }

    /**
     * Starts the game
     *
     * @param string   $from     The nick of who started the game
     * @param string   $to       The nick of who the bomb was sent to
     * @param callable $callable callable to run when the time is up
     */
    public function start($from, $to, callable $callable)
    {
        $this->players = new Group([$from, $to]);
        $this->setBombHolder($to);
        $this->bomb = Bomb::create($this->options);

        $options = array_merge([
            'min_seconds' => 120,
            'max_seconds' => 240,
        ], $this->options);

        $seconds = rand($options['min_seconds'], $options['max_seconds']);

        $this->timer->start($seconds, $callable);
    }

    /**
     * Ends the game
     */
    public function end()
    {
        if ($this->timer->running()) {
            $this->timer->cancel();
        }

        $this->setChannel(null);
        $this->setBombHolder(null);
        $this->players = null;
        $this->bomb = null;
    }
}
