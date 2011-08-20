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
 * @category    Auth_Facebook
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth_Facebook extends Auth_Driver {
    
    /**
     * Facebook Adapter configuration
     *
     * @access  protected
     * @var     array
     */
    protected $config   = null;

    /**
     * Auth data
     *
     * @access  protected
     * @var     object|array
     */
    protected $auth     = array(
        'id'        => 0,
        'user_id'   => 0,
        'token'     => '',
        'info'      => null,
        'access'    => 0,
    );

    /**
     * Facebook User ID
     *
     * @access  protected
     * @var     int
     */
    protected $user     = null;

    /**
     * Get self instance from cache instead of initiating a new object if time 
     * we need to use this object
     *
     * @static
     * @access  public
     * @return  self
     */
    public static function instance()
    {
        return \Hybrid\Auth::instance('facebook');
    }

    /**
     * Initiate a connection to Facebook SDK Class with config
     *
     * @access  public
     * @return  void
     */
    public function __construct() 
    {
        parent::_initiate();

        if (\is_null($this->adapter)) 
        {
            $this->config   = \Config::get('app.api.facebook');
            
            $config         = array(
                'appId'     => $this->config['app_id'],
                'secret'    => $this->config['secret'],
            );

            if (false === \Fuel::$is_cli)
            {
                import('facebook/facebook', 'vendor');
                $this->adapter = new \Facebook($config);
            }
        }
        
        $cookie             = \Cookie::get('_facebook_oauth');

        if (!\is_null($cookie))
        {
            $cookie         = \unserialize(\Crypt::decode($cookie));
            $this->auth     = (array) $cookie;
        }
    }

    /**
     * Authenticate user with Facebook Account
     * There are three process/stage of authenticating an account:
     * 2. authenticate the user with Facebook account
     * 3. verifying the user account
     *
     * @access  public
     * @return  bool
     */
    public function execute()
    {
        $status = false;

        switch (\intval($this->auth['access']))
        {
            case 0 :
                $status = $this->access_token();
            break;

            case 1 :
                /* fetch data from database to insert or update */
                $status = $this->verify_token();
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
     * @access  public
     * @param   array   $option
     * @return  void
     */
    public function get_url($option = array())
    {
        if (true === \Fuel::$is_cli)
        {
            return ;
        }
        
        // we already have $this->config but we need to check if properties doesn't exist
        $redirect_uri   = \Config::get('app.api.facebook.redirect_uri', '');
        $scope          = \Config::get('app.api.facebook.scope', '');

        $config         = array('scope' => $scope);

        if (!\is_null($redirect_uri))
        {
            $config['redirect_uri'] = \Uri::create($redirect_uri);
        }

        $config         = \array_merge($config, $option);

        switch ($this->auth['access'])
        {
            case 1 :
            case 2 :
                unset($config['scope']);
                return $this->adapter->getLogoutUrl($config);
            break;

            case 0 :
            default :
                return $this->adapter->getLoginUrl($config);
            break;
        }
    }

    /**
     * Stage 2: verifying the user account
     *
     * @access  protected
     * @return  bool
     */
    protected function verify_token() 
    {
        $this->auth['access'] = 2;

        $result = \DB::select('users_facebooks.*', array('users.user_name', 'username'))
                    ->from('users_facebooks')
                    ->join('users', 'LEFT')
                    ->on('users_facebooks.user_id', '=', 'users.id')
                    ->where('users_facebooks.facebook_id', '=', $this->auth['id'])
                    ->execute();

        if ($result->count() < 1) 
        {
            $this->add_handler();
            $this->register();

            if (\intval($this->auth['user_id']) < 1) 
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

            $this->auth['user_id'] = $row['user_id'];

            $this->update_handler();
            $this->register();

            if (\is_null($row['user_id']) or \intval($this->auth['user_id']) < 1) 
            {
                static::redirect('registration');

                return true;
            }

            $this->login($row['username'], $this->auth['token']);

            static::redirect('after_login');

            return true;
        }

        return false;
    }

    /**
     * Stage 1: authenticate the user with twitter account
     *
     * @access  protected
     * @return  bool
     */
    protected function access_token() 
    {
        $this->user                     = $this->adapter->getUser();

        if ($this->user <> 0 and !\is_null($this->user) and 0 < intval($this->auth['id']))
        {
            return $this->verify_access();
        }
            
        try
        {
            $this->auth['access']       = ($this->auth['access'] == 0 ? 1 : $this->auth['access']);
            $profile_data               = $this->adapter->api('/me');    
        } 
        catch (\FacebookApiException $e)
        {
            \Log::error('\\Hybrid\\Auth_Facebook::access_token request fail: ' . $e->getMessage());
            $this->user                 = null;
            $this->auth['access']       = 0;
        }
        
        $scopes                         = explode(',', \Config::get('app.api.facebook.scope', ''));

        $this->auth['info']             = new \stdClass();
        $profile_data                   = (object) $profile_data;
        
        $this->auth['id']               = $profile_data->id;
        $this->auth['info']->username   = $profile_data->username;
        $this->auth['info']->first_name = $profile_data->first_name;
        $this->auth['info']->last_name  = $profile_data->last_name;
        $this->auth['info']->link       = $profile_data->link;

        foreach ($scopes as $scope)
        {
            $scope = trim($scope);

            if (!empty($scope))
            {
                $this->auth['info']->{$scope} = $profile_data->{$scope};
            }
        }

        $this->auth['token']       = $this->adapter->getAccessToken();

        if ($this->auth['access'] == 0)
        {
            $this->auth['access']  = 1;
        }

        return $this->verify_token();
    }

    /**
     * Add Facebook Handler to database
     *
     * @access  private
     * @return  bool
     */
    private function add_handler() 
    {
        $id         = $this->auth['id'];

        if (!\is_numeric($id)) 
        {
            return false;
        }

        if (empty($this->auth['info'])) 
        {
            return false;
        }

        $bind = array(
            'facebook_id'   => $id,
            'token'         => $this->auth['token']
        );

        $auth_user  = \Hybrid\Auth::instance('user');

        if (true === $auth_user->is_logged())
        {
            $bind['user_id']        = $auth_user->get('id');
            $this->auth['user_id']  = $bind['user_id'];
        }

        \DB::insert('users_facebooks')
            ->set($bind)
            ->execute();

        \DB::insert('facebooks')
            ->set(array(
                'id'            => $id,
                'facebook_name' => $this->auth['info']->username,
                'first_name'    => $this->auth['info']->first_name,
                'last_name'     => $this->auth['info']->last_name,
                'facebook_url'  => $this->auth['info']->link
            ))
            ->execute();

        return true;
    }

    /**
     * Update Facebook Handler to database
     *
     * @access  private
     * @return  bool
     */
    private function update_handler() 
    {
        $id         = $this->auth['id'];

        if (!\is_numeric($id)) 
        {
            return false;
        }

        if (empty($this->auth['info'])) 
        {
            return false;
        }

        $bind = array(
            'token'     => $this->auth['token']
        );

        $auth_user  = \Hybrid\Auth::instance('user');

        if ($auth_user->is_logged() and 0 === \intval($this->auth['user_id']))
        {
            $bind['user_id']        = $auth_user->get('id');
            $this->auth['user_id']  = $bind['user_id'];
        }

        \DB::update('users_facebooks')
            ->set($bind)
            ->where('facebook_id', '=', $id)
            ->execute();

        \DB::update('facebooks')
            ->set(array(
                'facebook_name' => $this->auth['info']->username,
                'first_name'    => $this->auth['info']->first_name,
                'last_name'     => $this->auth['info']->last_name,
                'facebook_url'  => $this->auth['info']->link
            ))
            ->where('id', '=', $id)
            ->execute();

        return true;
    }

    /**
     * Register information to Session
     *
     * @access  protected
     * @return  bool
     */
    protected function register() 
    {
        \Cookie::set('_facebook_oauth', \Crypt::encode(\serialize((object) $this->auth)));

        return true;
    }

    /**
     * Unregister information from Session
     *
     * @access  protected
     * @return  bool
     */
    protected function unregister() 
    {
        \Cookie::delete('_facebook_oauth');

        return true;
    }

    /**
     * Initiate user login from Facebook
     *
     * Usage:
     * 
     * <code>\Hybrid\Auth::instance('facebook')->login($username, $token);</code>
     * 
     * @access  public
     * @param   string  $username
     * @param   string  $token
     * @return  bool
     */
     public function login($username, $token)
     {
         \Hybrid\Auth_Connection::instance('facebook')->login($username, $token);

         return true;
     }

    /**
     * Initiate user login out from Facebook
     *
     * Usage:
     * 
     * <code>\Hybrid\Auth::instance('facebook')->logout(false);</code>
     * 
     * @access  public
     * @param   bool    $redirect
     * @return  bool
     */
    public function logout($redirect = true)
    {
        $url = $this->get_url(array(
            'redirect_uri' => \Uri::create(static::redirect('after_logout'))
        ));
        
        $this->unregister();

        if (true === $redirect)
        {
            \Response::redirect($url, 'refresh');
        }
    }

}