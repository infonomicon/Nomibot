<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as CommandEvent;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

class FlipGoat extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'command.flipgoat' => 'handle',
            'command.flipgoat.help' => 'help',
        ];
    }

    /**
     * Show help text
     *
     * @param CommandEvent $event
     * @param Queue $queue
     */
    public function help(CommandEvent $event, Queue $queue)
    {
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, "flipgoat (no arguments)");
        $queue->ircPrivmsg($channel, "=======================");
        $queue->ircPrivmsg($channel, "Flips the goat!");
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
        $queue->ircPrivmsg($channel, "#===)        (====");
        $queue->ircPrivmsg($channel, "====)        (====");
        $queue->ircPrivmsg($channel, "========)(========");
    }
}
