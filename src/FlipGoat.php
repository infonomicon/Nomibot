<?php

namespace Infonomicon\IrcBot;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as CommandEvent;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

/**
 * FlipGoat plugin
 */
class FlipGoat extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['command.flipgoat' => 'handle'];
    }

    /**
     * Flip the goat
     *
     * @param CommandEvent $event
     * @param Queue        $queue
     */
    public function handle(CommandEvent $event, Queue $queue)
    {
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, "========)(========");
        usleep(50000);
        $queue->ircPrivmsg($channel, "====)        (====");
        usleep(50000);
        $queue->ircPrivmsg($channel, "====)        (====");
        usleep(50000);
        $queue->ircPrivmsg($channel, "========)(========");
    }
}
