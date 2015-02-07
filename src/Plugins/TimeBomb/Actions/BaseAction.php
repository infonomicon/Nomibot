<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as Event;
use Nomibot\Plugins\TimeBomb\Game;

abstract class BaseAction
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var Game
     */
    protected $game;

    /**
     * @param Event $event The irc event
     * @param Queue $queue The event queue
     * @param Game|null $game The current game
     */
    public function __construct(Event $event, Queue $queue, Game $game = null)
    {
        $this->event = $event;
        $this->queue = $queue;
        $this->game = $game;
    }

    /**
     * Execute the action
     */
    abstract public function __invoke();

    /**
     * Send a kick to the queue
     *
     * @param string $nick    The nick to kick
     * @param string $message The message to kick with
     */
    protected function kick($nick, $message = '')
    {
        $this->queue->ircKick($this->event->getSource(), $nick, $message);
    }

    /**
     * Send a message to the queue
     *
     * @param string $message
     */
    protected function message($message)
    {
        $this->queue->ircPrivmsg($this->event->getSource(), $message);
    }


    /**
     * Send an action to the queue
     *
     * @param string $action
     */
    protected function action($action)
    {
        $this->message("\x01ACTION {$action}\x01");
    }

    /**
     * Check if a nick is valid
     *
     * @see    RFC 2812 section 2.3.1
     *
     * @param  string $nick
     * @return boolean
     */
    protected function isValidNick($nick)
    {
        $letter = 'a-zA-Z';
        $number = '0-9';
        $special = preg_quote('[]\`_^{|}');
        $pattern =  "/^(?:[$letter$special][$letter$number$special-]*)$/";

        return preg_match($pattern, $nick);
    }

    /**
     * Check if a nick is the same as the bot's
     *
     * @param  string $nick The nick to test
     *
     * @return boolean
     */
    protected function isBotNick($nick)
    {
        return strtolower($nick) === strtolower($this->event->getConnection()->getNickname());
    }
}
