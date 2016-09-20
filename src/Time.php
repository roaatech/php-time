<?php
/**
 * Created by PhpStorm.
 * User: Muhannad Shelleh
 * Date: 9/20/2016
 * Time: 12:26 PM
 */

namespace ItvisionSy\Time;

/**
 * Class Time
 *
 * A class to manage time operations and values. It represents a counter of a time units in positive or negative.
 *
 * @package ItvisionSy\Time
 * @property integer hours
 * @property integer minutes
 * @property integer seconds
 * @property integer millis
 * @method Time|integer hours($set=null)
 * @method Time|integer minutes($set=null)
 * @method Time|integer seconds($set=null)
 * @method Time|integer millis($set=null)
 */
class Time
{

    const UNIT_HOURS = 0;
    const UNIT_MINUTES = 1;
    const UNIT_SECONDS = 2;
    const UNIT_MILLIS = 3;
    protected static $multipliers = [
        60 * 60 * 1000,
        60 * 1000,
        1000,
        1
    ];
    protected static $units = [
        'hours',
        'minutes',
        'seconds',
        'millis'
    ];

    protected $time = 0;

    /**
     * Time constructor.
     * @param null $time
     */
    public function __construct($time = null)
    {
        if (!$time) {
            return;
        } elseif (is_string($time)) {
            $this->parseString($time);
        } elseif (is_int($time)) {
            $this->seconds($time);
        } elseif (is_float($time)) {
            $this->seconds((int)$time);
            $this->millis((int) explode(".", "{$time}")[1]);
        }
    }

    /**
     * @param $timeString
     * @return Time
     */
    public static function makeFromString($timeString)
    {
        return new static($timeString);
    }

    /**
     * @param $timestamp
     * @return Time
     */
    public static function makeFromTimestamp($timestamp)
    {
        return (new static())->seconds($timestamp);
    }

    /**
     * @param $timeString
     * @return Time|$this
     */
    public function parseString($timeString)
    {
        $result = preg_match("#^(\d+)\:(\d{1,2})\:(\d{1,2})\.(\d+)$#", $timeString, $matches);
        if (!$result) {
            return;
        }
        $this->hours($matches[1]);
        $this->minutes($matches[2]);
        $this->seconds($matches[3]);
        $this->millis($matches[4]);
        return $this;
    }

    function __get($name)
    {
        switch ($name) {
            case 'hours':
            case 'minutes':
            case 'seconds':
            case 'millis':
                return $this->get($name);
                break;
            default:
                trigger_error("Property not defined: {$name}");
        }
    }

    function __set($name, $value)
    {
        switch ($name) {
            case 'hours':
            case 'minutes':
            case 'seconds':
            case 'millis':
                return $this->set($name, $value);
                break;
            default:
                trigger_error("Property not defined: {$name}");
        }
    }


    function __call($name, $args)
    {
        switch ($name) {
            case 'hours':
            case 'minutes':
            case 'seconds':
            case 'millis':
                return count($args) == 1 ? $this->set($name, $args[0]) : $this->get($name);
                break;
            default:
                trigger_error("Method not defined: {$name}");
        }
    }

    /**
     * @param $unit
     * @return float
     */
    public function get($unit)
    {
        $unitKey = array_search($unit, static::$units);
        if ($unitKey === false) {
            trigger_error("Unit not defined: {$unit}");
        }
        $unitMultiplier = static::$multipliers[$unitKey];
        $unitReminder = $unitKey == 0 ? 0 : static::$multipliers[$unitKey - 1];
        $value = floor(($unitReminder ? $this->time % $unitReminder : $this->time) / $unitMultiplier);
        return $value;
    }

    /**
     * @param string $unit
     * @param string|integer $value
     * @return Time|$this
     */
    public function set($unit, $value)
    {
        $unitKey = array_search($unit, static::$units);
        if ($unitKey === false) {
            trigger_error("Unit not defined: {$unit}");
        }
        $oldValue = $this->get($unit);
        $unitMultiplier = static::$multipliers[$unitKey];
        $newValue = gettype($value) === 'string' ? $this->parseStringValue($value, $oldValue) : (int)$value;
        $this->time = $this->time + ($newValue - $oldValue) * $unitMultiplier;
        return $this;
    }

    /**
     * @param string $value
     * @param $oldValue
     * @return int
     */
    protected function parseStringValue($value, $oldValue)
    {
        if (array_search(substr("{$value}", 0, 1), ["+", "-"]) !== false) {
            $delta = (int)$value;
            return $oldValue + $delta;
        } else {
            return (int)$value;
        }
    }

    /**
     * @param bool $leadingZeros
     * @return string
     */
    public function format($leadingZeros = false)
    {
        $sign = $this->hours < 0 ? "-" : "";
        $hours = $this->hours;
        $minutes = $leadingZeros ? str_pad($this->minutes, 2, "0", STR_PAD_LEFT) : $this->minutes;
        $seconds = $leadingZeros ? str_pad($this->seconds, 2, "0", STR_PAD_LEFT) : $this->seconds;
        $millis = $this->millis;
        return $sign . str_replace("-", "", "{$hours}:{$minutes}:{$seconds}.{$millis}");
    }

    function __toString()
    {
        return $this->format();
    }

    /**
     * @param string $unit
     * @return Time|$this
     */
    public function tick($unit = "seconds")
    {
        return $this->set($unit, "+1");
    }

}