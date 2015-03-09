<?php

namespace Nomibot\Plugins;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Plugin\React\Command\CommandEventInterface as CommandEvent;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;

class Calculator extends AbstractPlugin
{
    private $constants = [
        'π' => M_PI,
        'pi' => M_PI,
        '2π' => M_PI * 2,
        '2pi' => M_PI * 2,
    ];

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'command.calc' => 'handle',
            'command.calc.help' => 'help',
        ];
    }

    /**
     * Show help text
     *
     * @param CommandEvent $event
     * @param Queue        $queue
     */
    public function help(CommandEvent $event, Queue $queue)
    {
        $channel = $event->getSource();

        $queue->ircPrivmsg($channel, "calculator [numeric] [operator] [numeric]");
        $queue->ircPrivmsg($channel, "=========================================");
        $queue->ircPrivmsg($channel, "Do some basic math.  Operator may be '+', '-', '*', '/', '**', or '%'.");
    }

    /**
     * Calculate
     *
     * @param CommandEvent $event
     * @param Queue        $queue
     */
    public function handle(CommandEvent $event, Queue $queue)
    {
        $params = $event->getCustomParams();

        if (count($params) < 3) {
            return;
        }

        $a = $this->resolveNumber($params[0]);
        $op = $params[1];
        $b = $this->resolveNumber($params[2]);

        if (!is_numeric($a) || !is_numeric($b)) {
            $queue->ircPrivmsg($event->getSource(), "Values provided must be numeric.");
            return;
        }

        if (!in_array($op, ['+', '-', '*', '/', '**', '%'])) {
            $queue->ircPrivmsg($event->getSource(), "Invalid operator provided.");
            return;
        }

        switch($op) {
            case '+':
                $ans = $a + $b;
                break;
            case '-':
                $ans = $a - $b;
                break;
            case '*':
                $ans = $a * $b;
                break;
            case '/':
                if ($b == 0) {
                    $queue->ircKick($event->getSource(), $event->getNick(), "I will not tolerate this!");
                    return;
                } else {
                    $ans = $a / $b;
                }
                break;
            case '**':
                $ans = $a ** $b;
                break;
            case '%':
                $ans = $a % $b;
                break;
        }

        $queue->ircPrivmsg($event->getSource(), $ans ?: "0.");
    }

    public function resolveNumber($constant)
    {
        if (!isset($this->constants[$constant])) {
            return $constant;
        }

        return $this->constants[$constant];
    }
}
