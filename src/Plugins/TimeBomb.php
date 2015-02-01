<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Client\React\LoopAwareInterface;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as Event;
use React\EventLoop\LoopInterface;

class TimeBomb extends AbstractPlugin implements LoopAwareInterface
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
     * @var int
     */
    private $seconds;

    /**
     * @var float
     */
    private $startTime;

    /**
     * @var \Phergie\Irc\Plugin\React\Command\CommandEventInterface
     */
    private $ircEvent;

    /**
     * @var \Phergie\Irc\Bot\React\EventQueueInterface
     */
    private $ircQueue;

    /**
     * @var string
     */
    private $bombNick;

    /**
     * @var array
     */
    private $players = [];

    /**
     * @var array
     */
    private $optouts = [];

    /**
     * @var string
     */
    private $optoutFilename;

    /**
     * @var integer
     */
    private $tossExplosionChance = 1;

    /**
     * @var integer
     */
    private $wireCount;

    /**
     * @var integer
     */
    private $correctWireIndex;

    /**
     * @var array
     */
    private $wires = [
        'Red',
        'Orange',
        'Yellow',
        'Green',
        'Blue',
        'Indigo',
        'Violet',
        'Black',
        'White',
        'Grey',
        'Brown',
        'Pink',
        'Mauve',
        'Beige',
        'Aquamarine',
        'Chartreuse',
        'Bisque',
        'Crimson',
        'Fuchsia',
        'Gold',
        'Ivory',
        'Khaki',
        'Lavender',
        'Lime',
        'Magenta',
        'Maroon',
        'Navy',
        'Olive',
        'Plum',
        'Silver',
        'Tan',
        'Teal',
        'Turquoise',
    ];

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->optoutFilename = $options['optoutfile'];
        $this->loadOptouts();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'command.timebomb' => 'startGame',
            'command.bombtoss' => 'handleToss',
            'command.cut' => 'handleCut',
            'command.bombout' => 'handleOptOut',
            'command.bombin' => 'handleOptIn',
            'command.timebomb.help' => 'helpTimeBomb',
            'command.bombtoss.help' => 'helpBombToss',
            'command.cut.help' => 'helpCut',
            'command.bombout.help' => 'helpBombOut',
            'command.bombin.help' => 'helpBombIn',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * Show help text for timebomb command
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function helpTimeBomb(Event $event, Queue $queue)
    {
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, "timebomb [nick:optional]");
        $queue->ircPrivmsg($channel, "========================");
        $queue->ircPrivmsg($channel, "Start a timebomb game. It starts with you, unless another nick is specified.");
    }

    /**
     * Show help text for bombtoss command
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function helpBombToss(Event $event, Queue $queue)
    {
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, "bombtoss [nick]");
        $queue->ircPrivmsg($channel, "===============");
        $queue->ircPrivmsg($channel, "When a timebomb game is running, and you have the bomb, this will pass it to another player.");
    }

    /**
     * Show help text for cut command
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function helpCut(Event $event, Queue $queue)
    {
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, "cut [color]");
        $queue->ircPrivmsg($channel, "===========");
        $queue->ircPrivmsg($channel, "When a timebomb game is running, and you have the bomb, this will let you cut a wire in an attempt to defuse it.");
    }

    /**
     * Show help text for bombout command
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function helpBombOut(Event $event, Queue $queue)
    {
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, "bombout (no arguments)");
        $queue->ircPrivmsg($channel, "======================");
        $queue->ircPrivmsg($channel, "Opt-out of the timebomb game.");
    }

    /**
     * Show help text for bombin command
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function helpBombIn(Event $event, Queue $queue)
    {
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, "bombin (no arguments)");
        $queue->ircPrivmsg($channel, "=====================");
        $queue->ircPrivmsg($channel, "Opt-in to the timebomb game.");
    }

    /**
     * Check if a game is running
     *
     * @return bool
     */
    public function isRunning()
    {
        return null !== $this->timer;
    }

    /**
     * Start a game
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function startGame(Event $event, Queue $queue)
    {
        if ($this->isRunning()) {
            $this->sendAlreadyRunningMessage($event, $queue);
            return;
        }

        if (isset($this->optouts[strtolower($event->getNick())])) {
            $queue->ircPrivmsg($event->getSource(), "You can't bomb anyone if you're not opted in!");
            return;
        }

        $params = $event->getCustomParams();

        $this->bombNick = isset($params[0]) ? trim($params[0]) : $event->getNick();

        if (strtolower($this->bombNick) === strtolower($event->getConnection()->getNickname())) {
            $queue->ircKick($event->getSource(), $event->getNick(), "I will not tollerate this!");
            $this->bombNick = null;
            return;
        }

        if (isset($this->optouts[strtolower($this->bombNick)])) {
            $optedout = $this->bombNick;
            $this->bombNick = $event->getNick();
            $queue->ircPrivmsg($event->getSource(), "{$optedout} isn't playing...");
        }

        if (!$this->isValidNick($this->bombNick)) {
            $queue->ircPrivmsg($event->getSource(), "What kind of nick is {$this->bombNick}!?");
            $this->bombNick = $event->getNick();
        }

        $this->players[$event->getNick()] = 1;
        $this->players[$this->bombNick] = 1;
        $this->ircEvent = $event;
        $this->ircQueue = $queue;

        $this->seconds = rand(120, 240);

        shuffle($this->wires);

        $this->wireCount = rand(1, 3);

        // If there's more than one wire, choose a correct one
        // If there's only one, give it a 50/50 chance
        if ($this->wireCount > 1) {
            $this->correctWireIndex = rand(0, $this->wireCount - 1);
        } else {
            $this->correctWireIndex = rand(0, 1);
        }

        $this->sendAction("stuffs the bomb into {$this->bombNick}'s pants.  The display reads [\x02$this->seconds\x02] seconds.");

        if ($this->wireCount === 1) {
            $this->sendMessage("Defuse the bomb by cutting the correct wire. There is {$this->getWireCountWord()} wire. It is {$this->listWires()}.  Use !cut <color>");
        } else {
            $this->sendMessage("Defuse the bomb by cutting the correct wire. There are {$this->getWireCountWord()} wires. They are {$this->listWires()}.  Use !cut <color>");
        }

        $this->startTime = microtime(true);
        $this->timer = $this->loop->addTimer($this->seconds, [$this, 'timerDetonate']);
    }

    /**
     * Get the word for a number (0-9)
     *
     * @return string
     */
    private function getWireCountWord()
    {
        $words = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
        ];

        return $words[$this->wireCount];
    }

    /**
     * Get the list of wires
     *
     * @return string
     */
    private function listWires()
    {
        $text = $this->wires[0];

        for ($i = 1; $i < $this->wireCount ; $i++) {
            if ($i === $this->wireCount - 1) {
                $text .= ' and ' . $this->wires[$i];
            } else {
                $text .= ', ' . $this->wires[$i];
            }
        }

        return $text;
    }

    /**
     * Callback when the time's up
     */
    public function timerDetonate()
    {
        $this->ircQueue->ircKick($this->ircEvent->getSource(), $this->bombNick, "\x02*BOOM!*\x02");
        $this->endGame();
    }

    /**
     * End the running game, resetting all properties
     */
    private function endGame()
    {
        $this->loop->cancelTimer($this->timer);
        $this->timer = null;
        $this->seconds = null;
        $this->startTime = null;
        $this->ircEvent = null;
        $this->ircQueue = null;
        $this->bombNick = null;
        $this->bombWires = null;
        $this->tossExplosionChance = 1;
        $this->players = [];
    }

    /**
     * Toss the bomb to another nick
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handleToss(Event $event, Queue $queue)
    {
        if (!$this->isRunning() || $event->getSource() !== $this->ircEvent->getSource()) {
            return;
        }

        if ($event->getNick() !== $this->bombNick) {
            $this->sendMessage("Hey! No groping {$this->bombNick} while they have the bomb!");
            return;
        }

        $params = $event->getCustomParams();

        if (count($params) < 1) {
            return;
        }

        if (trim(strtolower($params[0])) === strtolower($this->bombNick)) {
            $this->sendMessage("{$this->bombNick}: You already haz the bomb. Dumbass");
            return;
        }

        $oldNick = $this->bombNick;
        $this->bombNick = trim($params[0]);

        if (isset($this->optouts[strtolower($this->bombNick)])) {
            $this->sendMessage("Sorry. It looks like {$this->bombNick} doesn't want the bomb.");
            $this->bombNick = $oldNick;
            return;
        }

        if (strtolower($this->bombNick) === strtolower($event->getConnection()->getNickname())) {
            $queue->ircKick($event->getSource(), $event->getNick(), "I will not tollerate this!");
            $this->endGame();
            return;
        }

        if (!$this->isValidNick($this->bombNick)) {
            $queue->ircPrivmsg($event->getSource(), "What kind of nick is {$this->bombNick}!?");
            $this->bombNick = $oldNick;
            return;
        }

        $this->players[$this->bombNick] = 1;

        if (rand(0, 99) === 0) {
            $this->sendMessage("As {$oldNick} was tossing the bomb to {$this->bombNick}, it disarmed!  Everybody wins!");
            $this->endGame();
            return;
        }

        if ($this->explodeOnToss()) {
            foreach ($this->players as $player => $val) {
                $queue->ircKick($event->getSource(), $player, "\x02The bomb is fragile...*BOOM!*\x02");
            }

            $this->endGame();
            return;
        }

        $this->sendMessage("{$this->bombNick}: {$oldNick} set you up the bomb. You have [\x02{$this->getSecondsRemaining()}\x02] seconds left!");
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
     * Get the seconds remaining
     *
     * @return int
     */
    private function getSecondsRemaining()
    {
        return floor($this->seconds - (microtime(true) - $this->startTime));
    }

    /**
     * Check if a typed word is correct
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handleCut(Event $event, Queue $queue)
    {
        if (!$this->isRunning() || $event->getNick() !== $this->bombNick || $event->getSource() !== $this->ircEvent->getSource()) {
            return;
        }

        $params = $event->getCustomParams();

        if (count($params) < 1) {
            return;
        }

        $wire = trim(strtolower($params[0]));

        for ($i = 0 ; $i < $this->wireCount ; $i++) {
            if ($wire === strtolower($this->wires[$i])) {
                if ($this->correctWireIndex === $i) {
                    $this->sendMessage("{$this->bombNick} cut the {$this->wires[$i]} wire.  This has defused the bomb with [\x02{$this->getSecondsRemaining()}\x02] seconds to spare!");
                } elseif ($this->wireCount === 1) {
                    $queue->ircKick($event->getSource(), $this->bombNick, "\x02...*trollface.jpg*\x02");
                } else {
                    $queue->ircKick($event->getSource(), $this->bombNick, "\x02snip...*BOOM!*\x02");
                }

                $this->endGame();
            }
        }
    }

    /**
     * Load optouts from the optout file
     */
    private function loadOptouts()
    {
        $optouts = json_decode(file_get_contents($this->optoutFilename), true);

        foreach ($optouts as $optout) {
            $this->optouts[$optout] = true;
        }
    }

    /**
     * Persist optouts to the optout file
     */
    private function saveOptouts()
    {
        file_put_contents($this->optoutFilename, json_encode(array_keys($this->optouts)));
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
            $queue->ircPrivmsg($event->getSource(), "{$event->getNick()}: you coward! No opting out during a game!");
            return;
        }

        $nick = strtolower($event->getNick());

        if (isset($this->optouts[$nick])) {
            $queue->ircPrivmsg($event->getSource(), "{$event->getNick()}: You're already opted out.");
            return;
        }

        $this->optouts[$nick] = true;
        $this->saveOptouts();

        $queue->ircPrivmsg($event->getSource(), "{$event->getNick()}: You've opted out of the timebomb game.");
    }

    /**
     * Allow opting in to the game
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function handleOptIn(Event $event, Queue $queue)
    {
        $nick = strtolower($event->getNick());

        if (!isset($this->optouts[$nick])) {
            $queue->ircPrivmsg($event->getSource(), "{$event->getNick()}: You're already opted in.");
            return;
        }

        unset($this->optouts[$nick]);
        $this->saveOptouts();

        $queue->ircPrivmsg($event->getSource(), "{$event->getNick()}: You've opted back in to the timebomb game.");
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
        if ($event->getSource() === $this->ircEvent->getSource()) {
            $this->sendAction("points at the bulge in the back of {$this->bombNick}'s pants.");
        } else {
            $queue->ircPrivmsg($event->getSource(), "I don't have a single bomb to spare. :-(");
        }
    }

    /**
     * Send a message to the source of the current game
     *
     * @param string $message
     */
    private function sendMessage($message)
    {
        $this->ircQueue->ircPrivmsg($this->ircEvent->getSource(), $message);
    }

    /**
     * Send an action to the source of the current game
     *
     * @param string $action
     */
    private function sendAction($action)
    {
        $this->sendMessage("\x01ACTION {$action}\x01");
    }

    /**
     * Check if a nick is valid
     *
     * @see RFC 2812 section 2.3.1
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
