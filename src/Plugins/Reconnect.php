<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;

class Reconnect extends AbstractPlugin
{
    /**
     * @var integer
     */
    private $lastAttempt = 0;

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['connect.end' => 'reconnect'];
    }

    /**
     * Reconnect when a connection drops
     *
     * @param Connection
     * @param Logger
     */
    public function reconnect($connection, $logger)
    {
        $logger->info('Connection ended unexpectedly.');
        $now = microtime(true);
        $diff = $now - $this->lastAttempt;

        if ($diff < 900) {
            $delay = 900 - floor($diff);
            $logger->info("Last connection attempt too recent.  Delaying $delay seconds.");
            sleep($delay);
        }

        $logger->info('Attempting to reconnect...');
        $this->lastAttempt = $now;
        $this->client->addConnection($connection);
    }
}
