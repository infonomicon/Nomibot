<?php

namespace Nomibot\Plugins\WordGame;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Client\React\LoopAwareInterface;
use Phergie\Irc\Event\EventInterface as Event;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as CommandEvent;
use React\EventLoop\LoopInterface;
use Nomibot\Plugins\WordGame\WordProvider;
use Nomibot\Plugins\WordGame\Scoreboard;

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
     * @var \Phergie\Irc\Event\EventInterface
     */
    private $ircEvent;

    /**
     * @var \Phergie\Irc\Bot\React\EventQueueInterface
     */
    private $ircQueue;

    /**
     * @var \Nomibot\Plugins\WordGame\WordProvider
     */
    private $wordProvider;

    /**
     * @var \Nomibot\Plugins\WordGame\Scoreboard
     */
    private $scoreboard;

    /**
     * @var \Nomibot\Plugins\WordGame\Word
     */
    private $word;

    /**
     * @var array
     */
    private $updateQueue = [];

    /**
     * @var array
     */
    private $winComments = [
        "must be a fluke",
        "you rule!!",
        "how's that VD comin along?",
        "can I be your friend?",
        "you're such a badass!",
        "must have gotten all the easy ones!",
        "but you still suck!",
        "you must be on \x034fire!!",
        "cheater!",
    ];

    /**
     * @param WordProvider $wordProvider
     * @param Scoreboard   $scoreboard
     */
    public function __construct(WordProvider $wordProvider, Scoreboard $scoreboard)
    {
        $this->wordProvider = $wordProvider;
        $this->scoreboard = $scoreboard;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'command.word' => 'startGame',
            'command.score' => 'showTopTen',
            'irc.received.privmsg' => 'checkWord',
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
     * @param CommandEvent $event
     * @param Queue        $queue
     */
    public function startGame(CommandEvent $event, Queue $queue)
    {
        if ($this->isRunning()) {
            $this->sendAlreadyRunningMessage($event, $queue);
            return;
        }

        $this->ircEvent = $event;
        $this->ircQueue = $queue;
        $this->word = $this->wordProvider->getWord();

        $this->enqueueUpdates();
        $this->sendNextUpdate();

        $this->timer = $this->loop->addPeriodicTimer(15, [$this, 'sendNextUpdate']);
    }

    /**
     * End the running game, resetting all properties
     */
    private function endGame()
    {
        $this->loop->cancelTimer($this->timer);
        $this->timer = null;
        $this->ircEvent = null;
        $this->ircQueue = null;
        $this->word = null;
        $this->updateQueue = [];
    }

    /**
     * Send the next message in the update queue
     */
    public function sendNextUpdate()
    {
        $this->sendMessage(array_shift($this->updateQueue));

        if (count($this->updateQueue) === 0) {
            $this->endGame();
        }
    }

    /**
     * Queue up the messages for the current word
     */
    private function enqueueUpdates()
    {
        $purple = "\x036";
        $blue = "\x0312";
        $red = "\x034";

        $this->updateQueue = [
            "Unscramble --->  {$purple}{$this->word->getScrambled()}",
            "Clue --->  {$blue}{$this->word->getHint()}",
            "First letter --->  {$blue}{$this->word->getFirst(1)}",
            "First two letters --->  {$blue}{$this->word->getFirst(2)}",
            "Nobody got it...it's {$red}{$this->word}",
        ];
    }

    /**
     * Check if a typed word is correct
     *
     * @param Event $event
     */
    public function checkWord(Event $event)
    {
        if (!$this->isRunning()) {
            return;
        }

        if ($event->getSource() !== $this->ircEvent->getSource()) {
            return;
        }

        $message = $event->getParams();

        if (trim($message['text']) == $this->word) {
            $this->handleWin($event);
        }
    }

    /**
     * Handle win
     *
     * @param Event $event
     */
    private function handleWin(Event $event)
    {
        $winner = $event->getNick();

        $this->sendWinMessage($winner);
        $this->scoreboard->addWin($winner);
        $stats = $this->scoreboard->getStats($winner);

        if ($stats->getTotal() == 1) {
            $this->sendMessage("{$winner} this is your first ever win!!!...Don't you feel pathetic!");
        } else {
            $this->sendMessage("{$winner} you've won {$stats->getToday()} times today, {$this->getWinComment()}");
        }

        $this->endGame();
    }

    /**
     * Get a win comment
     *
     * @return string
     */
    public function getWinComment()
    {
        return $this->winComments[array_rand($this->winComments)];
    }

    /**
     * Show the top ten scores
     *
     * @param Event $event
     * @param Queue $queue
     */
    public function showTopTen(Event $event, Queue $queue)
    {
        if ($this->isRunning()) {
            $this->sendScoreUnavailableMessage();
            return;
        }

        $topStats = $this->scoreboard->getTopTen();

        if (count($topStats) < 1) {
            $queue->ircPrivmsg($event->getSource(), ' No one has scored yet!');
            return;
        }

        $message = '';
        $i = 0;
        foreach ($topStats as $stats) {
            $i++;
            $message .= "\x034 {$i}.\x03 {$stats->getNick()}({$stats->getTotal()}/{$stats->getToday()})  ";
        }

        $queue->ircPrivmsg($event->getSource(), 'The top 10 Scramble scorers are (Total/Daily):');
        $queue->ircPrivmsg($event->getSource(), $message);
    }

    /**
     * Send a message announcing the winner
     *
     * @param string $nick
     */
    private function sendWinMessage($nick)
    {
        $red = "\x034"; 
        $message = "Woohoo {$nick}!! You got it...{$red}{$this->word}";

        $this->sendMessage($message);
    }

    /**
     * Send a message when a user tries to get the
     * score list when a game is running
     */
    private function sendScoreUnavailableMessage()
    {
        $this->sendMessage("Sheesh! Can't you see we're playing here!!");
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
            $this->sendMessage('HEY! One word at a time!');
        } else {
            $message = "Sorry, they're already playing in %s, go join in. I'll tell em your coming, %s.";
            $message = sprintf($message, $this->ircEvent->getSource(), $event->getNick());
            $queue->ircPrivmsg($event->getSource(), $message);
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
}
