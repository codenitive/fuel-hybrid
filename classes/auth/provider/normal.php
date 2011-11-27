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
 * as possible so that we can support the most basic structure available
 * 
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Auth_Provider_Normal
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth_Provider_Normal 
{
	public $data = null;

	/**
	 * List of user fields to be used
	 *
	 * @access  protected
	 * @var     array
	 */
	protected $optional_fields = array('status', 'full_name');
	 
	/**
	 * Allow status to login based on `users`.`status`
	 *
	 * @access  protected
	 * @var     array
	 */
	protected $allowed_status = array('verified');
	 
	/**
	 * Use `users_meta` table
	 *
	 * @access  protected
	 * @var     bool
	 */
	protected $use_meta = true;
	 
	/**
	 * Use `users_auth` table
	 *
	 * @access  protected
	 * @var     bool
	 */
	protected $use_auth = true;

	/**
	 * Total number of seconds before Cookie expired
	 *
	 * @access  protected
	 * @var     bool
	 */
	protected $expiration = null;

	/**
	 * Verify User Agent in Hash
	 * 
	 * @access  protected
	 * @var     bool
	 */
	protected $verify_user_agent = false;

	/**
	 * Load configurations
	 *
	 * @static 
	 * @access  public
	 * @return  void
	 */
	public static function _init()
	{
		\Lang::load('autho', 'autho');
	}

	/**
	 * Initiate a new Auth_Provider_Normal instance.
	 * 
	 * @static
	 * @access  public
	 * @return  object  Auth_Provider_Normal
	 */
	public static function forge()
	{
		return new static();
	}

	/**
	 * Initiate a new Auth_Provider_Normal instance.
	 * 
	 * @static
	 * @access  public
	 * @return  object  Auth_Provider_Normal
	 */
	public static function make()
	{
		return static::forge();
	}

	/**
	 * Shortcode to self::forge().
	 *
	 * @deprecated  1.3.0
	 * @static
	 * @access  public
	 * @param   string  $name
	 * @return  object  Auth_Provider_Normal
	 * @see     self::forge()
	 */
	public static function factory()
	{
		\Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
		return static::forge();
	}

	/**
	 * Construct this provider
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function __construct()
	{
		$this->reset();

		// load Auth configuration
		$config            = \Config::get('autho.normal', array());
		
		$reserved_property = array('optional_fields');
		
		foreach ($config as $key => $value)
		{
			if ( ! property_exists($this, $key) or in_array($key, $reserved_property))
			{
				continue;
			}

			if (null === $value)
			{
				continue;
			}

			$this->{$key} = $value;
			\Config::set("autho.normal.{$key}", $value);
		}

		if ( ! isset($config['optional_fields']) or ! is_array($config['optional_fields']))
		{
			$config['optional_fields'] = array();
		}
		
		$this->optional_fields = array_merge($config['optional_fields'], $this->optional_fields);

		foreach ($this->optional_fields as $field)
		{
			if (is_string($field) and !isset($this->items[$field]))
			{
				$this->data[$field] = '';
			}
		}

		$this->verify_user_agent = \Config::get('autho.verify_user_agent', $this->verify_user_agent);
		$this->expiration        = \Config::get('autho.expiration', $this->expiration);
	}

	/**
	 * Default value for user data
	 * 
	 * @access  protected
	 * @return  bool
	 */
	public function reset() 
	{
		$this->data = array(
			'id'         => 0,
			'user_name'  => 'guest',
			'full_name'  => '',
			'email'      => '',
			'_hash'      => null,
			'password'   => '',
			'method'     => 'normal',
			'gender'     => '',
			'status'     => null,
			'roles'      => array('0' => 'guest'),
			'accounts'   => array(),
			'expired_at' => null,
		);

		return $this;
	}

	/**
	 * Get user information
	 *
	 * @access  public
	 * @return  array
	 */
	public function get()
	{
		return $this->data;
	}

	/**
	 * Get and verify user information given by Cookie
	 *
	 * @access  public
	 * @param   array   $data
	 * @return  self
	 */
	public function access_token($data)
	{
		$this->data['_hash'] = '';

		if (array_key_exists('_hash', $data) or null === $data['_hash'])
		{
			$this->data['_hash'] = $data['_hash'];
		}

		if (isset($data['expired_at']))
		{
			$this->data['expired_at'] = $data['expired_at'];
		}

		// in case if data['id'] doesn't exist or null, default to zero
		if ( ! isset($data['id']) or null === $data['id'])
		{
			$data['id'] = 0;
		}

		$query = \DB::select('users.*')
			->from('users')
			->where('users.id', '=', $data['id'])
			->limit(1);
		
		if (true === $this->use_auth)
		{
			$query->select(array('users_auths.password', 'password_token'))
				->join('users_auths')
				->on('users_auths.user_id', '=', 'users.id');
		}
		else
		{
			$query->select(array('users.password', 'password_token'));
		}
		
		if (true === $this->use_meta)
		{
			$query->select('users_meta.*')
				->join('users_meta')
				->on('users_meta.user_id', '=', 'users.id');    
		}
		
		$result = $query->as_object()->execute();

		$this->fetch_user($result);

		$this->fetch_linked_roles();
		$this->fetch_linked_accounts();

		$this->verify_token();

		return $this;
	}

	/**
	 * User login
	 *
	 * @access  public
	 * @param   string  $username
	 * @param   string  $password
	 * @param   string  $remember_me
	 * @return  self
	 * @throws  AuthException
	 */
	public function login($username, $password, $remember_me = false)
	{
		$this->data['_hash'] = null;
		unset($this->data['expired_at']);

		if ( !! $remember_me)
		{
			$this->expiration = -1;
		}

		$query = \DB::select('users.*')
				->from('users');
		
		if (true === $this->use_auth)
		{
			$query->select(array('users_auths.password', 'password_token'))
				->join('users_auths')
				->on('users_auths.user_id', '=', 'users.id');
		}
		else
		{
			$query->select(array('users.password', 'password_token'));
		}

		if (true === $this->use_meta)
		{
			$query->select('users_meta.*')
				->join('users_meta')
				->on('users_meta.user_id', '=', 'users.id');    
		}

		$result = $query->where_open()
			->where('users.user_name', '=', $username)
			->or_where('users.email', '=', $username)
			->where_close()
			->limit(1)
			->as_object()
			->execute();

		$this->fetch_user($result);

		$this->fetch_linked_roles();
		$this->fetch_linked_accounts();

		if ($this->data['id'] < 1)
		{
			$this->reset();
			throw new AuthException(\Lang::get('autho.user.not_exist', array('username' => $username)));
		}

		if ($this->data['password'] !== Auth::create_hash($password))
		{
			$this->reset();
			throw new AuthException(\Lang::get('autho.user.bad_combination'));
		}
		
		$this->verify_token();

		return $this;
	}

	/**
	 * User login via token
	 *
	 * @access  public
	 * @param   array   $user_data
	 * @param   bool    $remember_me
	 * @return  self
	 */
	public function login_token($user_data, $remember_me = false)
	{
		$this->data['_hash'] = null;
		unset($this->data['expired_at']);

		if ( !! $remember_me)
		{
			$this->expiration = -1;
		}

		extract($user_data);

		$uid = $info['uid'];

		$query = \DB::select('users.*')
			->from('users')
			->join('authentications')
			->on('authentications.user_id', '=', 'users.id')
			->where('authentications.uid', '=', $uid);

		if (true === $this->use_auth)
		{
			$query->select(array('users_auths.password', 'password_token'))
				->join('users_auths')
				->on('users_auths.user_id', '=', 'users.id');
		}
		else
		{
			$query->select(array('users.password', 'password_token'));
		}

		if (true === $this->use_meta)
		{
			$query->select('users_meta.*')
				->join('users_meta')
				->on('users_meta.user_id', '=', 'users.id');    
		}

		$result = $query->limit(1)
			->as_object()
			->execute();

		$this->fetch_user($result);

		$this->fetch_linked_roles();
		$this->fetch_linked_accounts();

		if ($this->data['id'] < 1)
		{
			throw new AuthException(\Lang::get('autho.user.not_linked'));
		}

		$this->verify_token();

		return $this;
	}

	/**
	 * Logout user account
	 *
	 * @access  public
	 * @return  self
	 */
	public function logout()
	{
		$this->revoke_token(true);
		return $this;
	}

	/**
	 * Register user's authentication to Session
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function verify_token()
	{
		$values = $this->data;
		$hash   = $values['user_name'].$values['password'];

		if (true === $this->verify_user_agent)
		{
			$hash .= Input::user_agent();
		}

		// create a hash
		$values['_hash'] = Auth::add_salt($hash);

		// for secure, don't ever include actual password
		unset($values['password']);

		// set cookie expiration
		if ( ! isset($values['expired_at']) or null == $values['expired_at'])
		{
			$expired_at = 0;

			if (0 > $this->expiration)
			{
				$expired_at = pow(2,31) - (time() + 1);
			}
			elseif (null !== $this->expiration and 0 !== $this->expiration)
			{
				 $expired_at = $this->expiration;
			}

			$this->data['expired_at'] = $values['expired_at'] = $expired_at;
		}
		
		\Cookie::delete('_users');
		\Cookie::set('_users', \Crypt::encode(serialize((object) $values)), $values['expired_at']);

		return true;
	}

	/**
	 * Delete user's authentication
	 *
	 * @access  protected
	 * @param   bool    $delete     set to true to delete session, only when login out
	 * @return  bool
	 */
	protected function revoke_token($delete = false)
	{
		$this->reset();

		if (true === $delete) 
		{
			\Cookie::delete('_users');
		}
		
		return true;
	}

	/**
	 * Fetch user information (not using Model)
	 *
	 * @access  protected
	 * @param   array   $result
	 * @return  bool
	 */
	protected function fetch_user($result)
	{
		if (null === $result or $result->count() < 1) 
		{
			return $this->reset();
		} 
	
		$user = $result->current();

		if ( ! in_array($user->status, $this->allowed_status)) 
		{
			// only verified user can login to this application
			return $this->reset();
		}

		// we validate the hash to add security to this application
		$hash = $user->user_name.$user->password_token;

		if ($this->verify_user_agent)
		{
			$hash .= Input::user_agent();
		}

		// validate our hash data
		if (null !== $this->data['_hash'] and $this->data['_hash'] !== Auth::add_salt($hash)) 
		{
			return $this->reset();
		}

		// user_id property wouldn't be available if we don't use meta or auth
		if ( ! $this->use_meta and ! $this->use_auth)
		{
			$this->data['id'] = $user->id;
		}
		else
		{
			$this->data['id'] = $user->user_id;
		}
		
		$this->data['user_name'] = $user->user_name;
		$this->data['email']     = $user->email;
		$this->data['password']  = $user->password_token;
		
		foreach ($this->optional_fields as $property)
		{
			if ( ! property_exists($user, $property))
			{
				continue;
			}
				
			$this->data[$property] = $user->{$property};
		}
	}

	/**
	 * Fetch all roles associated to the account
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function fetch_linked_roles()
	{
		$data  = array();
		
		$roles = \DB::select('roles.id', 'roles.name')
			->from('roles')
			->join('users_roles')
			->on('users_roles.role_id', '=', 'roles.id')
			->where('users_roles.user_id', '=', $this->data['id'])
			->as_object()
			->execute();

		// set a default roles if user not authenticated
		if (1 > count($roles))
		{
			$this->data['roles'] = array('0' => 'guest');
			return true;
		}

		// link all available roles for this user
		foreach ($roles as $role) 
		{
			$data[strval($role->id)] = \Inflector::friendly_title($role->name, '-', true);
		}
			
		$this->data['roles'] = $data;

		return true;
	}

	/**
	 * Fetch all linked account from OAuth and OAuth2
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function fetch_linked_accounts()
	{
		$data = array();
		
		$accounts = \DB::select('provider', 'uid', 'access_token', 'secret')
			->from('authentications')
			->where('user_id', '=', $this->data['id'])
			->as_object()
			->execute();

		// linked all available accounts to user account
		foreach ($accounts as $account) 
		{
			$data[strval($account->provider)] = array(
				'uid'          => $account->uid,
				'access_token' => $account->access_token,
				'secret'       => $account->secret,
			);
		}

		$this->data['accounts'] = $data;

		return true;
	}

}