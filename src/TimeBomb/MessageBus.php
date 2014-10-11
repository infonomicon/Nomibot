<?php

namespace Infonomicon\IrcBot\TimeBomb;

class MessageBus
{
    private $handlers = [];

    public function addListener($message, callable $callable)
    {
        if (!isset($this->handlers[$message])) {
            $this->handlers[$message] = [];
        }

        $this->handlers[$message][] = $callable;
    }

    public function addListeners(array $listeners)
    {
        foreach ($listeners as $message => $callable) {
            $this->addListener($message, $callable);
        }
    }

    public function send($message)
    {
        if (!isset($this->handlers[$message])) {
            return;
        }

        foreach ($this->handlers[$message] as $handler) {
            $handler();
        }
    }
}
