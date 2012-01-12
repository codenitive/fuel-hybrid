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

	protected $tables = array();

	/**
	 * Aliases
	 *
	 * @access  protected
	 * @var     array
	 */
	protected $aliases = array(
		'user_name' => 'user_name',
		'email'     => 'email',
	);

	/**
	 * List of user fields to be used
	 *
	 * @access  protected
	 * @var     array
	 */
	protected $optionals = array('status', 'full_name');
	 
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
		\Config::load('hybrid', 'hybrid');
		\Lang::load('autho', 'autho');
	}

	/**
	 * Initiate a new Auth_Provider_Normal instance.
	 * 
	 * @static
	 * @access  public
	 * @return  object  Auth_Provider_Normal
	 * @throws  \FuelException
	 */
	public static function __callStatic($method, array $arguments)
	{
		if ( ! in_array($method, array('factory', 'forge', 'instance', 'make')))
		{
			throw new \FuelException(__CLASS__.'::'.$method.'() does not exist.');
		}

		return new static();
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
		
		$reserved_property = array('optionals', 'optionals');
		
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

		// backward compatibility
		if ( ! isset($config['optional_fields']) or ! is_array($config['optional_fields']))
		{
			$config['optional_fields'] = array();
		}

		if ( ! isset($config['optionals']) or ! is_array($config['optionals']))
		{
			$config['optionals'] = $config['optional_fields'];
		}
		
		$this->optionals = array_merge($config['optionals'], $this->optionals);

		foreach ($this->optionals as $field)
		{
			if (is_string($field) and !isset($this->items[$field]))
			{
				$this->data[$field] = '';
			}
		}

		$this->verify_user_agent = \Config::get('autho.verify_user_agent', $this->verify_user_agent);
		$this->expiration        = \Config::get('autho.expiration', $this->expiration);
		$this->tables            = \Config::get('hybrid.tables.users', array(
			'user' => 'users',
			'meta' => 'users_meta',
			'auth' => 'users_auths',
		));
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

		if (array_key_exists('_hash', $data))
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

		$query = \DB::select($this->tables['user'].'.*')
			->from($this->tables['user'])
			->where($this->tables['user'].'.id', '=', $data['id'])
			->limit(1);
		
		if (true === $this->use_auth)
		{
			$query->select(array($this->tables['auth'].'.password', 'password_token'))
				->join($this->tables['auth'])
				->on($this->tables['auth'].'.'.\Inflector::singularize($this->tables['user']).'_id', '=', $this->tables['user'].'.id');
		}
		else
		{
			$query->select(array($this->tables['user'].'.password', 'password_token'));
		}
		
		if (true === $this->use_meta)
		{
			$query->select($this->tables['meta'].'.*')
				->join($this->tables['meta'])
				->on($this->tables['meta'].'.'.\Inflector::singularize($this->tables['user']).'_id', '=', $this->tables['user'].'.id');    
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

		$query = \DB::select($this->tables['user'].'.*')
				->from($this->tables['user']);
		
		if (true === $this->use_auth)
		{
			$query->select(array($this->tables['auth'].'.password', 'password_token'))
				->join($this->tables['auth'])
				->on($this->tables['auth'].'.'.\Inflector::singularize($this->tables['user']).'_id', '=', $this->tables['user'].'.id');
		}
		else
		{
			$query->select(array($this->tables['user'].'.password', 'password_token'));
		}

		if (true === $this->use_meta)
		{
			$query->select($this->tables['meta'].'.*')
				->join($this->tables['meta'])
				->on($this->tables['meta'].'.'.\Inflector::singularize($this->tables['user']).'_id', '=', $this->tables['user'].'.id');    
		}

		$result = $query->where_open()
			->where($this->tables['user'].'.'.\Arr::get($this->aliases, 'user_name', 'user_name'), '=', $username)
			->or_where($this->tables['user'].'.email', '=', $username)
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

		$social_table = \Config::get('hybrid.tables.social', 'authentications');

		$query = \DB::select($this->tables['user'].'.*')
			->from($this->tables['user'])
			->join($social_table)
			->on($social_table.'.'.\Inflector::singularize($this->tables['user']).'_id', '=', $this->tables['user'].'.id')
			->where($social_table.'.uid', '=', $uid);

		if (true === $this->use_auth)
		{
			$query->select(array($this->tables['auth'].'.password', 'password_token'))
				->join($this->tables['auth'])
				->on($this->tables['auth'].'.'.\Inflector::singularize($this->tables['user']).'_id', '=', $this->tables['user'].'.id');
		}
		else
		{
			$query->select(array($this->tables['user'].'.password', 'password_token'));
		}

		if (true === $this->use_meta)
		{
			$query->select($this->tables['meta'].'.*')
				->join($this->tables['meta'])
				->on($this->tables['meta'].'.'.\Inflector::singularize($this->tables['user']).'_id', '=', $this->tables['user'].'.id');    
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
		$values['_hash'] = Auth::create_hash($hash);

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

		foreach ($this->aliases as $key => $alias)
		{
			// no point making an alias of the same key
			if ($key === $alias)
			{
				continue;
			}

			$this->data[$alias] = $this->data[$key];
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
		if (null !== $this->data['_hash'] and $this->data['_hash'] !== Auth::create_hash($hash)) 
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
			$user_id_field = \Inflector::singularize($this->tables['user']).'_id';
			$this->data['id'] = $user->{$user_id_field};
		}
		
		$user_name = \Arr::get($this->aliases, 'user_name', 'user_name');
		$email     = \Arr::get($this->aliases, 'email', 'email');

		$this->data[$user_name]  = $user->{$user_name};
		$this->data[$email]      = $user->{$email};
		$this->data['password']  = $user->password_token;
		
		foreach ($this->optionals as $property)
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
		$data        = array();
		
		$group_table = \Config::get('hybrid.tables.group', 'roles');
		$link_table  = \Config::get('hybrid.tables.users.group', 'users_roles');
		
		$roles = \DB::select($group_table.'.id', $group_table.'.name')
			->from($group_table)
			->join($link_table)
			->on($link_table.'.'.\Inflector::singularize($group_table).'_id', '=', $group_table.'.id')
			->where($link_table.'.'.\Inflector::singularize($this->tables['user']).'_id', '=', $this->data['id'])
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
			->from(\Config::get('hybrid.tables.social', 'authentications'))
			->where(\Inflector::singularize($this->tables['user']).'_id', '=', $this->data['id'])
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