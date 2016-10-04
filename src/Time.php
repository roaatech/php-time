<?php
/**
 * Created by PhpStorm.
 * User: Muhannad Shelleh
 * Date: 9/20/2016
 * Time: 12:26 PM
 */

namespace ItvisionSy\Time;

use ErrorException;

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
 * @property integer timestamp
 * @property float inHours
 * @property float inMinutes
 * @property float inSeconds
 * @property float inMillis
 * @method Time|integer hours(integer|string $set = null)
 * @method Time|integer minutes(integer|string $set = null)
 * @method Time|integer seconds(integer|string $set = null)
 * @method Time|integer millis(integer|string $set = null)
 */
class Time
{

    protected static $config = [
        'full_format' => "H:M:S.C",
        'format' => "h:m:s.c",
    ];

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
            $this->millis(@(int)explode(".", "{$time}")[1] ?: 0);
        }
    }

    /**
     * @param $timeString
     * @return Time
     */
    public static function makeFromString($timeString)
    {
        return (new static())->parseString($timeString);
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
     * @param string|integer $time
     * @return Time
     */
    public static function make($time = null)
    {
        return new static($time);
    }

    /**
     * @param $timeString
     * @return Time|$this
     */
    public function parseString($timeString)
    {
        $result = preg_match("#^(\d+)(\:(\d+)(\:(\d+)(\.(\d+))?)?)?$#", $timeString, $matches);
        if ($result) {
            $this->hours($matches[1]);
            $this->minutes(@$matches[3] ?: "0");
            $this->seconds(@$matches[5] ?: "0");
            $this->millis(substr(@$matches[7] ?: "0", 0, 3));
        }
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
            case 'inHours':
            case 'inMinutes':
            case 'inSeconds':
            case 'inMillis':
                return $this->represent(substr(strtolower($name), 2));
                break;
            case 'timestamp':
                return $this->time;
                break;
            default:
                trigger_error("Property not defined: {$name}");
        }
        return null;
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
                throw new ErrorException("Property not defined: {$name}");
        }
        return $this;
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
                throw new ErrorException("Method not defined: {$name}");
        }
        return null;
    }

    /**
     * @param $unit
     * @return float
     */
    public function get($unit)
    {
        $unitKey = $this->getUnitKey($unit);
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
        $unitKey = $this->getUnitKey($unit);
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
    public function toTimeString($leadingZeros = false)
    {
        $sign = $this->hours < 0 ? "-" : "";
        $hours = $this->hours;
        $minutes = $leadingZeros ? str_pad($this->minutes, 2, "0", STR_PAD_LEFT) : $this->minutes;
        $seconds = $leadingZeros ? str_pad($this->seconds, 2, "0", STR_PAD_LEFT) : $this->seconds;
        $millis = $leadingZeros ? str_pad($this->millis, 3, "0", STR_PAD_LEFT) : $this->millis;
        return $sign . str_replace("-", "", "{$hours}:{$minutes}:{$seconds}.{$millis}");
    }

    /**
     * @param string $format c=milli, C=leading milli, h=hours, H=leading hours, m=minutes, M=leading minutes, s=seconds, S=leading seconds
     * @return string
     */
    public function format($format = null)
    {
        if ($format === true) {
            $format = static::$config['full_format'];//"H:M:S.C";
        } elseif ($format === null) {
            $format = static::$config['format'];//"h:m:s.c"
        }
        $result = preg_replace_callback("#(\\\\)?([HhMmSsCc])#", function ($matches) {
            if ($matches[1] === "\\") {
                return $matches[0];
            }
            switch ($matches[2]) {
                case 'S':
                    return str_pad($this->seconds, 2, '0', STR_PAD_LEFT);
                    break;
                case 's':
                    return $this->seconds;
                    break;
                case 'M':
                    return str_pad($this->minutes, 2, '0', STR_PAD_LEFT);
                    break;
                case 'm':
                    return $this->minutes;
                    break;
                case 'H':
                    return str_pad($this->hours, 2, '0', STR_PAD_LEFT);
                    break;
                case 'h':
                    return $this->hours;
                    break;
                case 'C':
                    return str_pad($this->millis, 3, '0', STR_PAD_LEFT);
                    break;
                case 'c':
                    return $this->millis;
                    break;
            }
        }, $format);
        return $result;
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

    /**
     * Creates a new instance with the current time value
     * @return Time
     */
    public function copy()
    {
        return new static($this->format());
    }

    /**
     * @return int
     */
    public function timestamp()
    {
        return $this->time;
    }

    /**
     * @param Time $compareTo
     * @return Time
     */
    public function diff(Time $compareTo)
    {
        return new static(($this->timestamp() - $compareTo->timestamp) / 1000);
    }

    /**
     * @param string $unit
     * @return float|int
     */
    public function represent($unit)
    {
        $unitKey = $this->getUnitKey($unit);
        $unitMultiplier = static::$multipliers[$unitKey];
        return round($this->time / $unitMultiplier, 3);
    }

    /**
     * @return bool
     */
    public function isMinus()
    {
        return $this->hours < 0;
    }

    /**
     * @return bool
     */
    public function isZero()
    {
        return $this->timestamp === 0;
    }

    /**
     * @param Time $compareTo
     * @return bool
     */
    public function isBefore(Time $compareTo)
    {
        return $this->diff($compareTo)->isMinus();
    }

    /**
     * @param Time $compareTo
     * @return bool
     */
    public function isAfter(Time $compareTo)
    {
        return !$this->diff($compareTo)->isZero() && !$this->isBefore($compareTo);
    }

    /**
     * @param Time $compareTo
     * @return bool
     */
    public function isEqual(Time $compareTo)
    {
        return $this->timestamp === $compareTo->timestamp;
    }

    /**
     * @param $unit
     * @return mixed
     * @throws ErrorException
     */
    protected function getUnitKey($unit)
    {
        $unitKey = array_search($unit, static::$units);
        if ($unitKey === false) {
            throw new ErrorException("Unit not defined: {$unit}");
        }
        return $unitKey;
    }

    /**
     * @param $format
     * @param bool $full
     */
    public static function setDefaultFormat($format, $full = false)
    {
        static::$config[($full ? "full_" : "") . "format"] = $format;
    }

    public function add(Time $time)
    {
        return $this->millis('+' . $time->timestamp);
    }

    public function sub(Time $time)
    {
        return $this->millis('-' . $time->timestamp);
    }

}