<?php

namespace Infonomicon\IrcBot;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as Event;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Client\React\LoopAwareInterface;
use React\EventLoop\LoopInterface;

/**
 * Joke plugin
 */
class Joke extends AbstractPlugin implements LoopAwareInterface
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var \React\EventLoop\LoopInterface
     */
    private $loop;

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'command.joke' => 'handleJoke',
            'command.j' => 'handleJoke',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * Tell a joke
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handleJoke(Event $event, Queue $queue)
    {
        $joke = $this->getRandomJoke();
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, $joke['question']);

        $this->loop->addTimer(4, function () use ($joke, $channel, $queue) {
            $queue->ircPrivmsg($channel, $joke['answer']);
        });
    }

    /**
     * Get a random joke
     */
    private function getRandomJoke()
    {
        $jokes = json_decode(file_get_contents($this->filename), true);

        return $jokes[array_rand($jokes)];
    }
}
