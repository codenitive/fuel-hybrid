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

class Acl_Twitter {
	
	protected static $_tmhOAuth = null;
	protected static $items = array(
		'token' => null,
		'secret' => null,
		'access' => 0,
		'id' => 0,
		'info' => ''
	);

	/**
	 * Initiate a connection to tmhOAuth Class with config
	 *
	 * @access public
	 * @return boolean
	 */
	public static function _init() 
	{
		\Config::load('app', true);
		\Config::load('crypt', true);
		
		if (is_null(static::$_tmhOAuth)) 
		{
			$config = \Config::get('app.api.twitter');
			static::$_tmhOAuth = new \tmhOAuth($config);
		}
		
		static::_factory();
	}
	
	/**
	 * Get cookie contain
	 *
	 * @static
	 * @access	protected
	 * @return	bool
	 */
	protected static function _factory()
	{
		$oauth = \Cookie::get('_twitter_oauth');

		if (!is_null($oauth)) 
		{
			static::$items = (array) $oauth;
			static::$_tmhOAuth->config["user_token"] = $oauth->token;
			static::$_tmhOAuth->config["user_secret"] = $oauth->secret;
		}

		return true;
	}

	/**
	 * Return Twitter user information and token
	 *
	 * @access public
	 * @return object
	 */
	public static function get() 
	{
		return (object) static::$items;
	}

	public static function get_adapter() 
	{
		return static::$_tmhOAuth;
	}

	/**
	 * Authenticate user with Twitter Account
	 * There are three process/stage of authenticating an account:
	 * 1. getting a twitter token
	 * 2. authenticate the user with twitter account
	 * 3. verifying the user account
	 *
	 * @access public
	 * @return boolean
	 */
	public static function execute() 
	{
		switch (static::$items['access']) 
		{

			case 2 :
				# initiate stage 3
				return static::_verify_token();
			break;

			case 1 :
				# initiate stage 2
				static::_access_token();
			break;

			case 0 :
				# initiate stage 1
				return static::_request_token();
			break;
		}

		return false;
	}

	/**
	 * Stage 3: verifying the user account
	 *
	 * @static
	 * @access	protected
	 * @return	bool
	 */
	protected static function _verify_token() 
	{
		static::$_tmhOAuth->request('GET', static::$_tmhOAuth->url('1/account/verify_credentials'));

		$response = json_decode(static::$_tmhOAuth->response['response']);

		if (isset($response->id)) 
		{
			static::$items['id'] = $response->id;
			static::$items['info'] = (object) array(
				'screen_name' => $response->screen_name,
				'name' => $response->name,
				'id' => $response->id
			);

			static::$items['access'] = 3;

			static::_register();

			$result = \DB::select('users_twitters.*', array('users.user_name', 'username'))
							->from('users_twitters')
							->join('users', 'LEFT')
							->on('users_twitters.user_id', '=', 'users.id')
							->where('users_twitters.id', '=', $response->id)->execute();

			if ($result->count() < 1) 
			{
				static::_add_handler($response->id, $response);
				\Request::redirect('register');
				return true;
			} 
			else 
			{
				$row = $result->current();

				static::_update_handler($response->id, $response);

				if (is_null($row['user_id'])) {
					\Request::redirect('register');
					return true;
				}

				\Hybrid\Acl_User::login($row['username'], static::$items['token'], 'twitter_oauth');
				\Request::redirect('dashboard');
			}
		}

		return false;
	}

	/**
	 * Stage 2: authenticate the user with twitter account
	 *
	 * @static
	 * @access	protected
	 * @return	bool
	 */
	protected static function _access_token() 
	{
		static::$_tmhOAuth->request("POST", static::$_tmhOAuth->url("oauth/access_token", ""), array(
			//pass the oauth_verifier received from Twitter
			'oauth_verifier' => \Hybrid\Input::get('oauth_verifier', '')
		));

		if (200 == static::$_tmhOAuth->response['code']) 
		{
			$response = static::$_tmhOAuth->extract_params(static::$_tmhOAuth->response["response"]);

			static::$items['token'] = $response['oauth_token'];
			static::$items['secret'] = $response['oauth_token_secret'];
			static::$items['access'] = 2;

			static::$_tmhOAuth->config["user_token"] = $response['oauth_token'];
			static::$_tmhOAuth->config["user_secret"] = $response['oauth_token_secret'];

			static::_register();
			static::_factory();
		} 
		else 
		{
			logger('error', '\\Acl\\Twitter::access_token request fail: ' . static::$_tmhOAuth->response['code']);
			logger('debug', 'Response: ' . json_encode(static::$_tmhOAuth->response));
			return false;
		}

		return true;
	}

	/**
	 * Stage 1: getting a twitter token
	 *
	 * @static
	 * @access	protected
	 * @return	bool
	 */
	protected static function _request_token() 
	{
		static::$_tmhOAuth->request('POST', static::$_tmhOAuth->url('oauth/request_token', ''));

		if (200 == static::$_tmhOAuth->response['code']) 
		{
			$response = static::$_tmhOAuth->extract_params(static::$_tmhOAuth->response['response']);

			static::$items['token'] = $response['oauth_token'];
			static::$items['secret'] = $response['oauth_token_secret'];
			static::$items['access'] = 1;

			static::_register();

			$url = static::$_tmhOAuth->url("oauth/authorize", '');
			$url .= "?oauth_token={$response['oauth_token']}";

			\Request::redirect($url, 'refresh');
			exit();
			return true;
		} 
		else 
		{
			logger('error', '\\Acl\\Twitter::request_token request fail: ' . static::$_tmhOAuth->response['code']);
			return false;
		}

		return false;
	}

	/**
	 * Add Twitter Handler to database
	 *
	 * @static
	 * @access	private
	 * @param	int		$id
	 * @param	object	$meta
	 * @return	bool
	 */
	private static function _add_handler($id, $meta) 
	{
		if (!is_numeric($id)) 
		{
			return false;
		}

		if (empty($meta)) 
		{
			return false;
		}

		\DB::insert('users_twitters')->set(array(
			'twitter_id' => $id,
			'token' => static::$items['token'],
			'secret' => static::$items['secret']
		))->execute();

		\DB::insert('twitters')->set(array(
			'id' => $id,
			'twitter_name' => $meta->screen_name,
			'full_name' => $meta->name,
			'profile_image' => $meta->profile_image_url
		))->execute();

		return true;
	}

	/**
	 * Update Twitter Handler to database
	 *
	 * @static
	 * @access	private
	 * @param	int		$id
	 * @param	object	$meta
	 * @return	bool
	 */
	private static function _update_handler($id, $meta) 
	{
		if (!is_numeric($id)) 
		{
			return false;
		}

		if (empty($meta)) 
		{
			return false;
		}

		\DB::update('users_twitters')->set(array(
			'token' => static::$items['token'],
			'secret' => static::$items['secret']
		))->where('twitter_id', '=', $id)->execute();

		\DB::update('twitters')->set(array(
			'full_name' => $meta->name,
			'twitter_name' => $meta->screen_name,
			'profile_image' => $meta->profile_image_url
		))->where('id', '=', $id)->execute();

		return true;
	}

	/**
	 * Register information to Session
	 *
	 * @static
	 * @access	protected
	 * @return	bool
	 */
	protected static function _register() 
	{
		\Cookie::set('_twitter_oauth', (object) static::$items);

		return true;
	}

	/**
	 * Unregister information from Session
	 *
	 * @static
	 * @access	protected
	 * @return	bool
	 */
	protected static function _unregister() 
	{
		\Cookie::delete('_twitter_oauth');

		return true;
	}
}

