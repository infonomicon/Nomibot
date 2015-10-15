<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as CommandEvent;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

class Greet extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'command.greet' => 'handle',
            'command.greet.help' => 'help',
        ];
    }

    /**
     * Show help text
     *
     * @param CommandEvent $event
     * @param Queue        $queue
     */
    public function help(CommandEvent $event, Queue $queue)
    {
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, "greet");
        $queue->ircPrivmsg($channel, "=====");
        $queue->ircPrivmsg($channel, "Greets people for you, you lazy bum.");
    }

    /**
     * Omniscan!
     *
     * @param CommandEvent $event
     * @param Queue        $queue
     */
    public function handle(CommandEvent $event, Queue $queue)
    {
    }
}
