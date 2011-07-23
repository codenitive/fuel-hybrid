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
 * @category    Acl_User
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
class Acl_User extends Acl_Abstract {

	protected static $items = null;
	public static $acl = NULL;

	/**
	 * Default value for user data
	 * 
	 * @static
	 * @access	protected
	 * @return	bool
	 */
	protected static function _set_default() 
	{
		static::$items = array(
			'id' => 0,
			'user_name' => 'guest',
			'full_name' => '',
			'email' => '',
			'roles' => array('guest'),
			'_hash' => '',
			'password' => '',
			'method' => 'normal',
			'gender' => '',
			'status' => 1,
			'twitter' => 0,
			'facebook' => 0
		);

		return true;
	}
	
	protected static $_optionals = array('email', 'status', 'full_name', 'gender', 'birthdate');
	protected static $_use_meta = true;
	protected static $_use_auth = true;
	protected static $_use_twitter = false;
	protected static $_use_facebook = false;

	/**
	 * Get Acl\Role object, it's a quick way of get and use \Acl\Role without having to 
	 * initiate another call when this class already has it
	 * 
	 * Usage:
	 * 
	 * <code>$role = \Hybrid\Acl_User::acl();
	 * $role->add_recources('monkeys');</code>
	 * 
	 * @static
	 * @access	public
	 * @return	object
	 */
	public static function acl() 
	{
		return static::$acl;
	}

	/**
	 * Initiate and check user authentication, the method will try to detect current 
	 * cookie for this session and verify the cookie with the database, it has to 
	 * be verify so that no one else could try to copy the same cookie configuration 
	 * and use it as their own.
	 * 
	 * @TODO need to use User-Agent as one of the hash value 
	 * 
	 * @static
	 * @access	private
	 * @return	bool
	 */
	public static function _init() 
	{
		\Config::load('app', true);
		\Config::load('crypt', true);
		
		if (!is_null(static::$acl))
		{
			return;
		}

		$users = \Cookie::get('_users');

		if (!is_null($users)) 
		{
			$users = unserialize(\Crypt::decode($users));
			static::$items = (array) $users;
		} 
		else 
		{
			$users = new \stdClass();
			$users->method = 'normal';
			static::_unregister();
		}

		static::$acl = new \Hybrid\Acl;
		
		$config = \Config::get('app.user_table', array());
		
		foreach ($config as $key => $value)
		{
			if (!!property_exists('\\Hybrid\\Acl_User', "_{$key}"))
			{
				$property = '_'.$key;
				static::$$property = $value;
				\Config::set("app.user_table.{$key}", $value);
			}
		}
		
		static::$_optionals = \Config::get('app.user_table.optionals', static::$_optionals);
		
		foreach (static::$_optionals as $field)
		{
			if (is_string($field) and !isset(static::$items[$field]))
			{
				static::$items[$field] = '';
			}
		}

		switch ($users->method) 
		{
			case 'normal' :

				$results = \DB::select('users.*')
					->from('users')
					->where('users.id', '=', static::$items['id'])->limit(1);
				
				if (static::$_use_auth === true)
				{
					$results->select('users_auths.password')
						->join('users_auths')
						->on('users_auths.user_id', '=', 'users.id');
				}
				
				if (static::$_use_meta === true)
				{
					$results->select('users_meta.*')
						->join('users_meta')
						->on('users_meta.user_id', '=', 'users.id');	
				}
				
				if (static::$_use_twitter === true)
				{
					$results->select(array('users_twitters.id', 'twitter_id'))
						->join('users_twitters', 'left')
						->on('users_twitters.user_id', '=', 'users.id');
				}

				if (static::$_use_facebook === true)
				{
					$results->select(array('users_facebooks.id', 'facebook_id'))
						->join('users_facebooks', 'left')
						->on('users_facebooks.user_id', '=', 'users.id');
				}
				
				$result = $results->as_object()->execute();

			break;

			case 'twitter_oauth' :
				if (static::$_use_twitter !== true) 
				{
					static::_unregister(true);
					return true;
				}
				
				$twitter_oauth = \Hybrid\Acl_Twitter::get();
				
				$results = \DB::select('users.*', array('users_twitters.id', 'twitter_id'))
					->from('users')
					->join('users_twitters')
					->on('users.id', '=', 'users_twitters.user_id')
					->where('users_twitters.twitter_id', '=', $twitter_oauth->id)->limit(1);
				
				if (static::$_use_auth === true)
				{
					$results->select('users_auths.password')
						->join('users_auths')
						->on('users_auths.user_id', '=', 'users.id');
				}
				
				if (static::$_use_meta === true)
				{
					$results->select('users_meta.*')
						->join('users_meta')
						->on('users_meta.user_id', '=', 'users.id');	
				}

				if (static::$_use_facebook === true)
				{
					$results->select(array('users_facebooks.id', 'facebook_id'))
						->join('users_facebooks', 'left')
						->on('users_facebooks.user_id', '=', 'users.id');
				}
				
				$result = $results->as_object()->execute();
			break;

			case 'facebook_oauth' :
				if (static::$_use_facebook !== true) 
				{
					static::_unregister(true);
					return true;
				}
				
				$facebook_oauth = \Hybrid\Acl_Facebook::get();
				
				$results = \DB::select('users.*', array('users_facebooks.id', 'facebook_id'))
					->from('users')
					->join('users_facebooks')
					->on('users.id', '=', 'users_facebooks.user_id')
					->where('users_facebooks.facebook_id', '=', $facebook_oauth->id)->limit(1);
				
				if (static::$_use_auth === true)
				{
					$results->select('users_auths.password')
						->join('users_auths')
						->on('users_auths.user_id', '=', 'users.id');
				}
				
				if (static::$_use_meta === true)
				{
					$results->select('users_meta.*')
						->join('users_meta')
						->on('users_meta.user_id', '=', 'users.id');	
				}

				if (static::$_use_twitter === true)
				{
					$results->select(array('users_twitters.id', 'twitter_id'))
						->join('users_twitters', 'left')
						->on('users_twitters.user_id', '=', 'users.id');
				}
				
				$result = $results->as_object()->execute();
			break;
		}

		if (is_null($result) or $result->count() < 1) 
		{
			static::_unregister(true);
			return true;
		} 
		else 
		{
			$user = $result->current();

			if ($user->status !== 'verified') 
			{
				// only verified user can login to this application
				static::_unregister();
				return true;
			}

			// we validate the hash to add security to this application
			$hash = $user->user_name . $user->password;

			if (static::$items['_hash'] !== static::add_salt($hash)) 
			{
				static::_unregister();
				return true;
			}

			static::$items['id'] = $user->user_id;
			static::$items['user_name'] = $user->user_name;
			static::$items['roles'] = $users->roles;
			static::$items['password'] = $user->password;
			
			foreach (static::$_optionals as $property)
			{
				if (\property_exists($user, $property))
				{
					static::$items[$property] = $user->{$property};
				}
			}

			// if user already link their account with twitter, map the relationship
			if (property_exists($user, 'twitter_id')) 
			{
				static::$items['twitter'] = $user->twitter_id;
			}

			// if user already link their account with facebook, map the relationship
			if (property_exists($user, 'facebook_id')) 
			{
				static::$items['facebook'] = $user->facebook_id;
			}
		}

		return true;
	}

	/**
	 * Login user using normal authentication (username and password)
	 * 
	 * Usage:
	 * 
	 * <code>$login = \Hybrid\Acl_User::login('someone', 'password');</code>
	 * 
	 * @static
	 * @access	public
	 * @param	string	$username
	 * @param	string	$password
	 * @return	bool
	 */
	public static function login($username, $password, $method = 'normal') 
	{
		$result = \DB::select('users.*')
				->from('users');
		
		if (static::$_use_auth === true)
		{
			$result->select('users_auths.password')
				->join('users_auths')
				->on('users_auths.user_id', '=', 'users.id');
		}

		if (static::$_use_meta === true)
		{
			$result->select('users_meta.*')
				->join('users_meta')
				->on('users_meta.user_id', '=', 'users.id');	
		}

		if (static::$_use_twitter === true)
		{
			$result->select(array('users_twitters.id', 'twitter_id'), array('users_twitters.token', 'twitter_token'))
				->join('users_twitters', 'left')
				->on('users_twitters.user_id', '=', 'users.id');
		}

		if (static::$_use_facebook === true)
		{
			$result->select(array('users_facebooks.id', 'facebook_id'), array('users_facebooks.token', 'facebook_token'))
				->join('users_facebooks', 'left')
				->on('users_facebooks.user_id', '=', 'users.id');
		}

		$result = $result->where_open()
			->where('users.user_name', '=', $username)
			->or_where('users.email', '=', $username)
			->where_close()
			->limit(1)->as_object()->execute();

		if (is_null($result) or $result->count() < 1) 
		{
			return false;
		} 
		else 
		{
			$user = $result->current();

			switch ($method)
			{
				case 'normal' :
					if ($user->password !== static::add_salt($password)) 
					{
						return false;
					}
				break;

				case 'twitter_oauth' :
					if ($user->twitter_token !== $password)
					{
						return false;
					}
				break;

				case 'facebook_oauth' :
					if ($user->facebook_token !== $password)
					{
						return false;
					}
				break;
			}

			if ($user->status !== 'verified') 
			{
				return false;
			}

			static::$items['id'] = $user->user_id;
			static::$items['user_name'] = $user->user_name;
			static::$items['method'] = 'normal';
			static::$items['password'] = $user->password;
			
			foreach (static::$_optionals as $property)
			{
				if (\property_exists($user, $property))
				{
					static::$items[$property] = $user->{$property};
				}
			}

			// if user already link their account with twitter, map the relationship
			if (\property_exists($user, 'twitter_id')) 
			{
				static::$items['twitter'] = $user->twitter_id;
			}

			// if user already link their account with facebook, map the relationship
			if (property_exists($user, 'facebook_id')) 
			{
				static::$items['facebook'] = $user->facebook_id;
			}

			static::_get_roles();
			static::_register();

			return true;
		}

		return false;
	}

	/**
	 * Initiate user login out regardless of any method they use
	 *
	 * Usage:
	 * 
	 * <code>\Hybrid\Acl_User::logout(false);</code>
	 * 
	 * @static
	 * @access	public
	 * @param	bool	$redirect
	 * @return	bool
	 */
	public static function logout($redirect = true) 
	{
		static::_unregister(true);

		if (true === $redirect) 
		{
			\Response::redirect(\Config::get('app.api._route.after_logout', '/'));
		}

		return true;
	}

	/**
	 * Get user's roles
	 * 
	 * @static
	 * @access	protected
	 * @return	bool
	 */
	protected static function _get_roles() 
	{
		$data = array();

		/* SELECT `roles`.* 
		 * FROM `roles` 
		 * INNER JOIN `users_roles` 
		 * ON (`users_roles`.`role_id`=`roles`.`id`) 
		 * WHERE `users_roles`.`user_id`=%d
		 */
		$roles = \DB::select('roles.id', 'roles.name')
				->from('roles')
				->join('users_roles')
				->on('users_roles.role_id', '=', 'roles.id')
				->where('users_roles.user_id', '=', static::$items['id'])
				->as_object()
				->execute();

		foreach ($roles as $role) 
		{
			$data['' . $role->id] = \Inflector::friendly_title($role->name, '-', TRUE);
		}

		static::$items['roles'] = $data;

		return true;
	}

	/**
	 * Register user's authentication to Session
	 *
	 * @static
	 * @access	protected
	 * @return	bool
	 */
	protected static function _register() 
	{
		$values = static::$items;
		$values['_hash'] = static::add_salt(static::$items['user_name'] . static::$items['password']);

		\Cookie::set('_users', \Crypt::encode(serialize((object) $values)));

		return true;
	}

	/**
	 * Delete user's authentication
	 *
	 * @static
	 * @access	protected
	 * @param	bool	$delete set to true to delete session, only when login out
	 * @return	bool
	 */
	protected static function _unregister($delete = false) 
	{
		static::_set_default();

		if (true == $delete) 
		{
			\Cookie::delete('_users');

			if (static::$_use_twitter === true)
			{
				\Hybrid\Acl_Twitter::logout();
			}

			if (static::$_use_facebook === true)
			{
				\Hybrid\Acl_Facebook::logout(false);
			}
		}

		return true;
	}

}