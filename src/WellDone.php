<?php

namespace Infonomicon\IrcBot;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Event\UserEventInterface as Event;

/**
 * Well done sir!!!
 */
class WellDone extends AbstractPlugin
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'irc.received.join' => 'handleJoin'
        ];
    }

    /**
     * Sends a welcome message.
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handleJoin(Event $event, Queue $queue)
    {
        if (rand(0, 3) !== 0) {
            return;
        }

        $params = $event->getParams();

        if ($params['user'] == $event->getConnection()->getNickname()) {
            return;
        }

        $message = '*clap* *clap* *clap* -- Well done, sir! -- *clap* *clap* *clap*';
        $queue->ircPrivmsg($event->getSource(), $message);
    }
}
