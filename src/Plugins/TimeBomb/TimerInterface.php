<?php

namespace Nomibot\Plugins\TimeBomb;

interface TimerInterface
{
    /**
     * Start the timer
     *
     * @param  integer  $seconds  The number of seconds on the timer
     * @param  callable $callable The action to take when the timer is up
     *
     * @return mixed
     */
    public function start($seconds, callable $callable);

    /**
     * Get the total number of seconds the timer was set for
     *
     * @return integer
     */
    public function total();

    /**
     * Check if the timer is running
     *
     * @return boolean
     */
    public function running();

    /**
     * Get the number of seconds remaining
     *
     * @return integer
     */
    public function remaining();

    /**
     * Cancel a running timer
     *
     * @return void
     */
    public function cancel();
}
