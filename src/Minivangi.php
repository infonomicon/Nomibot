<?php

namespace Infonomicon\IrcBot;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Event\UserEventInterface as Event;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

/**
 * Minivangi plugin
 *
 * @author ragechin
 */
class Minivangi extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['irc.received.privmsg' => 'handle'];
    }

    /**
     * Say good morning to mirovengi
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handle(Event $event, Queue $queue)
    {
        if ($event->getNick() !== 'mirovengi') {
            return;
        }

        $params = $event->getParams();

        if (stripos($params['text'], 'mornin') !== false) {
            $queue->ircPrivmsg($event->getSource(), "morning, minivangi");
        }
    }
}
