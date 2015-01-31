<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as CommandEvent;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

class Say extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['command.say' => 'handle'];
    }

    /**
     * Make the bot say something on a channel
     *
     * @param CommandEvent $event
     * @param Queue        $queue
     */
    public function handle(CommandEvent $event, Queue $queue)
    {
        if ($event->getNick() !== $event->getSource()) {
            return;
        }

        $params = $event->getCustomParams();

        if (count($params) < 2) {
            return;
        }

        $channel = $params[0];
        $message = $params[1];

        $queue->ircPrivmsg($channel, $message);
    }
}
