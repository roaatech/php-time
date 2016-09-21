# php-time

A PHP class to easily create and maintain time values. You can create the object from a string like "1:22:13.212" or timestamps and it will automatically parse and fetch values.

You can then update the values, or convert it into strings or timestamps.

## Creating the object
 You can create the object from empty value, or from a string, or a timestamp. i.e.
 ```php
 $time1 = new \ItvisionSy\Time\Time(); //Time is set to "0:0:0.000"
 $time2 = new \ItvisionSy\Time\Time("1:22:18.212");
 $time3 = new \ItvisionSy\Time\Time(36232518212);
 ```
 Also you can use the `make($time)` factory method:
 ```php
 $time1 = \ItvisionSy\Time\Time::make();
 $time2 = \ItvisionSy\Time\Time::make("12:46:36.897");
 $time3 = \ItvisionSy\Time\Time::make(3600000);
 ```
 
 ### Time string
 Time string is a string with 4 parts as follows:
 `h[:i[:s[.m]]]`. I.e: 
 ```php
 \ItvisionSy\Time\Time::make('3')->format(); //3:0:0.0 
 \ItvisionSy\Time\Time::make('3:30')->format(); //3:30:0.0 
 \ItvisionSy\Time\Time::make('3:30:22')->format(); //3:30:22.0 
 \ItvisionSy\Time\Time::make('3:30:22.123')->format(); //3:30:22.123 
 ```
 If milliseconds digits are more than 3, only first 3 will be used.
 Also, you can use irregular (overflown) values and it will be automatically converted into the next unit. i.e.
 ```php
 \ItvisionSy\Time\Time::make('3:90')->format(); //4:30:0.0 
 \ItvisionSy\Time\Time::make('0:180')->format(); //3:0:0.0 
 ```
 
 ## Setting values
 You can use the methods: `hours($value)`, `minutes($value)`, `seconds($value)`, or `millis($value)` to update the value. The `$value` parameter can be either an integer which will replace the old value, or a string preceded with "+" or "-" to alter the current value. i.e.
 ```php
 $time = \ItvisionSy\Time\Time::make()->hours(4); //sets the hours to 4
 $time->hourse("+3"); //sets the hours to 7 (4+3)
 $time->hours(0)->minutes(128)->seconds(61)->format(); //returns 2:9:1.000
 ```
 You can also use the direct assignment which will call the previous methods. i.e.
 ```php
 $time = \ItvisionSy\Time\Time::make("1:2:3.4");
 $time->hours = 33;
 $time->seconds = "-22";
 ```
 Or you can use the parser method
 ```php
 $time->parseString("22:11:12.123");
 ```
 
 ## Getting values
 You can either use the methods or the properties
 ```php
 $time->hours; // === $time->hours()
 $time->minutes; // === $time->minutes()
 $time->seconds; // === $time->seconds()
 $time->millis; // === $time->millis()
 ```
 
 ## Utilities
 ### Formatting
 `format(boolean $leadingZeros = false)` 
 ```php
 $time = \ItvisionSy\Time\Time::make("6:3:7.23");
 $time->minutes("+123");
 $time->format(); //"8:6:7.23"
 $time->format(true); //"8:06:07.023"
 ```
### Representing
 You can represent the timestamp in a unit like hours or minutes. 
 ```php
 $time = \ItvisionSy\Time\Time::make("2:30:00.000");
 ```
### Comparing
####Diff function
```php
use \ItvisionSy\Time\Time; 
Time::make('3')->diff(Time::make(2))->inHours; //3:0:0.0   
```