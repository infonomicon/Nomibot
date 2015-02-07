<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

class TossAction extends BaseAction
{
    public function __invoke()
    {
        if (!$this->game->isRunning() || $this->event->getSource() !== $this->game->getChannel()) {
            return;
        }

        $bombNick = $this->game->getBombHolder()->getNick();

        if (!$this->game->getBombHolder()->is($this->event->getNick())) {
            $this->message("Hey! No groping $bombNick while they have the bomb!");
            return;
        }

        $params = $this->event->getCustomParams();

        if (!$receiver = reset($params)) {
            return;
        }

        if ($this->game->getBombHolder()->is($receiver)) {
            $this->message("$receiver: You already have the bomb. Dumb ass.");
            return;
        }

        if ($this->isBotNick($receiver)) {
            $this->kick($bombNick, "I will not tolerate this!");
            $this->game->end();
            return;
        }

        if ($this->game->getOptOuts()->contains($receiver)) {
            $this->message("Sorry. It looks like $receiver doesn't want the bomb.");
            return;
        }

        if (!$this->isValidNick($receiver)) {
            $this->message("What kind of nick is $receiver!?");
            return;
        }

        if ($this->game->disarmOnToss()) {
            $this->message("As $bombNick was tossing the bomb to $receiver, it disarmed!  Everybody wins!");
            $this->game->end();
            return;
        }

        $this->game->setBombHolder($receiver);

        if ($this->game->explodeOnToss()) {
            foreach($this->game->getPlayerList() as $player) {
                $this->kick($player, "\x02The bomb is fragile...*BOOM!*\x02");
            }

            $this->game->end();
            return;
        }

        $seconds = $this->game->getTimer()->remaining();

        $this->message("$receiver: $bombNick set you up the bomb. You have [\x02$seconds\x02] seconds left!");
    }
}
