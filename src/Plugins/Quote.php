<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as Event;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

class Quote extends AbstractPlugin
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->filename = $options['quotefile'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'command.quotes' => 'listQuoteGroups',
            'command.quote' => 'handleQuote',
            'command.q' => 'handleQuote',
            'command.addquote' => 'handleAdd',
            'command.quotes.help' => 'helpQuotes',
            'command.quote.help' => 'helpQuote',
            'command.q.help' => 'helpQuote',
            'command.addquote.help' => 'helpAddQuote',
        ];
    }

    /**
     * Show help text for quotes command
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function helpQuotes(Event $event, Queue $queue)
    {
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, "quotes (no arguments)");
        $queue->ircPrivmsg($channel, "=====================");
        $queue->ircPrivmsg($channel, "Lists current quote groups.");
    }

    /**
     * Show help text for quote command
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function helpQuote(Event $event, Queue $queue)
    {
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, "quote [group:optional]");
        $queue->ircPrivmsg($channel, "======================");
        $queue->ircPrivmsg($channel, "Prints a random quote, optionally from a specified group.");
    }

    /**
     * Show help text for addquote command
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function helpAddQuote(Event $event, Queue $queue)
    {
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, "addquote [group] [quote]");
        $queue->ircPrivmsg($channel, "========================");
        $queue->ircPrivmsg($channel, "Add a quote to a group.");
    }

    /**
     * List the quote groups
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function listQuoteGroups(Event $event, Queue $queue)
    {
        $quoteData = $this->loadQuotes();

        $groups = 'Quote groups: ';

        foreach ($quoteData as $name => $quotes) {
            $groups .= $name . '(' . count($quotes) . ') ';
        }

        $queue->ircPrivmsg($event->getSource(), trim($groups));
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

        if (count($params) >= 2) {
            $group = array_shift($params);
            $quote = implode(' ', $params);

            $this->addQuote($group, $quote);
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
        $quoteData = $this->loadQuotes();

        if ($section === null) {
            $pool = [];

            foreach ($quoteData as $group) {
                $pool = array_merge($pool, $group);
            }
        } elseif (isset($quoteData[$section])) {
            $pool = $quoteData[$section];
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
        $quoteData = $this->loadQuotes();

        if (!isset($quoteData[$section])) {
            $quoteData[$section] = [];
        }

        $quoteData[$section][] = $quote;

        $this->saveQuotes($quoteData);
    }

    /**
     * Load the quote list from a file
     */
    private function loadQuotes()
    {
        return json_decode(file_get_contents($this->filename), true);
    }

    /**
     * Save the quote list
     */
    private function saveQuotes($quoteData)
    {
        file_put_contents($this->filename, json_encode($quoteData, JSON_PRETTY_PRINT));
    }
}
