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

Namespace Hybrid;

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Auth_Twitter_Controller
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

/**
 * Facebook Connect Controller
 *
 * @package  Hybrid
 * @extends  \Hybrid\Controller
 */
class Auth_Twitter_Controller extends Controller {
    
    /**
     * Setup connection to Twitter OAuth Library
     *
     * @access  public
     * @return  void
     */
    public function action_index()
    {
        $twitter = \Hybrid\Auth::instance('twitter')->execute();

        if (false === $twitter)
        {
            \Log::error('Communication with Twitter OAuth failed');
        }
    }

    public function action_login()
    {
        return $this->action_index();
    }

    /**
     * Logout from Twitter, normally for debugging purpose. Otherwise please use \Hybrid\Acl_User::logout();
     *
     * @access  public
     * @return  void
     */
    public function action_reset()
    {
        \Hybrid\Auth::instance('twitter')->logout();
    }

    /**
     * Get account detail, should only be accessible from DEVELOPMENT environment
     *
     * @access  public
     * @return  void
     */
    public function action_detail()
    {
        if (\Config::get('environment', \Fuel::DEVELOPMENT))
        {
            \Debug::dump(\Hybrid\Auth::instance('twitter')->get(), \Hybrid\Auth::instance('user')->get());
        }
        else
        {
            \Request::show_404();
        }
    }

}