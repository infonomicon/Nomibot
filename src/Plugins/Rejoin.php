<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Event\UserEventInterface as Event;

/**
 * Plugin to rejoin a channel the bot was kicked from
 */
class Rejoin extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'irc.received.kick' => 'handleKick',
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
        }
    }
}
