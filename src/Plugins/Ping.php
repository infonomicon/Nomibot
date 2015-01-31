<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as Event;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

class Ping extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['command.ping' => 'handle'];
    }

    /**
     * Send a pong
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handle(Event $event, Queue $queue)
    {
        $queue->ircPrivmsg($event->getSource(), "pong");
    }
}
