<?php

namespace Nomibot\Plugins\TimeBomb;

use Mockery as m;

use React\EventLoop\Timer;

class ReactTimerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testConstruct()
    {
        $loop = m::mock('React\EventLoop\LoopInterface');
        $timer = new ReactTimer($loop);
        $this->assertInstanceOf('Nomibot\Plugins\TimeBomb\TimerInterface', $timer);
    }

    public function testStart()
    {
        $loop = m::mock('React\EventLoop\LoopInterface');
        $timer = new ReactTimer($loop);
        $internalTimer = m::mock('React\EventLoop\Timer\TimerInterface');
        $callable = function () {};

        $loop
            ->shouldReceive('addTimer')
            ->withArgs([123, $callable])
            ->andReturn($internalTimer);

        $timer->start(123, $callable);
    }

    public function testRunning()
    {
        $loop = m::mock('React\EventLoop\LoopInterface');
        $timer = new ReactTimer($loop);
        $internalTimer = m::mock('React\EventLoop\Timer\TimerInterface');
        $callable = function () {};

        $loop
            ->shouldReceive('addTimer')
            ->withArgs([123, $callable])
            ->andReturn($internalTimer);

        $timer->start(123, $callable);

        $internalTimer
            ->shouldReceive('isActive')
            ->andReturn(true);

        $result = $timer->running();

        $this->assertTrue($result);
    }


    public function testTotal()
    {
        $loop = m::mock('React\EventLoop\LoopInterface');
        $timer = new ReactTimer($loop);
        $internalTimer = m::mock('React\EventLoop\Timer\TimerInterface');
        $callable = function () {};

        $loop
            ->shouldReceive('addTimer')
            ->withArgs([123, $callable])
            ->andReturn($internalTimer);

        $timer->start(123, $callable);

        $this->assertEquals(123, $timer->total());
    }

    public function testRemaining()
    {
        $loop = m::mock('React\EventLoop\LoopInterface');
        $timer = new ReactTimer($loop);
        $internalTimer = m::mock('React\EventLoop\Timer\TimerInterface');
        $callable = function () {};

        $loop
            ->shouldReceive('addTimer')
            ->withArgs([123, $callable])
            ->andReturn($internalTimer);

        $timer->start(123, $callable);

        $this->assertEquals(122, $timer->remaining());
    }

    public function testCancel()
    {
        $loop = m::mock('React\EventLoop\LoopInterface');
        $timer = new ReactTimer($loop);
        $internalTimer = m::mock('React\EventLoop\Timer\TimerInterface');
        $callable = function () {};

        $loop
            ->shouldReceive('addTimer')
            ->withArgs([123, $callable])
            ->andReturn($internalTimer);

        $timer->start(123, $callable);

        $loop
            ->shouldReceive('cancelTimer')
            ->withArgs([$internalTimer]);

        $internalTimer
            ->shouldReceive('isActive')
            ->andReturn(true);

        $this->assertTrue($timer->running());

        $timer->cancel();

        $this->assertFalse($timer->running());
    }
}
