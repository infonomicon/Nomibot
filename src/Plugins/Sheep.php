<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Event\UserEventInterface as Event;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

class Sheep extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['irc.received.privmsg' => 'handle'];
    }

    /**
     * Kick a user who dares to speak of sheep desecration
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handle(Event $event, Queue $queue)
    {
        $params = $event->getParams();
        $text = strtolower($params['text']);
        $text = preg_replace('/[^a-z]/', '', $text);

        if (preg_match('/(fuck|molest|rape|intercourse|havesex).*(sheep)/', $text) && !preg_match('/(never|dont|wont|not)/', $text)) {
            $queue->ircKick($event->getSource(), $event->getNick(), "We don't fuck sheep.");
        }
    }
}
