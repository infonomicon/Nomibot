<?php

namespace Infonomicon\IrcBot\TimeBomb;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Client\React\LoopAwareInterface;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as Event;
use React\EventLoop\LoopInterface;

/**
 * Time bomb plugin
 */
class Plugin extends AbstractPlugin implements LoopAwareInterface
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    private $loop;

    /**
     * @var \React\EventLoop\Timer\TimerInterface
     */
    private $timer;

    /**
     * @var Game
     */
    private $game;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->game = new Game;
        $this->game->getMessageBus()->addListeners([
            'started'                           => [$this, 'handleGameStart'],
            'won.cut_wire'                      => [$this, 'handleWin'],
            'lost.cut_wire'                     => [$this, 'handleCutLoss'],
            'lost.troll_wire'                   => [$this, 'handleTrollLoss'],
            'tossed'                            => [$this, 'handleToss'],
            'error.invalid_wire'                => [$this, 'handleInvalidWire'],
            'error.start_player_was_opted_out'  => [$this, 'handleFromOptedOutError'],
            'error.to_player_was_opted_out'     => [$this, 'handleToOptedOutError'],
            'error.to_player_already_has_bomb'  => [$this, 'handleAlreadyHasBombError'],
            'error.game_already_started'        => [$this, 'handleGameAlreadyStarted'],
            'won.cut_wire'                      => [$this, 'handleWin'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'command.timebomb' => 'commandTimebomb',
            'command.bombtoss' => 'commandBombtoss',
            'command.cut'      => 'commandCut',
            'command.bombout'  => 'commandBombout',
            'command.bombin'   => 'commandBombin',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    private function getParam(Event $event, $index = 1, $default = null)
    {
        $params = $event->getCustomParams();
        $index--;

        return isset($params[$index]) ? trim($params[$index]) : $default;
    }

    private function isBotNick($event, $nick)
    {
        return strtolower($nick) === strtolower($event->getConnection()->getNickname());
    }

    private function msg()
    {
        $msg = call_user_func_array('sprintf', func_get_args());
        $this->queue->ircPrivmsg($this->game->getContext(), $msg); 
    }

    private function action()
    {
        $action = call_user_func_array('sprintf', func_get_args());
        $this->msg("\x01ACTION {$action}\x01");
    }

    private function kick($nick, $msg)
    {
        $this->queue->ircKick($this->game->getContext(), $nick, $msg);
    }

    /**
     * Start a game
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function commandTimebomb(Event $event, Queue $queue)
    {
        $this->queue = $queue;
        $from = $event->getNick();
        $to = $this->getParam($event, 1);

        if ($this->isBotNick($event, $to)) {
            return $this->kick($from, "I will not tollerate this!");
        }

        if (!$this->isValidNick($to)) {
            $this->msg("What kind of nick is %s!?", $to);
            $to = null;
        }

        $this->game->start($event->getSource(), $from, $to);
    }

    public function handleGameStart()
    {
        $this->timer = $this->loop->addTimer($this->game->getSeconds(), [$this, 'timerDetonate']);
        $holder = $this->game->getHolder();
        $seconds = $this->game->getSeconds();
        $wireCount = $this->game->countWires();
        $wireList = implode(', ', $this->game->getWires());
        $wireList = preg_replace('/(, )[a-zA-z ]$/', ' and ', $wireList);
        $formatter = new \NumberFormatter('en-US', \NumberFormatter::SPELLOUT);

        $this->action("stuffs the bomb into %s's pants.  The display reads [\x02%s\x02] seconds.", $holder, $seconds);

        $msg = "Defuse the bomb by cutting the correct wire. ";

        if ($wireCount === 1) {
            $msg .= "There is %s wire. It is %s.  Use !cut <color>";
        } else {
            $msg .= "There are %s wires. They are %s.  Use !cut <color>";
        }

        $this->msg($msg, $formatter->format($wireCount), $wireList);
    }

    /**
     * Callback when the time's up
     */
    public function timerDetonate()
    {
        $this->kick($this->game->getHolder(), "\x02*BOOM!*\x02");
        $this->endGame();
    }

    /**
     * End the running game, resetting all properties
     */
    private function endGame()
    {
        $this->loop->cancelTimer($this->timer);
        $this->timer = null;
        $this->game = null;
        $this->queue = null;
    }

    /**
     * Toss the bomb to another nick
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handleToss(Event $event, Queue $queue)
    {
        if (!$this->isRunning() || !$this->isFromChannel($event)) {
            return;
        }

        if (!$this->isFromHolder($event)) {
            return $this->msg("Hey! No groping %s while they have the bomb!", $this->game->getHolder());
        }

        if (!$to = $this->getParam($event, 1)) {
            return;
        }

        if (strtolower($to) === strtolower($this->game->getHolder())) {
            return $this->msg("%s: You already haz the bomb. Dumb ass.", $to);
        }

        if ($this->isOptout($to)) {
            return $this->msg("Sorry. It looks like %s doesn't want the bomb.", $to);
        }

        if ($this->isBotNick($event, $to)) {
            $this->kick($event->getNick(), "I will not tollerate this!");
            return $this->endGame();
        }

        if (!$this->isValidNick($to)) {
            return $this->msg("What kind of nick is %s!?", $to);
        }

        $this->game->toss($to);

        if (rand(0, 99) === 0) {
            $this->msg("As %s was tossing the bomb to %s, it disarmed!  Everybody wins!", $event->getNick(), $to);
            return $this->endGame();
        }

        if ($this->explodeOnToss()) {
            foreach ($this->game->getPlayers() as $player) {
                $this->kick($player, "\x02The bomb is fragile...*BOOM!*\x02");
            }

            return $this->endGame();
        }

        $this->msg("%s: %s set you up the bomb. You have [\x02%s\x02] seconds left!", $to, $event->getNick(), $this->game->getSecondsLeft());
    }

    /**
     * Does the bomb explode during a toss
     *
     * @return boolean
     */
    private function explodeOnToss()
    {
        if (rand(1, 100) <= $this->tossExplosionChance) {
            return true;
        }

        $this->tossExplosionChance += rand(0, 10);

        return false;
    }

    /**
     * Check if a typed word is correct
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handleCut(Event $event, Queue $queue)
    {
        if (!$this->isRunning() || !$this->isFromHolder($event) || !$this->isFromChannel($event)) {
            return;
        }

        if (!$wire = $this->getParam($event, 1)) {
            return;
        }

        try {
            $win = $this->game->cut($wire);
        } catch (\InvalidArgumentException $e) {
            return;
        }

        if ($win) {
            $this->msg(
                "%s cut the %s wire.  This has defused the bomb with [\x02%s\x02] seconds to spare!",
                $event->getNick(),
                ucwords($wire),
                $this->game->getSecondsLeft()
            );
        } elseif ($this->game->countWires() === 1) {
            $this->kick($this->game->getHolder(), "\x02...*trollface.jpg*\x02");
        } else {
            $this->kick($this->game->getHolder(), "\x02snip...*BOOM!*\x02");
        }

        $this->endGame();
    }

    /**
     * Allow opting out of the game
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handleOptOut(Event $event, Queue $queue)
    {
        if ($this->isRunning()) {
            return $queue->ircPrivmsg($event->getSource(), "{$event->getNick()}: you coward! No opting out during a game!");
        }

        if ($this->optOut($event->getNick())) {
            $queue->ircPrivmsg($event->getSource(), "{$event->getNick()}: You've opted out of the timebomb game.");
        } else {
            $queue->ircPrivmsg($event->getSource(), "{$event->getNick()}: You're already opted out.");
        }
    }

    /**
     * Allow opting in to the game
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handleOptIn(Event $event, Queue $queue)
    {
        if ($this->optIn($event->getNick()) {
            $queue->ircPrivmsg($event->getSource(), "{$event->getNick()}: You've opted back in to the timebomb game.");
        } else {
            $queue->ircPrivmsg($event->getSource(), "{$event->getNick()}: You're already opted in.");
        }
    }

    public function optOut($nick)
    {
        $nick = strtolower($nick);

        if (isset($this->optouts[$nick])) {
            return false;
        }

        return $this->optouts[$nick] = true;
    }

    public function outIn($nick)
    {
        $nick = strtolower($nick);

        if (!isset($this->optouts[$nick])) {
            return false;
        }

        unset($this->optouts[$nick]);

        return true;
    }

    /**
     * Send a message when a user tries to play a
     * new game when one is already in progress
     *
     * @param Event $event
     * @param Queue $queue
     */
    private function sendAlreadyRunningMessage(Event $event, Queue $queue)
    {
        if ($this->isFromChannel($event)) {
            $this->action("points at the bulge in the back of %s's pants.", $this->game->getHolder());
        } else {
            $queue->ircPrivmsg($event->getSource(), "I don't have a single bomb to spare. :-(");
        }
    }

    /**
     * Check if a nick is valid
     *
     * RFC 2812 section 2.3.1
     *
     * @param string $nick
     * @return boolean
     */
    private function isValidNick($nick)
    {
        $letter = 'a-zA-Z';
        $number = '0-9';
        $special = preg_quote('[]\`_^{|}');
        $pattern =  "/^(?:[$letter$special][$letter$number$special-]*)$/";

        return preg_match($pattern, $nick);
    }
}
