<?php

namespace Nomibot\Plugins\TimeBomb;

use React\EventLoop\LoopInterface;

class ReactTimer implements TimerInterface
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
     * @var integer
     */
    private $totalSeconds;

    /**
     * @var float
     */
    private $startTime;

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * {@inheritdoc}
     */
    public function start($seconds, callable $callable)
    {
        $this->startTime = microtime(true);
        $this->totalSeconds = $seconds;
        $this->timer = $this->loop->addTimer($this->totalSeconds, $callable);
    }

    /**
     * {@inheritdoc}
     */
    public function total()
    {
        return $this->totalSeconds;
    }

    /**
     * {@inheritdoc}
     */
    public function running()
    {
        return $this->timer !== null && $this->timer->isActive();
    }

    /**
     * {@inheritdoc}
     */
    public function remaining()
    {
        return floor($this->totalSeconds - (microtime(true) - $this->startTime));
    }

    /**
     * {@inheritdoc}
     */
    public function cancel()
    {
        $this->loop->cancelTimer($this->timer);
        $this->timer = null;
    }
}
