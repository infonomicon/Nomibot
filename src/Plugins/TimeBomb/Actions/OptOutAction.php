<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

class OptOutAction extends BaseAction
{
    /**
     * Opt a nick out of the game
     */
    public function __invoke()
    {
        $nick = $this->event->getNick();

        if ($this->game->isRunning()) {
            $this->message("$nick: you coward! No opting out during a game!");
            return;
        }

        $optouts = $this->game->getOptOuts();

        if ($optouts->contains($nick)) {
            $this->message("$nick: You're already opted out.");
            return;
        }

        $optouts->add($nick);

        $this->message("$nick: You've opted out of the timebomb game.");
    }
}
