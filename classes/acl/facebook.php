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

import('facebook/facebook', 'vendor');

use \Facebook;
use \FacebookApiException;

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
	
	protected static $_instance = null;
	protected static $items = array(
		'id' => 0,
		'user_id' => 0,
		'token' => '',
		'info' => null,
		'access' => 0,
	);
	protected static $_config = null;
	protected static $_user = null;

	/**
	 * Initiate a connection to Facebook SDK Class with config
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
			static::$_config = \Config::get('app.api.facebook');
			static::$_instance = new \Facebook(array(
				'appId' => static::$_config['app_id'],
				'secret' => static::$_config['secret'],
			));
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
		$oauth = \Cookie::get('_facebook_oauth');

		if (!is_null($oauth))
		{
			$oauth = unserialize(\Crypt::decode($oauth));
			static::$items = (array) $oauth;
		}
	}

	/**
	 * return Facebook Object
	 *
	 * @static
	 * @access 	public
	 * @return 	boolean
	 */
	public static function get_adapter() 
	{
		return static::$_instance;
	}

	/**
	 * Authenticate user with Facebook Account
	 * There are three process/stage of authenticating an account:
	 * 2. authenticate the user with Facebook account
	 * 3. verifying the user account
	 *
	 * @access public
	 * @return boolean
	 */
	public static function execute()
	{
		$status = false;

		switch (intval(static::$items['access']))
		{
			case 0 :
				$status = static::_access_token();
			break;

			case 1 :
				/* fetch data from database to insert or update */
				$status = static::_verify_token();
			break;

			case 2 :
			default :
				/* Do nothing for now */
				$status = true;
			break;
		}

		return $status;
	}

	public static function get_url($option = array())
	{
		$redirect_uri = \Config::get('app.api.facebook.redirect_uri');
		$scope = \Config::get('app.api.facebook.scope', '');

		$config = array('scope' => $scope);

		if (!is_null($redirect_uri))
		{
			$config['redirect_uri'] = \Uri::create($redirect_uri);
		}

		$config = array_merge($config, $option);

		switch (static::$items['access'])
		{
			case 1 :
			case 2 :
				unset($config['scope']);
				return static::$_instance->getLogoutUrl($config);
			break;

			case 0 :
			default :
				return static::$_instance->getLoginUrl($config);
			break;
		}
	}

	/**
	 * Stage 2: verifying the user account
	 *
	 * @static
	 * @access	protected
	 * @return	bool
	 */
	protected static function _verify_token() 
	{
		static::$items['access'] = 2;

		$result = \DB::select('users_facebooks.*', array('users.user_name', 'username'))
						->from('users_facebooks')
						->join('users', 'LEFT')
						->on('users_facebooks.user_id', '=', 'users.id')
						->where('users_facebooks.facebook_id', '=', static::$items['id'])->execute();

		if ($result->count() < 1) 
		{
			static::_add_handler();
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
			static::_update_handler();
			static::_register();

			if (is_null($row['user_id']) or intval(static::$items['user_id']) < 1) 
			{
				\Response::redirect(\Config::get('app.api._redirect.registration', '/'));
				return true;
			}

			\Hybrid\Acl_User::login($row['username'], static::$items['token'], 'facebook_oauth');
			\Response::redirect(\Config::get('app.api._redirect.after_login', '/'));
			return true;
		}

		return false;
	}

	/**
	 * Stage 1: authenticate the user with twitter account
	 *
	 * @static
	 * @access	protected
	 * @return	bool
	 */
	protected static function _access_token() 
	{

		static::$_user = static::$_instance->getUser();

		if (static::$_user <> 0 and !is_null(static::$_user))
		{

			try
			{
				static::$items['access'] = (static::$items['access'] == 0 ? 1 : static::$items['access']);
				$profile_data = static::$_instance->api('/me');
				
			} 
			catch (\FacebookApiException $e)
			{
				logger('error', '\\Hybrid\\Acl_Facebook::_factory request fail: ' . $e);
				static::$_user = null;
				static::$items['access'] = 0;
			}
		}

		if (static::$_user <> 0 and !is_null(static::$_user))
		{
			$profile_data = (object) $profile_data;
			static::$items['id'] = $profile_data->id;
			static::$items['info'] = new \stdClass();
			static::$items['info']->username = $profile_data->username;
			static::$items['info']->first_name = $profile_data->first_name;
			static::$items['info']->last_name = $profile_data->last_name;
			static::$items['info']->link = $profile_data->link;
			static::$items['token'] = static::$_instance->getAccessToken();

			if (static::$items['access'] == 0)
			{
				static::$items['access'] = 1;
			}

			return static::_verify_token();
		}

		return false;
	}

	/**
	 * Add Facebook Handler to database
	 *
	 * @static
	 * @access	private
	 * @param	int		$id
	 * @param	object	$meta
	 * @return	bool
	 */
	private static function _add_handler() 
	{
		$id = static::$items['id'];

		if (!is_numeric($id)) 
		{
			return false;
		}

		if (empty(static::$items['info'])) 
		{
			return false;
		}

		$bind = array(
			'facebook_id' => $id,
			'token' => static::$items['token']
		);

		if (\Hybrid\Acl_User::is_logged())
		{
			$bind['user_id'] = \Hybrid\Acl_User::get('id');
			static::$items['user_id'] = $bind['user_id'];
		}

		\DB::insert('users_facebooks')->set($bind)->execute();

		\DB::insert('facebooks')->set(array(
			'id' => $id,
			'facebook_name' => static::$items['info']->username,
			'first_name' => static::$items['info']->first_name,
			'last_name' => static::$items['info']->last_name,
			'facebook_url' => static::$items['info']->link
		))->execute();

		return true;
	}

	/**
	 * Update Facebook Handler to database
	 *
	 * @static
	 * @access	private
	 * @param	int		$id
	 * @param	object	$meta
	 * @return	bool
	 */
	private static function _update_handler() 
	{
		$id = static::$items['id'];

		if (!is_numeric($id)) 
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

		if (\Hybrid\Acl_User::is_logged() and static::$items['user_id'] == 0)
		{
			$bind['user_id'] = \Hybrid\Acl_User::get('id');
			static::$items['user_id'] = $bind['user_id'];
		}

		\DB::update('users_facebooks')->set($bind)->where('facebook_id', '=', $id)->execute();

		\DB::update('facebooks')->set(array(
			'facebook_name' => static::$items['info']->username,
			'first_name' => static::$items['info']->first_name,
			'last_name' => static::$items['info']->last_name,
			'facebook_url' => static::$items['info']->link
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
		\Cookie::set('_facebook_oauth', \Crypt::encode(serialize((object) static::$items)));

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
	 * @access	public
	 * @param	bool	$redirect
	 * @return	bool
	 */
	public static function logout($redirect = true)
	{
		$url = static::get_url(array('redirect_uri' => \Uri::create('/')));
		static::_unregister();

		if ($redirect == true)
		{
			\Response::redirect($url, 'refresh');
		}
		
	}
}