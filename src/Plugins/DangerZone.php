<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Event\UserEventInterface as Event;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

class DangerZone extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['irc.received.privmsg' => 'handle'];
    }

    /**
     * Check for nasty commands, and send a warning if one is found
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handle(Event $event, Queue $queue)
    {
        $params = $event->getParams();
        $text = str_replace(' ', '', $params['text']);

        if (stripos($text, ':(){:|:&};:') !== false) {
            $message = "It's a fork bomb.";
        } elseif (stripos($text, 'rm-rf/') !== false || stripos($text, 'rm-fr/') !== false) {
            $message = "It will delete the contents of your root partition.";
        } else {
            return;
        }

        $queue->ircPrivmsg($event->getSource(), "Do \x034NOT\x03 execute the above command.  " . $message);
    }
}
