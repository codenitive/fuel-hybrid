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
 * @category    Cart
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

 class Cart {

    protected static $instances = array();

    public static function _init()
    {
        \Config::load('app', true);
        \Config::load('crypt', true);
    }

    public static function factory($name = null)
    {
        // set instance name to default if null given
        if (is_null($name) or !is_string($name) or empty($name))
        {
            $name = \Config::get('app.identity', 'fuelapp');
        }

        if (is_null(static::$instances[$name]))
        {
            static::$instances[$name] = new static();
        }

        return static::$instances[$name];
    }

    protected $cart_contents = array();

    protected function __construct($config = null)
    {
        $initconfig = \Config::load('app.cart', array());
        $config     = array_merge($config, $initconfig);
        

        // get available cart content (if available)
        $this->cart_content = \Session::get('_cart_content', array());
    }

    public function insert($items = array())
    {
        
    }

    public function update()
    {
        
    }

    public function checkout()
    {
        
    }

    /**
     * Remove all saved content/checkout
     * 
     * @access  public
     * @return  bool
     */
    public function destroy()
    {
        $this->cart_contents = array();
        \Session::set('_cart_content', array());

        return true;
    }


 }