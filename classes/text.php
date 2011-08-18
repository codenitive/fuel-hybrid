<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Hybrid;

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Text
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
class Text {
    
    protected static $instances = array();

    public static function factory($name = null)
    {
        if (\is_null($name))
        {
            $name = '';
        }

        $driver = '\\Hybrid\\Text_' . \Str::ucfirst($name);

        if (!isset(static::$instances[$name]))
        {
            // instance has yet to be initiated
            if (\class_exists($driver))
            {
                static::$instances[$name] = new $driver();
            }
            else
            {
                throw new \Fuel_Exception("Requested {$driver} does not exist");
            }
        }

        return static::$instances[$name];
    }

}