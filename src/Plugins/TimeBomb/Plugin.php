<?php

namespace Nomibot\Plugins\TimeBomb;

use BadMethodCallException;
use InvalidArgumentException;
use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Client\React\LoopAwareInterface;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as Event;
use React\EventLoop\LoopInterface;

class Plugin extends AbstractPlugin implements LoopAwareInterface
{
    /**
     * @var Game
     */
    private $game;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @param array $options Game options
     */
    public function __construct(array $options = [])
    {
        $this->game = new Game($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'command.timebomb' => 'startGame',
            'command.bombtoss' => 'toss',
            'command.cut' => 'cut',
            'command.bombout' => 'optOut',
            'command.bombin' => 'optIn',
            'command.timebomb.help' => 'help',
            'command.bombtoss.help' => 'help',
            'command.cut.help' => 'help',
            'command.bombout.help' => 'help',
            'command.bombin.help' => 'help',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->game->setTimer(new ReactTimer($loop));
    }

    /**
     * Magic to load and invoke actions routed by the subscribed events
     *
     * @param  string $name The method name
     * @param  array  $args The method arguments
     *
     * @return mixed
     */
    public function __call($name, array $args = [])
    {
        $className = 'Nomibot\\Plugins\\TimeBomb\\Actions\\' . ucfirst($name) . 'Action';

        if (!class_exists($className)) {
            throw new BadMethodCallException("No handler for '$name' method.");
        }

        if (!isset($args[0]) || !isset($args[1]) || !($args[0] instanceof Event && $args[1] instanceof Queue)) {
            throw new InvalidArgumentException("Arguments to '$name' method must by an irc event, and queue.");
        }

        $action = new $className($args[0], $args[1], $this->game);

        return $action();
    }
}
