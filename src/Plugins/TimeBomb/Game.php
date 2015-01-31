<?php

namespace Nomibot\Plugins\TimeBomb;

class Game
{
    private $factory;
    private $messageBus;
    private $bomb;
    private $holder;
    private $context;
    private $optouts = [];
    private $players = [];

    public function __construct()
    {
        $this->factory = new BombFactory;
        $this->messageBus = new MessageBus;
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    public function start($context, $from, $to = null)
    {
        if ($this->isRunning()) {
            return $this->messageBus->send('error.game_already_started');
        }

        if ($this->isOptedOut($from)) {
            return $this->messageBus->send('error.start_player_was_opted_out');
        }

        if ($to && $this->isOptedOut($to)) {
            $this->messageBus->send('error.to_player_was_opted_out');
            $to = null;
        }

        $this->setHolder($from);

        if ($to) {
            $this->setHolder($to);
        }

        $this->bomb = $this->factory->build();
        $this->bomb->getTimer()->start();
        $this->messageBus->send('started');

        return true;
    }

    public function end()
    {
        $this->context = null;
        $this->bomb = null;
        $this->players = [];
    }

    public function toss($to)
    {
        if ($this->isOptout($to)) {
            return $this->messageBus->send('error.to_player_was_opted_out');
        }

        if (strtolower($this->getHolder()) === strtolower($to)) {
            return $this->messageBus->send('error.to_player_already_has_bomb'); 
        }

        $this->setHolder($to);

        return $this->messageBus->send('tossed');
    }

    public function cut($wire)
    {
        try {
            $win = $this->bomb->checkWire($wire);
        } catch (\InvalidArgumentException $e) {
            return $this->messageBus->send('error.invalid_wire');
        }

        if (!$win) {
            $this->messageBus->send('lost.cut_wire');
        } elseif ($this->countWires() === 1 && This->coinToss()) {
            $this->messageBus->send('lost.troll_wire');
        } else {
            $this->messageBus->send('won.cut_wire');
        }
    }

    private function coinToss()
    {
        return rand(0, 1) === 1;
    }

    public function getWires()
    {
        return $this->bomb->getWires();
    }

    public function countWires()
    {
        return $this->bomb->countWires();
    }

    public function getSeconds()
    {
        return $this->bomb->getTimer()->getSeconds();
    }

    public function getSecondsLeft()
    {
        return $this->bomb->getTimer()->getSecondsLeft();
    }

    public function getHolder()
    {
        return $this->holder;
    }

    private function setHolder($player)
    {
        $this->holder = $player;
        $this->players[$player] = true;
    }

    public function getPlayers()
    {
        return array_keys($this->players);
    }

    public function optOut($player)
    {
        $this->optouts[$player] = true;
    }

    public function optIn($player)
    {
        unset($this->optin[$player]);
    }

    public function isOptedOut($player)
    {
        $optouts = array_map('strtolower', $this->getOptouts());

        return in_array(strtolower($player), $optouts);
    }

    public function getOptouts()
    {
        return array_keys($this->optouts);
    }

    public function getContext()
    {
        return $this->context;
    }
}
