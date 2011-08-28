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
 * Google APIs Visualization Library Class
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Chart
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
 
class Chart {

    protected static $instances = array();

    /**
     * A shortcode to initiate this class as a new object
     * 
     * @static
     * @access  public
     * @return  static 
     */
    public static function forge($name = null) 
    {
        if (\is_null($name))
        {
            $name = 'default';
        }

        $name   = \Str::lower($name);

        if (!isset(static::$instances[$name]))
        {
            $driver = '\\Hybrid\\Chart_' . ucfirst($name);
            
            if (class_exists($driver))
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

    public static function factory($name = null)
    {
        return static::forge();
    }
    
    public static function js() 
    {
        return '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
    }
}