<?php

namespace Infonomicon\IrcBot;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as CommandEvent;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

/**
 * Hackers quotes plugin
 */
class Hackers extends AbstractPlugin
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     */
    private $quotes = [];

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->loadQuotes();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['command.hackers' => 'handle'];
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

        if (count($params) === 2 && $params[0] === 'add') {
            $this->quotes[] = $params[1];
            $this->saveQuotes();
            $queue->ircPrivmsg($event->getSource(), "Quote added.");
            return;
        }

        $quote = $this->quotes[array_rand($this->quotes)];

        $queue->ircPrivmsg($event->getSource(), $quote);
    }

    /**
     * Load the quote list from a file
     */
    private function loadQuotes()
    {
        $this->quotes = json_decode(file_get_contents($this->filename), true);
    }

    /**
     * Save the quote list
     */
    private function saveQuotes()
    {
        file_put_contents($this->filename, json_encode($this->quotes));
    }
}
