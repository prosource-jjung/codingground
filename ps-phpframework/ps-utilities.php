<?php
/*
    ps-utilities.php <Utilities helper for ProSource Framework>
    
    Copyright (C) 2015  ProSource Web Team: Jean Jung - Team leader.
                            

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once('ps-consts.php');

/**
  * Output something on the page with a ECHO command, just if DEBUG mode is on on ps-consts.php.
  */
function PS_EchoDebug($where, $what)
{
        if (DEBUG) 
        {
                echo "'$where' -> '$what'<br/>";
        }
}
  

/**
  * A interface to describe Parsers behaviors.
  */
interface PS_Parser
{
        /**
          * Parse the given data in desired value.
          */
	public static function parse($src);
} 

/**
  * Decribe an innitializable resources handler.
  */ 
interface PS_ResourceHandler
{
        /**
          * Initialize resources with given data.
          */
        public function initialize();
        
        /**
          * Close, free or finalize resources handled by this class.
          */
        public function finalize();
}

/**
  * Implements Enum base class. Common functional methods must be created here.
  *
  * This code was obtained from http://stackoverflow.com/questions/254514/php-and-enumerations
  */ 
abstract class Enum {
    private static $constCacheArray = NULL;

    private static function getConstants() {
        if (self::$constCacheArray == NULL) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function isValidName($name, $strict = false) {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value) {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict = true);
    }
}
  
?>