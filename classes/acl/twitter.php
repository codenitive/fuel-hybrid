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
	
	protected static $_instance = null;
	protected static $items = array(
		'token' => null,
		'secret' => null,
		'access' => 0,
		'id' => 0,
		'user_id' => 0,
		'info' => null
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
		
		if (is_null(static::$_instance)) 
		{
			$config = \Config::get('app.api.twitter');
			static::$_instance = new \tmhOAuth($config);
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
			$oauth = unserialize(\Crypt::decode($oauth));
			static::$items = (array) $oauth;
			static::$_instance->config["user_token"] = $oauth->token;
			static::$_instance->config["user_secret"] = $oauth->secret;
		}

		return true;
	}

	/**
	 * Return tmhOAuth Object
	 *
	 * @static
	 * @access public
	 * @return object
	 */
	public static function get_adapter() 
	{
		return static::$_instance;
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
			case 3 :
				return static::_authenticate();
			break;

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
	 * Stage 4: authenticate
	 * 
	 * @static
	 * @access 	protected
	 * @return 	bool
	 */
	protected static function _authenticate()
	{
		$result = \DB::select('users_twitters.*', array('users.user_name', 'username'))
				->from('users_twitters')
				->join('users', 'LEFT')
				->on('users_twitters.user_id', '=', 'users.id')
				->where('users_twitters.twitter_id', '=', static::$items['id'])->execute();

		if ($result->count() < 1) 
		{
			return false;
		} 
		else 
		{
			$row = $result->current();
			static::$items['user_id'] = $row['user_id'];
			static::_update_handler($row['twitter_id']);

			if (is_null($row['user_id']) or intval(static::$items['user_id']) < 1) {
				\Response::redirect(\Config::get('app.api._redirect.registration', '/'));
				return true;
			}

			\Hybrid\Acl_User::login($row['username'], static::$items['token'], 'twitter_oauth');
			\Response::redirect(\Config::get('app.api._redirect.after_login', '/'));
			return true;
		}	
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
		static::$_instance->request('GET', static::$_instance->url('1/account/verify_credentials'));

		$response = json_decode(static::$_instance->response['response']);

		if (isset($response->id)) 
		{
			static::$items['id'] = $response->id;
			static::$items['info'] = (object) array(
				'screen_name' => $response->screen_name,
				'name' => $response->name,
				'id' => $response->id
			);

			static::$items['access'] = 3;

			$result = \DB::select('users_twitters.*', array('users.user_name', 'username'))
							->from('users_twitters')
							->join('users', 'LEFT')
							->on('users_twitters.user_id', '=', 'users.id')
							->where('users_twitters.twitter_id', '=', $response->id)->execute();

			if ($result->count() < 1) 
			{
				static::_add_handler($response->id, $response);
				static::_register();

				if (intval(static::$items['user_id']) < 1) 
				{
					\Response::redirect(\Config::get('app.api._redirect.registration', '/'));
				}
				else 
				{
					\Response::redirect(\Config::get('app.api._redirect.after_login', '/'));
				}
				
				return true;
			} 
			else 
			{
				$row = $result->current();

				static::$items['user_id'] = $row['user_id'];
				static::_update_handler($response->id, $response);
				static::_register();

				if (is_null($row['user_id']) or intval(static::$items['user_id']) < 1) {
					\Response::redirect(\Config::get('app.api._redirect.registration', '/'));
					return true;
				}

				\Hybrid\Acl_User::login($row['username'], static::$items['token'], 'twitter_oauth');
				\Response::redirect(\Config::get('app.api._redirect.after_login', '/'));
				return true;
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
		static::$_instance->request("POST", static::$_instance->url("oauth/access_token", ""), array(
			//pass the oauth_verifier received from Twitter
			'oauth_verifier' => \Hybrid\Input::get('oauth_verifier', '')
		));

		if (200 == static::$_instance->response['code']) 
		{
			$response = static::$_instance->extract_params(static::$_instance->response["response"]);

			static::$items['token'] = $response['oauth_token'];
			static::$items['secret'] = $response['oauth_token_secret'];
			static::$items['access'] = 2;

			static::$_instance->config["user_token"] = $response['oauth_token'];
			static::$_instance->config["user_secret"] = $response['oauth_token_secret'];

			static::_register();
			static::execute();
			return true;
		} 
		else 
		{
			logger('error', '\\Hybrid\\Acl_Twitter::access_token request fail: ' . static::$_instance->response['code']);
			logger('debug', 'Response: ' . json_encode(static::$_instance->response));
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

		static::$_instance->request('POST', static::$_instance->url("oauth/request_token", ""));

		if (200 == static::$_instance->response['code']) 
		{
			$response = static::$_instance->extract_params(static::$_instance->response['response']);

			static::$items['token'] = $response['oauth_token'];
			static::$items['secret'] = $response['oauth_token_secret'];
			static::$items['access'] = 1;

			static::_register();

			$url = static::$_instance->url("oauth/authorize", '');
			$url .= "?oauth_token={$response['oauth_token']}";

			\Response::redirect($url, 'refresh');
			exit();
			return true;
		} 
		else 
		{
			logger('error', '\\Hybrid\\Acl_Twitter::request_token request fail: ' . static::$_instance->response['code']);
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
	private static function _add_handler($id, $meta = null) 
	{
		if (!is_numeric($id)) 
		{
			return false;
		}

		$bind = array(
			'twitter_id' => $id,
			'token' => static::$items['token'],
			'secret' => static::$items['secret'],
		);

		if (\Hybrid\Acl_User::is_logged()) 
		{
			$bind['user_id'] = \Hybrid\Acl_User::get('id');
			static::$items['user_id'] = $bind['user_id'];
		}

		\DB::insert('users_twitters')->set($bind)->execute();

		if (!empty($meta)) 
		{
			\DB::insert('twitters')->set(array(
				'id' => $id,
				'twitter_name' => $meta->screen_name,
				'full_name' => $meta->name,
				'profile_image' => $meta->profile_image_url
			))->execute();
		}

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
	private static function _update_handler($id, $meta = null) 
	{
		if (!is_numeric($id)) 
		{
			return false;
		}

		$bind = array(
			'token' => static::$items['token'],
			'secret' => static::$items['secret'],
		);

		if (\Hybrid\Acl_User::is_logged() and static::$items['user_id'] == 0) 
		{
			$bind['user_id'] = \Hybrid\Acl_User::get('id');
			static::$items['user_id'] = $bind['user_id'];
		}

		\DB::update('users_twitters')->set($bind)->where('twitter_id', '=', $id)->execute();

		if (!empty($meta)) 
		{
			\DB::update('twitters')->set(array(
				'full_name' => $meta->name,
				'twitter_name' => $meta->screen_name,
				'profile_image' => $meta->profile_image_url
			))->where('id', '=', $id)->execute();
		}

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
		\Cookie::set('_twitter_oauth', \Crypt::encode(serialize((object) static::$items)));

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


	/**
	 * Initiate user login out from Twitter
	 *
	 * Usage:
	 * 
	 * <code>\Hybrid\Acl_Twitter::logout(false);</code>
	 * 
	 * @static
	 * @access	public
	 * @param	bool	$redirect
	 * @return	bool
	 */
	public static function logout()
	{
		return static::_unregister();
	}
}

