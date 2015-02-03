<?php

namespace Nomibot;

use League\Container\Container;
use Phergie\Irc\Bot\React\Bot;

class Application extends Container
{
    /**
     * @var Phergie\Irc\Bot\React\Bot
     */
    private $bot;

    /**
     * Load a plugin by name
     *
     * @throws \RuntimeException
     *
     * @param  string  $name  The plugin FQCN
     * @return mixed
     */
    private function loadPlugin($name)
    {
        try {
            return $this[$name];
        } catch (\Exception $e) {
            throw new \RuntimeException("Could not load plugin '$name'.  Make sure it's service is defined.");
        }
    }

    /**
     * Apply the bot configuration
     */
    private function applyBotConfig()
    {
        $config = [
            'connections' => [$this['irc.connection']],
            'plugins' => [],
        ];

        foreach ($this['config']['enabled_plugins'] as $pluginName) {
            $config['plugins'][] = $this->loadPlugin($pluginName);
        }

        $this->bot->setConfig($config);
    }

    /**
     * Apply the bot logger
     */
    private function applyBotLogger()
    {
        $logger = $this->bot->getLogger();
        $logger->popHandler();
        $logger->pushHandler($this['logger.handler']);
    }

    /**
     * Run the bot!
     */
    public function run()
    {
        $this->bot = $this['irc.bot'];

        $this->applyBotConfig();
        $this->applyBotLogger();

        $this->bot->run();
    }
}
