<?php

namespace Hybrid;

abstract class Acl_Abstract {

	protected static $items;

	/**
	 * Return TRUE/FALSE whether visitor is logged in to the system
	 * 
	 * Usage:
	 * 
	 * <code>false === \Hybrid\Acl_User::is_logged()</code>
	 *
	 * @static
	 * @access	public
	 * @return	bool
	 */
	public static function is_logged() 
	{
		return (static::$items['id'] > 0 ? true : false);
	}

	/**
	 * Get current user authentication
	 * 
	 * Usage:
	 * 
	 * <code>$user = \Hybrid\Acl_User::get();</code>
	 *
	 * @static
	 * @access	public
	 * @return	object
	 */
	public static function get($name = null) 
	{
		if (!is_string($name)) 
		{
			return (object) static::$items;
		}

		if (!\array_key_exists($name, static::$items)) 
		{
			return false;
		}

		return static::$items[$name];
	}

	/**
	 * Enable to add salt to increase the security of the system
	 *
	 * @static
	 * @access	public
	 * @param	string	$password
	 * @return	string
	 */
	public static function add_salt($password = '') 
	{
		$salt =  \Config::get('app.salt', \Config::get('crypt.crypto_key'));

		return sha1($salt . $password);
	}
}