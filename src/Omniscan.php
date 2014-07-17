<?php

namespace Infonomicon\IrcBot;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as CommandEvent;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

/**
 * Omniscan plugin
 *
 * @author slick0
 */
class Omniscan extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['command.omniscan' => 'handle'];
    }

    /**
     * Omniscan!
     *
     * @param CommandEvent $event
     * @param Queue        $queue
     */
    public function handle(CommandEvent $event, Queue $queue)
    {
        $params = $event->getCustomParams();

        if (count($params) > 0) {
            $name = $params[0];
        } else {
            $name = $event->getNick();
        }

        $queue->ircPrivmsg($event->getSource(), "Omniscan, {$name}!");
    }
}
