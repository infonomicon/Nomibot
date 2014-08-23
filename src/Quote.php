<?php

namespace Infonomicon\IrcBot;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as Event;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

/**
 * Quotes plugin
 */
class Quote extends AbstractPlugin
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
        return [
            'command.quote' => 'handleQuote',
            'command.q' => 'handleQuote',
            'command.addquote' => 'handleAdd',
        ];
    }

    /**
     * Get a quote
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handleQuote(Event $event, Queue $queue)
    {
        $params = $event->getCustomParams();

        if (isset($params[0])) {
            $quote = $this->getRandomQuote($params[0]);
        } else {
            $quote = $this->getRandomQuote();
        }

        $queue->ircPrivmsg($event->getSource(), $quote);
    }

    /**
     * Add a quote
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handleAdd(Event $event, Queue $queue)
    {
        $params = $event->getCustomParams();

        if (count($params) === 2) {
            $this->addQuote($params[0], $params[1]);
            $queue->ircPrivmsg($event->getSource(), "Quote added.");
        }
    }

    /**
     * Get a random quote
     *
     * @param string $section
     * @return string
     */
    private function getRandomQuote($section = null)
    {
        if ($section === null) {
            $pool = [];

            foreach ($this->quotes as $group) {
                $pool = array_merge($pool, $group);
            }
        } elseif (isset($this->quotes[$section])) {
            $pool = $this->quotes[$section];
        } else {
            return false;
        }

        return $pool[array_rand($pool)];
    }

    /**
     * Add a quote
     *
     * @param string $section
     * @param string $quote
     */
    private function addQuote($section, $quote)
    {
        if (!isset($this->quotes[$section])) {
            $this->quotes[$section] = [];
        }

        $this->quotes[$section][] = $quote;

        $this->saveQuotes();
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
        file_put_contents($this->filename, json_encode($this->quotes, JSON_PRETTY_PRINT));
    }
}
