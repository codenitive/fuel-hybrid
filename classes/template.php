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
 * @category    Template
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Template {

    const DEFAULT_TEMPLATE = 'normal';

    protected static $instances = array();

    public static function _init()
    {
        \Config::load('app', true);
    }

    public static function factory($name = null)
    {
        if (is_null($name))
        {
            $name = \Config::get('app.template.default', self::DEFAULT_TEMPLATE);   
        }

        $folder = null;
        $filename = null;
        $type = explode('.', strval($name));

        if (count($type) > 1) 
        {
            // set filename if available
            if (isset($type[2]))
            {
                $filename = $type[2];
            }

            // folder should be available if type count > 1
            $folder = $type[1];
        }
        
        $type = $type[0];
        $name = $type . '.' . $folder;

        $driver = '\\Hybrid\\Template_'.ucfirst($type);

        if (isset(static::$instances[$name]))
        {
            return static::$instances[$name];
        }
        elseif (class_exists($driver)) 
        {
            static::$instances[$name] = new $driver($folder, $filename);
            return static::$instances[$name];
        }
        else 
        {
            throw new \Fuel_Exception("Requested {$driver} does not exist");
        }
    }

}