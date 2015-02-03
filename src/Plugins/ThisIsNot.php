<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Event\UserEventInterface as Event;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

class ThisIsNot extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['irc.received.privmsg' => 'handle'];
    }

    /**
     * Tell a user if they are not where they think they are.
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handle(Event $event, Queue $queue)
    {
        $params = $event->getParams();
        $text = trim($params['text']);

        switch ($text) {
            case ':q':
            case ':w':
            case ':wq':
            case ':q!':
            case ':w!':
            case ':wq!':
                $message = 'This is not vi.';
                break;
            default:
                return;
        }

        $queue->ircPrivmsg($event->getSource(), $message);
    }
}
