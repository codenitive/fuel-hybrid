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
 * @category    Acl_Facebook
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Acl_Facebook extends Acl_Abstract {
    
    /**
     * Facebook Adapter configuration
     *
     * @static
     * @access  protected
     * @var     array
     */
    protected static $config    = null;

    /**
     * Facebook Adapter object
     *
     * @static
     * @access  protected
     * @var     object
     */
    protected static $adapter  = null;
    
    /**
     * User data
     *
     * @static
     * @access  protected
     * @var     object|array
     */
    protected static $items     = array(
        'id'        => 0,
        'user_id'   => 0,
        'token'     => '',
        'info'      => null,
        'access'    => 0,
    );

    /**
     * Facebook User ID
     *
     * @static
     * @access  protected
     * @var     int
     */
    protected static $user      = null;

    /**
     * Initiate a connection to Facebook SDK Class with config
     *
     * @static
     * @access  public
     * @return  void
     */
    public static function _init() 
    {
        parent::_init();

        if (\is_null(static::$adapter)) 
        {
            static::$config = \Config::get('app.api.facebook');
            
            $config         = array(
                'appId'     => static::$config['app_id'],
                'secret'    => static::$config['secret'],
            );

            if (false === \Fuel::$is_cli)
            {
                import('facebook/facebook', 'vendor');
                static::$adapter = new \Facebook($config);
            }
        }
        
        static::factory();
    }

    /**
     * Get cookie contain
     *
     * @static
     * @access  protected
     * @return  void
     */
    protected static function factory()
    {
        $oauth              = \Cookie::get('_facebook_oauth');

        if (!\is_null($oauth))
        {
            $oauth          = \unserialize(\Crypt::decode($oauth));
            static::$items  = (array) $oauth;
        }
    }

    /**
     * return Facebook Object
     *
     * @static
     * @access  public
     * @return  object
     */
    public static function get_adapter() 
    {
        return static::$adapter;
    }

    /**
     * Authenticate user with Facebook Account
     * There are three process/stage of authenticating an account:
     * 2. authenticate the user with Facebook account
     * 3. verifying the user account
     *
     * @static
     * @access  public
     * @return  bool
     */
    public static function execute()
    {
        $status = false;

        switch (\intval(static::$items['access']))
        {
            case 0 :
                $status = static::access_token();
            break;

            case 1 :
                /* fetch data from database to insert or update */
                $status = static::verify_token();
            break;

            case 2 :
            default :
                /* Do nothing for now */
                $status = true;
            break;
        }

        return $status;
    }

    /**
     * Get Facebook Connect Login/logout URL
     *
     * @static
     * @access  public
     * @param   array   $option
     * @return  void
     */
    public static function get_url($option = array())
    {
        if (true === \Fuel::$is_cli)
        {
            return ;
        }
        
        // we already have static::$config but we need to check if properties doesn't exist
        $redirect_uri   = \Config::get('app.api.facebook.redirect_uri');
        $scope          = \Config::get('app.api.facebook.scope', '');

        $config         = array('scope' => $scope);

        if (!\is_null($redirect_uri))
        {
            $config['redirect_uri'] = \Uri::create($redirect_uri);
        }

        $config         = \array_merge($config, $option);

        switch (static::$items['access'])
        {
            case 1 :
            case 2 :
                unset($config['scope']);
                return static::$adapter->getLogoutUrl($config);
            break;

            case 0 :
            default :
                return static::$adapter->getLoginUrl($config);
            break;
        }
    }

    /**
     * Stage 2: verifying the user account
     *
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function verify_token() 
    {
        static::$items['access'] = 2;

        $result = \DB::select('users_facebooks.*', array('users.user_name', 'username'))
                    ->from('users_facebooks')
                    ->join('users', 'LEFT')
                    ->on('users_facebooks.user_id', '=', 'users.id')
                    ->where('users_facebooks.facebook_id', '=', static::$items['id'])
                    ->execute();

        if ($result->count() < 1) 
        {
            static::add_handler();
            static::register();

            if (\intval(static::$items['user_id']) < 1) 
            {
                static::redirect('registration');
            }
            else 
            {
                static::redirect('after_login');
            }
            
            return true;
        } 
        else 
        {
            $row = $result->current();

            static::$items['user_id'] = $row['user_id'];
            static::update_handler();
            static::register();

            if (\is_null($row['user_id']) or \intval(static::$items['user_id']) < 1) 
            {
                static::redirect('registration');

                return true;
            }

            \Hybrid\Acl_User::login($row['username'], static::$items['token'], 'facebook_oauth');
            static::redirect('after_login');

            return true;
        }

        return false;
    }

    /**
     * Stage 1: authenticate the user with twitter account
     *
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function access_token() 
    {
        static::$user = static::$adapter->getUser();

        if (static::$user <> 0 and !\is_null(static::$user) and 0 < intval(static::$items['id']))
        {
            return static::verify_access();
        }
            
        try
        {
            static::$items['access']        = (static::$items['access'] == 0 ? 1 : static::$items['access']);
            $profile_data                   = static::$adapter->api('/me');    
        } 
        catch (\FacebookApiException $e)
        {
            \Log::error('\\Hybrid\\Acl_Facebook::access_token request fail: ' . $e->getMessage());
            static::$user                   = null;
            static::$items['access']        = 0;
        }
        
        $scopes                             = explode(',', \Config::get('app.api.facebook.scope', ''));

        static::$items['info']              = new \stdClass();
        $profile_data                       = (object) $profile_data;
        
        static::$items['id']                = $profile_data->id;
        static::$items['info']->username    = $profile_data->username;
        static::$items['info']->first_name  = $profile_data->first_name;
        static::$items['info']->last_name   = $profile_data->last_name;
        static::$items['info']->link        = $profile_data->link;

        foreach ($scopes as $scope)
        {
            $scope = trim($scope);

            if (!empty($scope))
            {
                static::$items['info']->{$scope} = $profile_data->{$scope};
            }
        }

        static::$items['token']             = static::$adapter->getAccessToken();

        if (static::$items['access'] == 0)
        {
            static::$items['access']        = 1;
        }

        return static::verify_token();
    }

    /**
     * Add Facebook Handler to database
     *
     * @static
     * @access  private
     * @return  bool
     */
    private static function add_handler() 
    {
        $id = static::$items['id'];

        if (!\is_numeric($id)) 
        {
            return false;
        }

        if (empty(static::$items['info'])) 
        {
            return false;
        }

        $bind = array(
            'facebook_id'   => $id,
            'token'         => static::$items['token']
        );

        if (\Hybrid\Acl_User::is_logged())
        {
            $bind['user_id'] = \Hybrid\Acl_User::get('id');
            static::$items['user_id'] = $bind['user_id'];
        }

        \DB::insert('users_facebooks')
            ->set($bind)
            ->execute();

        \DB::insert('facebooks')
            ->set(array(
                'id'            => $id,
                'facebook_name' => static::$items['info']->username,
                'first_name'    => static::$items['info']->first_name,
                'last_name'     => static::$items['info']->last_name,
                'facebook_url'  => static::$items['info']->link
            ))
            ->execute();

        return true;
    }

    /**
     * Update Facebook Handler to database
     *
     * @static
     * @access  private
     * @return  bool
     */
    private static function update_handler() 
    {
        $id = static::$items['id'];

        if (!\is_numeric($id)) 
        {
            return false;
        }

        if (empty(static::$items['info'])) 
        {
            return false;
        }

        $bind = array(
            'token' => static::$items['token']
        );

        if (\Hybrid\Acl_User::is_logged() and 0 === \intval(static::$items['user_id']))
        {
            $bind['user_id'] = \Hybrid\Acl_User::get('id');
            static::$items['user_id'] = $bind['user_id'];
        }

        \DB::update('users_facebooks')
            ->set($bind)
            ->where('facebook_id', '=', $id)
            ->execute();

        \DB::update('facebooks')
            ->set(array(
                'facebook_name' => static::$items['info']->username,
                'first_name'    => static::$items['info']->first_name,
                'last_name'     => static::$items['info']->last_name,
                'facebook_url'  => static::$items['info']->link
            ))
            ->where('id', '=', $id)
            ->execute();

        return true;
    }

    /**
     * Register information to Session
     *
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function register() 
    {
        \Cookie::set('_facebook_oauth', \Crypt::encode(\serialize((object) static::$items)));

        return true;
    }

    /**
     * Unregister information from Session
     *
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function unregister() 
    {
        \Cookie::delete('_facebook_oauth');

        return true;
    }

    /**
     * Initiate user login out from Facebook
     *
     * Usage:
     * 
     * <code>\Hybrid\Acl_Facebook::logout(false);</code>
     * 
     * @static
     * @access  public
     * @param   bool    $redirect
     * @return  bool
     */
    public static function logout($redirect = true)
    {
        $url = static::get_url(array('redirect_uri' => \Uri::create(static::redirect('after_logout'))));
        static::unregister();

        if (true === $redirect)
        {
            \Response::redirect($url, 'refresh');
        }
        
    }
}