<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

class CutAction extends BaseAction
{
    public function __invoke()
    {
        if (!$this->game->isRunning() || !$this->validateBombHolder() || !$this->validateChannel()) {
            return;
        }

        if (!$wire = reset($this->event->getCustomParams())) {
            return;
        }

        $bomb = $this->game->getBomb();
        $bombHolder = $this->game->getBombHolder();

        if (!$bomb->hasWire($wire)) {
            return;
        }

        if ($bomb->isFuse($wire)) {
            $holderNick = $bombHolder->getNick();
            $seconds = $this->game->getTimer()->remaining();
            $this->message("$holderNick cut the $wire wire.  This has defused the bomb with [\x02$seconds\x02] seconds to spare!");
            $this->game->end();
            return;
        }

        if ($bomb->countWires() === 1) {
            $message = "\x02...*trollface.jpg*\x02";
        } else {
            $message = "\x02snip...*BOOM!*\x02";
        }

        $this->kick($bombHolder, $message);
        $this->game->end();
    }

    private function validateBombHolder()
    {
        return $this->game->getBombHolder()->is($this->event->getNick());
    }

    private function validateChannel()
    {
        return $this->game->getChannel() === $this->event->getSource();
    }
}
