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
 * Authentication Class
 * 
 * Why another class? FuelPHP does have it's own Auth package but what Hybrid does 
 * it not defining how you structure your database but instead try to be as generic 
 * as possible so that we can support the most basic structure available.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Auth
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class AuthException extends \FuelException {}
class AuthCancelException extends AuthException {}

class Auth 
{
	/**
	 * Cache Auth instance so we can reuse it on multiple request.
	 * 
	 * @static
	 * @access  protected
	 * @var     array
	 */
	protected static $instances = array();

	protected static $hasher = null;

	/**
	 * Redirect user based on type
	 *
	 * @static
	 * @access  protected
	 * @param   string  $type
	 * @return  void
	 * @throws  \FuelException
	 */
	public static function redirect($type)
	{
		$path = \Config::get("autho.urls.{$type}");

		if (null === $path)
		{
			throw new \FuelException(__METHOD__.": Unable to redirect using {$type} type.");
		}
		
		\Response::redirect($path);

		return true;
	}

	/**
	 * Only called once 
	 * 
	 * @static 
	 * @access  public
	 */
	public static function _init() 
	{
		\Config::load('autho', 'autho');
	}

	/**
	 * Initiate a new Auth_Driver instance.
	 * 
	 * @static
	 * @access  public
	 * @param   string  $name       null to fetch the default driver, or a driver id to get a specific one
	 * @return  Auth_Driver
	 * @throws  \FuelException
	 */
	public static function forge($name = null)
	{
		if (null === $name)
		{
			$name = 'user';
		}

		$name = strtolower($name);

		if ( ! isset(static::$instances[$name]))
		{
			$driver = "\Hybrid\Auth_Driver_".ucfirst($name);

			if ( !! class_exists($driver))
			{
				static::$instances[$name] = new $driver();
			}
			else
			{
				throw new \FuelException("Requested {$driver} does not exist.");
			}
		}

		return static::$instances[$name];
	}

	/**
	 * Initiate a new Auth_Driver instance.
	 * 
	 * @static
	 * @access  public
	 * @param   string  $name       null to fetch the default driver, or a driver id to get a specific one
	 * @return  Auth_Driver
	 * @throws  \FuelException
	 */
	public static function make($name = null)
	{
		return static::forge($name);
	}

	/**
	 * Shortcode to self::forge().
	 *
	 * @deprecated  1.3.0
	 * @static
	 * @access  public
	 * @param   string  $name
	 * @return  self::forge()
	 */
	public static function factory($name = null)
	{
		\Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
		
		return static::forge($name);
	}

	/**
	 * Get cached instance, or generate new if currently not available.
	 *
	 * @static
	 * @access  public
	 * @return  Auth_Driver
	 * @see     self::forge()
	 */
	public static function instance($name = null)
	{
		return static::forge($name);
	}

	/**
	 * Turn string to hash using sha1() hash with salt.
	 *
	 * @static
	 * @deprecated
	 * @access  public
	 * @param   string  $string       String to be hashed
	 * @return  string
	 */
	public static function add_salt($string = '')
	{
		\Log::warning('This method is deprecated. Please use create_hash() instead.', __METHOD__);
		
		return static::create_hash($string);
	}

	/**
	 * Turn string to hash using sha1(), md5() or crypt_hash hash with salt.
	 *
	 * @static
	 * @access  public
	 * @param   string  $string       String to be hashed
	 * @param   string  $hash_type    String of hash type
	 * @return  string
	 */
	public static function create_hash($string = '', $hash_type = null)
	{
		$salt   = \Config::get('autho.salt', \Config::get('crypt.crypto_key'));
		$string = $string;

		if (null === $hash_type or ! in_array($hash_type, array('md5', 'crypt_hash', 'sha1')))
		{
			$hash_type = \Config::get('autho.hash_type', 'sha1');
		}

		switch ($hash_type)
		{
			case 'md5' :
				return \md5($salt.$string);
			break;

			case 'crypt_hash' :
				return static::crypt_hash($string);
			break;

			case 'sha1' :
			default :
				return sha1($salt.$string);
		}
	}

	/**
	 * Use crypt_hash hash type
	 *
	 * @static
	 * @access  protected
	 * @param   string  $string     String to be hashed
	 * @return  string
	 */
	protected static function crypt_hash($string = '')
	{
		if ( ! class_exists('PHPSecLib\\Crypt_Hash', false))
		{
			import('phpseclib/Crypt/Hash', 'vendor');
		}

		is_null(static::$hasher) and static::$hasher = new \PHPSecLib\Crypt_Hash();

		$salt   = \Config::get('autho.salt', \Config::get('crypt.crypto_key'));
		
		return base64_encode(static::$hasher->pbkdf2($string, $salt, 10000, 32));
	}

	/**
	 * Check if user has any of provided roles.
	 * 
	 * @static
	 * @access  public
	 * @param   mixed   $check_roles
	 * @return  bool 
	 */
	public static function has_roles($check_roles) 
	{
		$user = static::make('user')->get();

		if ( ! is_array($check_roles)) 
		{
			$check_roles = func_get_args();
		}

		foreach ($user->roles as $role) 
		{
			$role = \Inflector::friendly_title($role, '-', TRUE);

			foreach ($check_roles as $check_against) 
			{
				if ($role == $check_against) 
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Login based on available Auth_Driver.
	 *
	 * @static
	 * @access  public
	 * @param   string  $username       A string of either `user_name` or `email` field from table `users`.
	 * @param   string  $password       An unhashed `password` or `token` string from external API.
	 * @param   string  $driver         Driver type string, default to 'user'.
	 * @return  bool
	 * @throws  \FuelException
	 */
	public static function login($username, $password, $driver = 'user')
	{
		return static::make($driver)->login($username, $password);
	}

	/**
	 * Reauthenticate current user.
	 *
	 * @static
	 * @access  public
	 * @param   string  $driver         Driver type string, default to 'user'.
	 * @return  bool
	 * @throws  \FuelException
	 */
	public static function reauthenticate($driver = 'user')
	{
		return static::make($driver)->reauthenticate();
	}

	/**
	 * Logout from all loaded instances.
	 *
	 * @static
	 * @access  public
	 * @return  bool
	 */
	public static function logout()
	{
		foreach (static::$instances as $name => $instance)
		{
			$instance->logout(false);
		}

		return true;
	}

	/**
	 * Link user account with external provider
	 *
	 * @static
	 * @access  public
	 * @param   int     $user_id
	 * @param   array   $user_data
	 * @return  bool
	 */
	public static function link_account($user_id, $user_data)
	{
		$provider = null;
		$token    = null;
		$info     = null;

		extract($user_data);

		if (empty($token) or empty($info))
		{
			return ;
		}

		if ($user_id < 1)
		{
			return ;
		}

		if ( ! isset($info['uid']) or null === $info['uid'])
		{
			throw new AuthException("Missing required information: uid");
		}
		
		if ( ! isset($token->access_token) or null === $token->access_token)
		{	
			throw new AuthException("Missing required information: access_token");	
		}

		$auth = Auth_Model_Authentication::find(array(
			'where' => array(
				array('user_id', '=', $user_id),
				array('provider', '=', $provider)
			),
			'limit' => 1,
		));

		$values = array(
			'uid'           => $info['uid'],
			'access_token'  => isset($token->access_token) ? $token->access_token : '',
			'secret'        => isset($token->secret) ? $token->secret : '',
			'expires'       => isset($token->expires) ? $token->expires : -1,
			'refresh_token' => isset($token->refresh_token) ? $token->refresh_token : '',
		);

		// Attach this account to the logged in user
		if (null !== $auth)
		{
			$auth->current();
			$auth->set($values);
		}
		else
		{
			$values = array(
				'user_id'  => $user_id,
				'provider' => $provider,
			) + $values;

			$auth = Auth_Model_Authentication::forge($values);
		}

		$auth->save();

		return true;
	}
	
}