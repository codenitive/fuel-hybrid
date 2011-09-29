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

\Hybrid\Factory::import('tmhOAuth', 'vendor');

use \tmhOAuth;

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Auth_Twitter
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth_Twitter extends Auth_Driver {

    /**
     * Auth data
     *
     * @access  protected
     * @var     object|array
     */
    protected $auth     = array(
        'token'     => null,
        'secret'    => null,
        'access'    => 0,
        'id'        => 0,
        'user_id'   => 0,
        'info'      => null,
    );

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
        return \Hybrid\Auth::instance('twitter');
    }

    /**
     * Initiate a connection to tmhOAuth Class with config
     *
     * @access  public
     * @return  void
     */
    public function __construct() 
    {
        parent::_initiate();
        
        if (is_null($this->adapter)) 
        {
            $config         = \Config::get('app.api.twitter');
            $this->adapter  = new \tmhOAuth($config);
        }
        
        $cookie         = \Cookie::get('_twitter_oauth');

        if (!\is_null($cookie)) 
        {
            $cookie     = \unserialize(\Crypt::decode($cookie));

            $this->auth                             = (array) $cookie;
            $this->adapter->config["user_token"]    = $cookie->token;
            $this->adapter->config["user_secret"]   = $cookie->secret;
        }

        return true;
    }


    /**
     * Authenticate user with Twitter Account
     * There are three process/stage of authenticating an account:
     * 1. getting a twitter token
     * 2. authenticate the user with twitter account
     * 3. verifying the user account
     *
     * @access  public
     * @return  bool
     */
    public function execute() 
    {
        switch ($this->auth['access']) 
        {
            case 3 :
                return $this->authenticate();
            break;

            case 2 :
                # initiate stage 3
                return $this->verify_token();
            break;

            case 1 :
                # initiate stage 2
                $this->access_token();
            break;

            case 0 :
                # initiate stage 1
                return $this->request_token();
            break;
        }

        return false;
    }

    /**
     * Stage 4: authenticate
     * 
     * @access  protected
     * @return  bool
     */
    protected function authenticate()
    {
        $result = \DB::select('users_twitters.*', array('users.user_name', 'username'))
                ->from('users_twitters')
                ->join('users', 'LEFT')
                ->on('users_twitters.user_id', '=', 'users.id')
                ->where('users_twitters.twitter_id', '=', $this->auth['id'])
                ->execute();

        if ($result->count() < 1) 
        {
            return false;
        } 
        else 
        {
            $row = $result->current();

            $this->auth['user_id'] = $row['user_id'];
            $this->update_handler($row['twitter_id']);

            if (\is_null($row['user_id']) or \intval($this->auth['user_id']) < 1) 
            {
                static::redirect('registration');

                return true;
            }

            $this->login($row['username'], $this->auth['token']);
            
            static::redirect('after_login');

            return true;
        }   
    }

    /**
     * Stage 3: verifying the user account
     *
     * @access  protected
     * @return  bool
     */
    protected function verify_token() 
    {
        $this->adapter->request('GET', $this->adapter->url('1/account/verify_credentials'));

        $response = \json_decode($this->adapter->response['response']);

        if (isset($response->id)) 
        {
            $this->auth['id']      = $response->id;
            $this->auth['info']    = (object) array(
                'screen_name'       => $response->screen_name,
                'name'              => $response->name,
                'id'                => $response->id,
            );

            $this->auth['access']  = 3;

            $result = \DB::select('users_twitters.*', array('users.user_name', 'username'))
                        ->from('users_twitters')
                        ->join('users', 'LEFT')
                        ->on('users_twitters.user_id', '=', 'users.id')
                        ->where('users_twitters.twitter_id', '=', $response->id)
                        ->execute();

            if ($result->count() < 1) 
            {
                $this->add_handler($response->id, $response);
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
                $row                    = $result->current();

                $this->auth['user_id']  = $row['user_id'];

                $this->update_handler($response->id, $response);
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
        }

        return false;
    }

    /**
     * Stage 2: authenticate the user with twitter account
     *
     * @access  protected
     * @return  bool
     */
    protected function access_token() 
    {
        $this->adapter->request("POST", $this->adapter->url("oauth/access_token", ""), array(
            //pass the oauth_verifier received from Twitter
            'oauth_verifier' => \Hybrid\Input::get('oauth_verifier', '')
        ));

        if (200 === \intval($this->adapter->response['code'])) 
        {
            $response = $this->adapter->extract_params($this->adapter->response["response"]);

            $this->auth['token']     = $response['oauth_token'];
            $this->auth['secret']    = $response['oauth_token_secret'];
            $this->auth['access']    = 2;

            $this->adapter->config["user_token"]  = $response['oauth_token'];
            $this->adapter->config["user_secret"] = $response['oauth_token_secret'];

            $this->register();
            $this->execute();

            return true;
        } 
        else 
        {
            \Log::error('\\Hybrid\\Auth_Twitter::access_token request fail: ' . $this->adapter->response['code']);
            \Log::debug('Response: ' . \json_encode($this->adapter->response));

            return false;
        }

        return true;
    }

    /**
     * Stage 1: getting a twitter token
     *
     * @access  protected
     * @return  bool
     */
    protected function request_token() 
    {
        $this->adapter->request('POST', $this->adapter->url("oauth/request_token", ""));

        if (200 == $this->adapter->response['code']) 
        {
            $response = $this->adapter->extract_params($this->adapter->response['response']);

            $this->auth['token']     = $response['oauth_token'];
            $this->auth['secret']    = $response['oauth_token_secret'];
            $this->auth['access']    = 1;

            $this->register();

            $url    = $this->adapter->url("oauth/authorize", '');
            $url    .= "?oauth_token={$response['oauth_token']}";

            \Response::redirect($url, 'refresh');
            exit();
            return true;
        } 
        else 
        {
            \Log::error('\\Hybrid\\Auth_Twitter::request_token request fail: ' . $this->adapter->response['code']);
            return false;
        }

        return false;
    }

    /**
     * Add Twitter Handler to database
     *
     * @access  private
     * @param   int     $id
     * @param   object  $meta
     * @return  bool
     */
    private function add_handler($id, $meta = null) 
    {
        if (!\is_numeric($id)) 
        {
            return false;
        }

        $bind = array(
            'twitter_id'    => $id,
            'token'         => $this->auth['token'],
            'secret'        => $this->auth['secret'],
        );

        $auth_user  = \Hybrid\Auth::instance('user');

        if ($auth_user->is_logged()) 
        {
            $bind['user_id']        = $auth_user->get('id');
            $this->auth['user_id']  = $bind['user_id'];
        }

        \DB::insert('users_twitters')
            ->set($bind)
            ->execute();

        if (!empty($meta)) 
        {
            \DB::insert('twitters')->set(array(
                'id'            => $id,
                'twitter_name'  => $meta->screen_name,
                'full_name'     => $meta->name,
                'profile_image' => $meta->profile_image_url
            ))->execute();
        }

        return true;
    }

    /**
     * Update Twitter Handler to database
     *
     * @access  private
     * @param   int     $id
     * @param   object  $meta
     * @return  bool
     */
    private function update_handler($id, $meta = null) 
    {
        if (!\is_numeric($id)) 
        {
            return false;
        }

        $bind = array(
            'token'     => $this->auth['token'],
            'secret'    => $this->auth['secret'],
        );

        $auth_user = \Hybrid\Auth::instance('user');

        if ($auth_user->is_logged() and 0 === \intval($this->auth['user_id'])) 
        {
            $bind['user_id']        = $auth_user->get('id');
            $this->auth['user_id']  = $bind['user_id'];
        }

        \DB::update('users_twitters')
            ->set($bind)
            ->where('twitter_id', '=', $id)
            ->execute();

        if (!empty($meta)) 
        {
            \DB::update('twitters')
                ->set(array(
                    'full_name'     => $meta->name,
                    'twitter_name'  => $meta->screen_name,
                    'profile_image' => $meta->profile_image_url
                ))
                ->where('id', '=', $id)
                ->execute();
        }

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
        \Cookie::set('_twitter_oauth', \Crypt::encode(\serialize((object) $this->auth)));

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
        \Cookie::delete('_twitter_oauth');

        return true;
    }

    /**
     * Initiate user login from Twitter
     *
     * Usage:
     * 
     * <code>\Hybrid\Auth::instance('twitter')->login($username, $token);</code>
     * 
     * @access  public
     * @param   string  $username
     * @param   string  $token
     * @return  bool
     */
     public function login($username, $token)
     {
         \Hybrid\Auth_Connection::instance('twitter')->login($username, $token);

         return true;
     }


    /**
     * Initiate user login out from Twitter
     *
     * Usage:
     * 
     * <code>\Hybrid\Auth::instance('twitter')->logout(false);</code>
     * 
     * @access  public
     * @param   bool    $redirect
     * @return  bool
     */
    public function logout($redirect = false)
    {
        return $this->unregister();
    }
    
}

