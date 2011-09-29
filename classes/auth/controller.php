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
 * Authentication Class
 * 
 * Why another class? FuelPHP does have it's own Auth package but what Hybrid does 
 * it not defining how you structure your database but instead try to be as generic 
 * as possible so that we can support the most basic structure available
 * 
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Auth_Controller
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth_Controller extends \Controller {
    
    public function before()
    {
        parent::before();

        // Load the configuration for this provider
        \Config::load('autho', 'autho');
    }

    public function action_session($provider)
    {
        Strategy::factory($provider)->authenticate();
    }

    public function action_callback($provider)
    {
        $strategy = Strategy::factory($provider);
        
        Strategy::login_or_register($strategy);
    }

}