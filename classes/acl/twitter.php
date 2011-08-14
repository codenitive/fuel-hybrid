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

import('tmhOAuth', 'vendor');

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
 * @category    Acl_Twitter
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Acl_Twitter extends Acl_Abstract {
    
    /**
     * Twitter Adapter object
     *
     * @static
     * @access  protected
     * @var     object
     */
    protected static $adapter   = null;

    /**
     * User data
     *
     * @static
     * @access  protected
     * @var     object|array
     */
    protected static $items     = array(
        'token'     => null,
        'secret'    => null,
        'access'    => 0,
        'id'        => 0,
        'user_id'   => 0,
        'info'      => null,
    );

    /**
     * Initiate a connection to tmhOAuth Class with config
     *
     * @static
     * @access  public
     * @return  void
     */
    public static function _init() 
    {
        parent::_init();
        
        if (is_null(static::$adapter)) 
        {
            $config             = \Config::get('app.api.twitter');
            static::$adapter    = new \tmhOAuth($config);
        }
        
        static::factory();
    }
    
    /**
     * Get cookie contain
     *
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function factory()
    {
        $oauth = \Cookie::get('_twitter_oauth');

        if (!\is_null($oauth)) 
        {
            $oauth  = \unserialize(\Crypt::decode($oauth));

            static::$items                          = (array) $oauth;
            static::$adapter->config["user_token"]  = $oauth->token;
            static::$adapter->config["user_secret"] = $oauth->secret;
        }

        return true;
    }

    /**
     * Return tmhOAuth Object
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
     * Authenticate user with Twitter Account
     * There are three process/stage of authenticating an account:
     * 1. getting a twitter token
     * 2. authenticate the user with twitter account
     * 3. verifying the user account
     *
     * @static
     * @access  public
     * @return  bool
     */
    public static function execute() 
    {
        switch (static::$items['access']) 
        {
            case 3 :
                return static::authenticate();
            break;

            case 2 :
                # initiate stage 3
                return static::verify_token();
            break;

            case 1 :
                # initiate stage 2
                static::access_token();
            break;

            case 0 :
                # initiate stage 1
                return static::request_token();
            break;
        }

        return false;
    }

    /**
     * Stage 4: authenticate
     * 
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function authenticate()
    {
        $result = \DB::select('users_twitters.*', array('users.user_name', 'username'))
                ->from('users_twitters')
                ->join('users', 'LEFT')
                ->on('users_twitters.user_id', '=', 'users.id')
                ->where('users_twitters.twitter_id', '=', static::$items['id'])
                ->execute();

        if ($result->count() < 1) 
        {
            return false;
        } 
        else 
        {
            $row = $result->current();
            static::$items['user_id'] = $row['user_id'];
            static::update_handler($row['twitter_id']);

            if (\is_null($row['user_id']) or \intval(static::$items['user_id']) < 1) 
            {
                static::redirect('registration');

                return true;
            }

            \Hybrid\Acl_User::login($row['username'], static::$items['token'], 'twitter_oauth');
            static::redirect('after_login');

            return true;
        }   
    }

    /**
     * Stage 3: verifying the user account
     *
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function verify_token() 
    {
        static::$adapter->request('GET', static::$adapter->url('1/account/verify_credentials'));

        $response = \json_decode(static::$adapter->response['response']);

        if (isset($response->id)) 
        {
            static::$items['id']    = $response->id;
            static::$items['info']  = (object) array(
                'screen_name'       => $response->screen_name,
                'name'              => $response->name,
                'id'                => $response->id,
            );

            static::$items['access'] = 3;

            $result = \DB::select('users_twitters.*', array('users.user_name', 'username'))
                        ->from('users_twitters')
                        ->join('users', 'LEFT')
                        ->on('users_twitters.user_id', '=', 'users.id')
                        ->where('users_twitters.twitter_id', '=', $response->id)
                        ->execute();

            if ($result->count() < 1) 
            {
                static::add_handler($response->id, $response);
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
                static::update_handler($response->id, $response);
                static::register();

                if (\is_null($row['user_id']) or \intval(static::$items['user_id']) < 1) 
                {
                    static::redirect('registration');

                    return true;
                }

                \Hybrid\Acl_User::login($row['username'], static::$items['token'], 'twitter_oauth');
                static::redirect('after_login');

                return true;
            }
        }

        return false;
    }

    /**
     * Stage 2: authenticate the user with twitter account
     *
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function access_token() 
    {
        static::$adapter->request("POST", static::$adapter->url("oauth/access_token", ""), array(
            //pass the oauth_verifier received from Twitter
            'oauth_verifier' => \Hybrid\Input::get('oauth_verifier', '')
        ));

        if (200 === \intval(static::$adapter->response['code'])) 
        {
            $response = static::$adapter->extract_params(static::$adapter->response["response"]);

            static::$items['token']     = $response['oauth_token'];
            static::$items['secret']    = $response['oauth_token_secret'];
            static::$items['access']    = 2;

            static::$adapter->config["user_token"]  = $response['oauth_token'];
            static::$adapter->config["user_secret"] = $response['oauth_token_secret'];

            static::register();
            static::execute();
            return true;
        } 
        else 
        {
            \Log::error('\\Hybrid\\Acl_Twitter::access_token request fail: ' . static::$adapter->response['code']);
            \Log::debug('Response: ' . \json_encode(static::$adapter->response));
            return false;
        }

        return true;
    }

    /**
     * Stage 1: getting a twitter token
     *
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function request_token() 
    {
        static::$adapter->request('POST', static::$adapter->url("oauth/request_token", ""));

        if (200 == static::$adapter->response['code']) 
        {
            $response = static::$adapter->extract_params(static::$adapter->response['response']);

            static::$items['token']     = $response['oauth_token'];
            static::$items['secret']    = $response['oauth_token_secret'];
            static::$items['access']    = 1;

            static::register();

            $url    = static::$adapter->url("oauth/authorize", '');
            $url    .= "?oauth_token={$response['oauth_token']}";

            \Response::redirect($url, 'refresh');
            exit();
            return true;
        } 
        else 
        {
            \Log::error('\\Hybrid\\Acl_Twitter::request_token request fail: ' . static::$adapter->response['code']);
            return false;
        }

        return false;
    }

    /**
     * Add Twitter Handler to database
     *
     * @static
     * @access  private
     * @param   int     $id
     * @param   object  $meta
     * @return  bool
     */
    private static function add_handler($id, $meta = null) 
    {
        if (!\is_numeric($id)) 
        {
            return false;
        }

        $bind = array(
            'twitter_id'    => $id,
            'token'         => static::$items['token'],
            'secret'        => static::$items['secret'],
        );

        if (\Hybrid\Acl_User::is_logged()) 
        {
            $bind['user_id']            = \Hybrid\Acl_User::get('id');
            static::$items['user_id']   = $bind['user_id'];
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
     * @static
     * @access  private
     * @param   int     $id
     * @param   object  $meta
     * @return  bool
     */
    private static function update_handler($id, $meta = null) 
    {
        if (!\is_numeric($id)) 
        {
            return false;
        }

        $bind = array(
            'token'     => static::$items['token'],
            'secret'    => static::$items['secret'],
        );

        if (\Hybrid\Acl_User::is_logged() and 0 === \intval(static::$items['user_id'])) 
        {
            $bind['user_id']            = \Hybrid\Acl_User::get('id');
            static::$items['user_id']   = $bind['user_id'];
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
     * @static
     * @access  protected
     * @return  bool
     */
    protected static function register() 
    {
        \Cookie::set('_twitter_oauth', \Crypt::encode(\serialize((object) static::$items)));

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
        \Cookie::delete('_twitter_oauth');

        return true;
    }


    /**
     * Initiate user login out from Twitter
     *
     * Usage:
     * 
     * <code>\Hybrid\Acl_Twitter::logout(false);</code>
     * 
     * @static
     * @access  public
     * @param   bool    $redirect
     * @return  bool
     */
    public static function logout()
    {
        return static::unregister();
    }
}

