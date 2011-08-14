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
 * @category    Acl_Controller_Facebook
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

/**
 * Facebook Connect Controller
 *
 * @package  Hybrid
 * @extends  \Hybrid\Controller
 */
class Acl_Controller_Facebook extends \Hybrid\Controller {
    
    /**
     * Setup connection to Facebook API Library
     *
     * @access  public
     * @return  void
     */
    public function action_index()
    {
        $facebook = \Hybrid\Acl_Facebook::execute();
        $login = \Hybrid\Acl_Facebook::get_url();

        if (null === \Hybrid\Input::get('code', null))
        {
            \Response::redirect($login, 'refresh');
        }
    }

    /**
     * Login to Facebook API
     * 
     * @access  public
     * @return  void
     */
    public function action_login()
    {
        $login = \Hybrid\Acl_Facebook::get_url();

        if (false === \Hybrid\Acl_Facebook::is_logged() and null === \Hybrid\Input::get('code', null))
        {
            \Response::redirect($login, 'refresh');
        }
        else
        {
            return $this->action_index();
        }
    }

    /**
     * Logout from Facebook, normally for debugging purpose. Otherwise please use \Hybrid\Acl_User::logout();
     *
     * @access  public
     * @return  void
     */
    public function action_reset()
    {
        \Hybrid\Acl_Facebook::logout();
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
            \Debug::dump(\Hybrid\Acl_Facebook::get(), \Hybrid\Acl_User::get());
        }
        else
        {
            throw new \Request404Exception();
        }
    }

}