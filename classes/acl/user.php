<?php

/**
 * Fuel
 *
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
class Acl_User {

	protected static $items = null;
	public static $acl = NULL;

	/**
	 * Default value for user data
	 * 
	 * @static
	 * @access private
	 * @return boolean
	 */
	private static function _set_default() {
		$twitter = 0;

		static::$items = array(
			'id' => 0,
			'user_name' => 'guest',
			'full_name' => '',
			'email' => '',
			'roles' => array('guest'),
			'hash' => '',
			'password' => '',
			'method' => 'normal',
			'gender' => '',
			'age' => 0,
			'status' => 1,
			'twitter' => $twitter
		);

		return true;
	}

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
	 * @access public
	 * @return object
	 */
	public static function acl() {
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
	 * @access private
	 * @return boolean
	 */
	public static function _init() {
		\Config::load('crypt', true);

		$users = \Cookie::get('_users');

		if (!is_null($users)) {
			$users = unserialize(\Crypt::decode($users));
			static::$items = (array) $users;
		} else {
			static::_unregister();
			return true;
		}

		static::$acl = new \Hybrid\Acl;

		switch ($users->method) {
			case 'normal' :
				/*
				 * SELECT `users`.*, `users_auths`.`password`, `users_twitter`.`id` AS `twitter_id` 
				 * FROM `users` 
				 * INNER JOIN `users_auths` ON (`users_auths`.`user_id`=`users`.`id`) 
				 * LEFT JOIN `users_twitters` ON (`users_twitters`.`user_id`=`users`.`id`)   
				 * WHERE `users`.`id`=%d  */
				$result = \DB::select('users.*', 'users_auths.password', array('users_twitters.id', 'twitter_id'), 'users_meta.*')
						->from('users')
						->join('users_auths')
						->on('users_auths.user_id', '=', 'users.id')
						->join('users_meta')
						->on('users_meta.user_id', '=', 'users.id')
						->join('users_twitters', 'left')
						->on('users_twitters.user_id', '=', 'users.id')
						->where('users.id', '=', static::$items['id'])
						->limit(1)
						->as_object()
						->execute();

				break;

			case 'twitter_oauth' :
				/**
				 * @todo: Twitter OAuth integration
				 */
				/* $result = \DB::select('users.*', 'users_auths.password', array('twitters.id', 'twitter_id'))->from('users')
				  ->join('users_auths')
				  ->on('users_auths.id', '=', 'users.id')
				  ->join('twitters')
				  ->on('users.id', '=', 'twitters.user_id')
				  ->where('twitters.id', '=', $twitter_oauth->id)
				  ->execute(); */
				break;
		}

		if ($result->count() < 1) {
			static::_unregister(true);
			return true;
		} else {
			$user = $result->current();

			if ($user->status !== 'verified') {
				// only verified user can login to this application
				static::_unregister();
				return true;
			}

			// we validate the hash to add security to this application
			$hash = $user->user_name . $user->password;

			if (static::$items['hash'] !== static::add_salt($hash)) {
				static::_unregister();
				return true;
			}

			static::$items['id'] = $user->id;
			static::$items['user_name'] = $user->user_name;
			static::$items['full_name'] = $user->full_name;
			static::$items['email'] = $user->email;
			static::$items['status'] = $user->status;
			static::$items['roles'] = $users->roles;
			static::$items['password'] = $user->password;
			
			if (property_exists($user, 'gender')) {
				static::$items['gender'] = $user->gender;
			}
			
			if (property_exists($user, 'birthdate')) {
				static::$items['age'] = (int) date('Y') - (int) date('Y', strtotime($user->birthdate));
			}

			// if user already link their account with twitter, map the relationship
			if (!is_null($user->twitter_id)) {
				static::$items['twitter'] = $user->twitter_id;
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
	 * @access public
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public static function login($username, $password) {
		/*
		 * SELECT `users`.*, `users_auths`.`password`, `users_twitter`.`id` AS `twitter_id` 
		 * FROM `users` 
		 * INNER JOIN `users_auths` ON (`users_auths`.`user_id`=`users`.`id`) 
		 * LEFT JOIN `users_twitters` ON (`users_twitters`.`user_id`=`users`.`id`)   
		 * WHERE (`users`.`user_name`='%s' OR `users`.`email`='%s') 
		 * AND `users_auth`.`password`=''  */
		/* $user = \Model_User::find_by_user_name_or_email($username, $username, array('limit' => 1, 'include' => array('users_auths', 'users_twitters'))); */

		$users = \DB::select('users.*', 'users_auths.password', array('users_twitters.id', 'twitter_id'))
				->from('users')
				->join('users_auths')
				->on('users_auths.user_id', '=', 'users.id')
				->join('users_twitters', 'left')
				->on('users_twitters.user_id', '=', 'users.id')
				->where_open()
				->where('users.user_name', '=', $username)
				->or_where('users.email', '=', $username)
				->where_close()
				->limit(1)
				->as_object()
				->execute();

		if ($users->count() < 1) {
			return false;
		} else {
			$user = $users->current();

			if ($user->password !== static::add_salt($password)) {
				return false;
			}

			if ($user->status !== 'verified') {
				return false;
			}

			static::$items['id'] = $user->id;
			static::$items['user_name'] = $user->user_name;
			static::$items['full_name'] = $user->full_name;
			static::$items['email'] = $user->email;
			static::$items['status'] = $user->status;
			static::$items['method'] = 'normal';
			static::$items['password'] = $user->password;

			if (!is_null($user->twitter_id)) {
				static::$items['twitter'] = $user->twitter_id;
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
	 * @access public
	 * @param boolean $redirect
	 * @return boolean
	 */
	public static function logout($redirect = true) {
		static::_unregister(true);

		if (true === $redirect) {
			\Response::redirect('site/index');
		}

		return true;
	}

	/**
	 * Get user's roles
	 *
	 * @TODO: not using ActiveRecord
	 * 
	 * @static
	 * @access private
	 * @return boolean
	 */
	private static function _get_roles() {
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

		foreach ($roles as $role) {
			$data['' . $role->id] = \Inflector::friendly_title($role->name, '-', TRUE);
		}

		static::$items['roles'] = $data;

		return true;
	}

	/**
	 * Register user's authentication to Session
	 *
	 * @static
	 * @access private
	 * @access private
	 * @return boolean
	 */
	private static function _register() {
		$values = static::$items;
		$values['hash'] = static::add_salt(static::$items['user_name'] . static::$items['password']);

		\Cookie::set('_users', \Crypt::encode(serialize((object) $values)));

		return true;
	}

	/**
	 * Delete user's authentication
	 *
	 * @static
	 * @access public
	 * @param boolean $delete set to true to delete session, only when login out
	 * @return boolean
	 */
	private static function _unregister($delete = false) {
		static::_set_default();

		if (true == $delete) {
			\Cookie::delete('_users');
		}

		return true;
	}

	/**
	 * Return TRUE/FALSE whether visitor is logged in to the system
	 * 
	 * Usage:
	 * 
	 * <code>false === \Hybrid\Acl_User::is_logged()</code>
	 *
	 * @static
	 * @access public
	 * @return boolean
	 */
	public static function is_logged() {
		return (static::$items['id'] > 0 ? true : false);
	}

	/**
	 * Enable to add salt to increase the security of the system
	 *
	 * @static
	 * @access public
	 * @param string $password
	 * @return string
	 */
	public static function add_salt($password = '') {
		$salt = \Config::get('crypt.salt');

		return sha1($salt . $password);
	}

	/**
	 * Get current user authentication
	 * 
	 * Usage:
	 * 
	 * <code>$user = \Hybrid\Acl_User::get();</code>
	 *
	 * @static
	 * @access public
	 * @return object
	 */
	public static function get($name = null) {
		if (!is_string($name)) {
			return (object) static::$items;
		}

		if (!\array_key_exists($name, static::$items)) {
			return false;
		}

		return static::$items[$name];
	}

}