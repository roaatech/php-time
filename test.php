<?php
/**
 * Created by PhpStorm.
 * User: mhh14
 * Date: 9/20/2016
 * Time: 12:39 PM
 */

require_once 'vendor/autoload.php';

$x = new \ItvisionSy\Time\Time("1:2:3.4");
echo $x . "\n";
echo $x->tick() . "\n";
echo $x->tick() . "\n";
echo $x->tick() . "\n";
echo $x->tick() . "\n";
echo $x->tick("hours") . "\n";
echo $x->tick("minutes") . "\n";
echo $x->tick("millis") . "\n";
echo $x->hours("+5") . "\n";
echo $x->hours("-5") . "\n";
echo $x->minutes("+20") . "\n";
echo $x->minutes("+120") . "\n";
echo $x->seconds("-36000") . "\n";
