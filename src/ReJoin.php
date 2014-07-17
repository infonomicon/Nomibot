<?php

namespace Infonomicon\IrcBot;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Event\UserEventInterface as Event;

/**
 * Plugin to rejoin a channel the bot was kicked from
 */
class ReJoin extends AbstractPlugin
{
    private $joinMessage;

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'irc.received.kick' => 'handleKick',
            'irc.received.join' => 'sendJoinMessage'
        ];
    }

    /**
     * Responds to kicks with a re-join.
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handleKick(Event $event, Queue $queue)
    {
        $params = $event->getParams();

        if ($params['user'] === $event->getConnection()->getNickname()) {
            $queue->ircJoin($params['channel']);
            $this->joinMessage = "Thanks, {$event->getNick()}. I needed that.";
        }
    }

    /**
     * Sends a message on rejoin
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function sendJoinMessage(Event $event, Queue $queue)
    {
        if (!$this->joinMessage) {
            return;
        }

        $queue->ircPrivmsg($event->getSource(), $this->joinMessage);
        $this->joinMessage = null;
    }
}
