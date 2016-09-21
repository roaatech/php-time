<?php

namespace ItvisionSy\Time\Tests;

use ErrorException;
use ItvisionSy\Time\Time;
use PHPUnit_Framework_TestCase;

class TimeTest extends PHPUnit_Framework_TestCase
{

    public function testAttributes(){
        $time = Time::make('1:30:30.500');
        $this->assertEquals(1.508, $time->inHours);
        $this->assertEquals(90.508, $time->inMinutes);
        $this->assertEquals(5430.5, $time->inSeconds);
        $this->assertEquals($time->timestamp, $time->inMillis);
    }

    public function testRepresenter()
    {
        $this->assertEquals(2.5, Time::make('0:150')->inHours);
        $this->assertEquals(2, Time::make('0:0:120')->inMinutes);
    }

    public function testDiff()
    {
        $this->assertEquals(2, Time::make('4')->diff(Time::make('2'))->inHours);
    }

    public function testZeroMinus()
    {
        $this->assertTrue(Time::make()->hours('-3')->isMinus());
        $this->assertTrue(Time::make()->isZero());
    }

    public function testAfterBefore()
    {
        $this->assertTrue(Time::make(4)->isAfter(Time::make(2)));
        $this->assertTrue(Time::make(2)->isBefore(Time::make(4)));
        $this->assertTrue(Time::make(2)->isEqual(Time::make(2)));
    }

    public function testParse()
    {
        $this->assertEquals('1:1:1.0', Time::make()->parseString('1:1:1'));
    }

    public function testAssign()
    {
        $time = Time::make();
        $this->assertEquals(1, $time->hours(1)->hours);
        $time->hours = 2;
        $this->assertEquals(2, $time->hours);
        $time->hours = '-2';
        $this->assertEquals(0, $time->hours);
        $this->assertEquals('1:1:1.0', $time->tick()->tick('minutes')->tick('hours')->format());
    }

    public function testOverflow()
    {
        $time = Time::make();
        $this->assertEquals(4.5, $time->hours(1)->minutes(120)->seconds(5400)->inHours);
    }

    public function testFactories()
    {
        $this->assertEquals(2.5, Time::makeFromString('2:30')->inHours);
        $this->assertEquals(2.5, Time::makeFromTimestamp(30 + 60 * 2)->inMinutes);
    }

    /**
     * @expectedException ErrorException
     */
    public function testErrors()
    {
        $this->assertNull(@Time::make(3600000)->micros);
        $this->assertNull(@Time::make(3600000)->micros());
        Time::make()->micros = 50;
    }

    public function testCopy()
    {
        $time = Time::make('2');
        $this->assertTrue($time->isBefore($time->copy()->tick()));
    }

}
