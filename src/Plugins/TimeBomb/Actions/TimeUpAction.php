<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

class TimeUpAction extends BaseAction
{
    /**
     * Handle time up action
     */
    public function __invoke()
    {
        $holder = $this->game->getBombHolder()->getNick();

        $this->kick($holder, "\x02*BOOM!*\x02");
        $this->game->end();
    }
}
