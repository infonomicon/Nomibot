<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

class OptInAction extends BaseAction
{
    /**
     * Opt a nick in to the game
     */
    public function __invoke()
    {
        $nick = $this->event->getNick();
        $optouts = $this->game->getOptOuts();

        if (!$optouts->contains($nick)) {
            $this->message("$nick: You're already opted in.");
            return;
        }

        $optouts->remove($nick);

        $this->message("$nick: You've opted back in to the timebomb game.");
    }
}
