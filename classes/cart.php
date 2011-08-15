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
        if (is_null($name) or !\is_string($name) or empty($name))
        {
            $name = \Config::get('app.identity', 'fuelapp');
        }

        if (\is_null(static::$instances[$name]))
        {
            static::$instances[$name] = new static($name);
        }

        return static::$instances[$name];
    }

    protected $name     = null;

    protected $contents = array(
        'data'      => array(),
        'total'     => 0.00,
        'quantity'  => 0,
    );

    protected function __construct($name)
    {
        $this->name     = $name;

        // get available cart content (if available)
        $contents = \Session::get('_cart_content_' . $name, null);

        if (\is_null($contents))
        {
            $contents['data']       = array();
            $contents['total']      = 0.00;
            $contents['quantity']   = 0;
        }

        $this->contents = $contents;
    }

    /**
     * Insert items into the cart and save it to the session
     *
     * @access  public
     * @param   array   $items
     */
    public function insert($items = array())
    {
        if (empty($items) or !\is_array($items))
        {
            throw new \Fuel_Exception("Item doesn't contain proper format, please provide an array");
            return false;
        }

        $save_cart  = false;

        if (isset($items['id']))
        {
            if (true === $this->_insert($items))
            {
                $save_cart = true;
            }
        }
        else 
        {
            foreach ($items as $item)
            {
                if (\is_array($item) and isset($item['id']))
                {
                    if (true === $this->_insert($item))
                    {
                        $save_cart = true;
                    }
                }
            }
        }

        if (true === $save_cart)
        {
            return $this->save();
        }

        return false;
    }

    /**
     * Insert to self::contents
     *
     * @access  protected
     * @param   array   $items
     * @return  bool
     * @throws  \Fuel_Exception
     */
    protected function _insert($items = array())
    {
        if (empty($items) or !\is_array($items))
        {
            throw new \Fuel_Exception("Item doesn't contain proper format, please provide an array");
            return false;
        }


    }

    public function update($items = array())
    {
        
    }

    protected function _update($item)
    {
        
    }

    /**
     * Save all cart
     *
     * @access  public
     * @return  bool
     */
    public function save()
    {
        
        \Session::set('_cart_content_' . $this->name, $this->contents);

        return true;
    }

    /**
     * Get cart content based on type
     *
     * @access  public
     * @param   string  $type   should be either "data", "quantity" or "total"
     * @return  array
     * @throws  \Fuel_Exception
     */
    public function get($type = null)
    {
        $type = trim(strtolower(strval($type)));

        if (\array_key_exists($type, $this->contents))
        {
            return $this->contents[$type];
        }
        else
        {
            throw new \Fuel_Exception("Trying to access undefined `{$type}`");
        }
    }

    /**
     * Remove all saved content/checkout
     * 
     * @access  public
     * @return  bool
     */
    public function destroy()
    {
        // clear all value and database
        $this->contents = array();
        \Session::set('_cart_content_' . $this->name, array());

        return true;
    }


 }