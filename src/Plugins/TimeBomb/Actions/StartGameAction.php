<?php

namespace Nomibot\Plugins\TimeBomb\Actions;

use NumberFormatter;

class StartGameAction extends BaseAction
{
    /**
     * Handle the event to start a game
     */
    public function __invoke()
    {
        if ($this->game->isRunning()) {
            $this->sendRunningMessage();
            return;
        }

        $sender = $this->event->getNick();

        if ($this->game->getOptOuts()->contains($sender)) {
            $this->message("You can't bomb anyone if you're not opted in!");
            return;
        }

        $receiver = $this->getNickParam();

        if ($this->isBotNick($receiver)) {
            $this->kick($sender, "I will not tolerate this!");
            return;
        }

        if ($this->game->getOptOuts()->contains($receiver)) {
            $this->message("$receiver isn't playing...");
            $receiver = $sender;
        }

        if (!$this->isValidNick($receiver)) {
            $this->message("What kind of nick is $receiver!?");
            $receiver = $sender;
        }

        $this->game->setChannel($this->event->getSource());
        $action = new TimeUpAction($this->event, $this->queue, $this->game);
        $this->game->start($sender, $receiver, [$action, '__invoke']);
        $this->sendStartMessage();
    }

    /**
     * Send a message if the game is currently running
     */
    private function sendRunningMessage()
    {
        if ($this->game->getChannel() === $this->event->getSource()) {
            $playerNick = $this->game->getBombHolder()->getNick();
            $this->message("points at the bulge in the back of {$playerNick}'s pants.");
        } else {
            $this->message("I don't have a single bomb to spare. :-(");
        }
    }

    /**
     * Send the start message using the current game state
     */
    private function sendStartMessage()
    {
        $seconds = $this->game->getTimer()->total();
        $nick = $this->game->getBombHolder()->getNick();
        $wires = $this->game->getBomb()->getWires();
        $wireList = $this->languageList($wires);
        $wireCount = $this->languageNumber(count($wires));

        $this->action("stuffs the bomb into $nick's pants.  The display reads [\x02$seconds\x02] seconds.");

        if (count($wires) === 1) {
            $this->message("Defuse the bomb by cutting the correct wire. There is $wireCount wire. It is $wireList.  Use !cut <color>");
        } else {
            $this->message("Defuse the bomb by cutting the correct wire. There are $wireCount wires. They are $wireList.  Use !cut <color>");
        }
    }

    /**
     * Convert an integer to the spelled out word
     *
     * @param  integer $count The integer to convert to word
     * @return string
     */
    private function languageNumber($count)
    {
        $formatter = new NumberFormatter('en_US', NumberFormatter::SPELLOUT);

        return $formatter->parse($count);
    }

    /**
     * Comma separate a list, with an 'and' if needed before the last element
     *
     * @param  array $array
     * @return string
     */
    private function languageList(array $array)
    {
        if (count($array) === 1) {
            return reset($array);
        }

        $lastItem = array_pop($array);
        $str = implode(', ', $array);
        $str .= ' and ' . $lastItem;

        return $str;
    }

    /**
     * Get the nick param from the event
     *
     * @return null|string
     */
    private function getNickParam()
    {
        $params = $this->event->getCustomParams();

        return isset($params[0]) ? trim($params[0]) : null;
    }
}
