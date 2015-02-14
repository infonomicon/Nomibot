<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as CommandEvent;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

class Cointoss extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'command.cointoss' => 'handle',
            'command.cointoss.help' => 'help',
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

        $queue->ircPrivmsg($channel, "cointoss (no arguments)");
        $queue->ircPrivmsg($channel, "=======================");
        $queue->ircPrivmsg($channel, "Returns heads or tails.");
    }

    /**
     * Toss a coin
     *
     * @param CommandEvent $event
     * @param Queue        $queue
     */
    public function handle(CommandEvent $event, Queue $queue)
    {
        $channel = $event->getSource();
        $params = $event->getCustomParams();
        $a = 'heads';
        $b = 'tails';

        if (count($params) > 1) {
            $a .= ': ' . $params[0];
            $b .= ': ' . $params[1];
        }

        $queue->ircPrivmsg($channel, rand(0, 1) === 0 ? "$a" : "$b");
    }
}
