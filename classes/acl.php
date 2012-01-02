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
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Acl
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Acl 
{
	/**
	 * Cache ACL instance so we can reuse it on multiple request. 
	 * 
	 * @static
	 * @access  protected
	 * @var     array
	 */
	protected static $instances = array();

	/**
	 * List of types
	 * 
	 * @access      protected
	 * @staticvar   array
	 */
	protected static $types = array('deny', 'view', 'create', 'edit', 'delete', 'all');

	/**
	 * Only called once 
	 * 
	 * @static 
	 * @access  public
	 */
	public static function _init() 
	{
		\Event::trigger('init_acl');
	}

	/**
	 * Initiate a new Acl instance.
	 * 
	 * @static
	 * @access  public
	 * @param   string  $name
	 * @return  Acl
	 * @throws  \FuelException
	 */
	public static function __callStatic($method, array $arguments)
	{
		if ( ! in_array($method, array('factory', 'forge', 'instance', 'make')))
		{
			throw new \FuelException(__CLASS__.'::'.$method.'() does not exist.');
		}
		
		foreach (array(null, null) as $key => $default)
		{
			isset($arguments[$key]) or $arguments[$key] = $default;
		}

		list($name, $registry) = $arguments;

		$name = $name ?: 'default';

		if ( ! isset(static::$instances[$name]))
		{
			static::$instances[$name] = new static($name, $registry);
		}

		return static::$instances[$name];
	}

	/**
	 * Construct a new object.
	 *
	 * @access  protected
	 */
	protected function __construct($name = null, $registry = null) 
	{
		$this->name = $name;

		if ($registry instanceof Registry_Database)
		{
			$this->registry = $registry;

			foreach ($this->registry->get("acl_".$this->name, array()) as $role => $resources)
			{
				$this->add_roles($role);

				foreach ($resources as $name => $type)
				{
					$this->add_resources($name);

					$this->allow($role, $name, $type);
				}
			}
		}
	}

	protected $name = null;
	protected $registry = null;

	/**
	 * List of roles
	 * 
	 * @access  protected
	 * @var     array
	 */
	protected $roles = array('guest');
	 
	/**
	 * List of resources
	 * 
	 * @access  protected
	 * @var     array
	 */
	protected $resources = array();

	/**
	 * List of default actions
	 * 
	 * @access  protected
	 * @var     array
	 */
	protected $actions = array();
	 
	/**
	 * List of ACL map between roles, resources and types
	 * 
	 * @access  protected
	 * @var     array
	 */
	protected $acl = array();

	/**
	 * Verify whether current user has sufficient roles to access the resources based 
	 * on available type of access.
	 *
	 * @access  public
	 * @param   mixed   $resource   A string of resource name
	 * @param   string  $type       need to be any one of deny, view, create, edit, delete or all
	 * @return  bool
	 */
	public function access($resource, $type = 'view') 
	{
		$types = static::$types;

		if ( ! in_array($resource, $this->resources)) 
		{
			throw new \FuelException(__METHOD__.": Unable to verify unknown resource {$resource}.");
		}

		$user    = Auth::make('user')->get();
		
		$type_id = array_search($type, $types);
		$length  = count($types);

		if (empty($user->roles) and in_array('guest', $this->roles))
		{
			array_push($user->roles, 'guest');
		}

		foreach ($user->roles as $role) 
		{
			if ( ! isset($this->acl[$role.'/'.$resource])) 
			{
				continue;
			}

			if ($this->acl[$role.'/'.$resource] == $type) 
			{
				return true;
			}

			for ($i = ($type_id + 1); $i < $length; $i++) 
			{
				if ($this->acl[$role.'/'.$resource] == $types[$i]) 
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Verify whether current user has sufficient roles to access the resources based 
	 * on available type of access.
	 *
	 * @access  public
	 * @param   mixed   $resource   A string of resource name
	 * @param   string  $type       need to be any one of static::$type
	 * @return  int					http status
	 * @see     self::access()
	 */
	public function access_status($resource, $type = 'view') 
	{

		switch ($this->access($resource, $type)) 
		{
			case true :
				return 200;
			break;

			case false :
				return 401;
			break;
		}
	}

	/**
	 * Check if user has any of provided roles, deprecated and will be removed in v1.2.0
	 * 
	 * @deprecated  v1.2.0
	 * @static
	 * @access  public
	 * @param   mixed   $check_roles
	 * @return  bool 
	 */
	public static function has_roles($check_roles) 
	{
	   \Log::warning('This method is deprecated. Please use a \Hybrid\Auth::has_roles() instead.', __METHOD__);
		
		return Auth::has_roles($check_roles);
	}

	/**
	 * Add new user roles to the this instance
	 * 
	 * @access  public
	 * @param   mixed   $roles      A string or an array of roles
	 * @return  bool
	 * @throws  \FuelException
	 */
	public function add_roles($roles = null) 
	{
		if (null === $roles) 
		{
			throw new \FuelException(__METHOD__.": Can't add NULL roles.");
		}

		if (is_string($roles)) 
		{
			$roles = func_get_args();
		}
		
		if (is_array($roles)) 
		{
			foreach ($roles as $role)
			{
				$role = trim(\Inflector::friendly_title($role, '-', true));

				if ( ! in_array($role, $this->roles))
				{
					array_push($this->roles, $role);
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Add new resource to this instance
	 * 
	 * @access  public
	 * @param   mixed   $resources      A string of resource name
	 * @return  bool
	 * @throws  \FuelException
	 */
	public function add_resources($resources = null) 
	{
		if (null === $resources) 
		{
			throw new \FuelException(__METHOD__.": Can't add NULL resources.");
		}


		if ( ! is_array($resources)) 
		{
			$resources = func_get_args();
		}

		if (is_array($resources)) 
		{
			foreach ($resources as $resource => $action)
			{
				if (is_numeric($resource))
				{
					$resource = $action;
					$action   = null;
				}

				$resource = trim(\Inflector::friendly_title($resource, '-', true));
				
				if ( ! in_array($resource, $this->resources))
				{
					array_push($this->resources, $resource);
					$this->set_action(array("{$resource}" => $action));
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Add a callback action if a ACL return access to resource as unavailable
	 *
	 * @access  public
	 * @param   mixed   $resources      A string of resource name
	 * @param   mixed   $action			A closure or null
	 * @return  bool
	 */
	public function set_action($resources, $action = null)
	{
		if ( ! is_array($resources))
		{
			$resources = array("{$resources}" => $action);
		}

		if (is_array($resources))
		{
			foreach ($resources as $resource => $action)
			{
				if ( ! $action instanceof \Closure)
				{
					$action = null;
				}

				if (in_array($resource, $this->resources))
				{
					$this->actions[$resource] = $action;
				}
			}
			
			return true;
		}

		return false;
	}

	/**
	 * Remove a callback action
	 *
	 * @access  public
	 * @param   mixed     $resources    A string of resource name
	 * @return  bool
	 */
	public function delete_action($resources)
	{
		if ( ! is_array($resources))
		{
			$resources = array("{$resources}");
		}

		if (is_array($resources))
		{
			foreach ($resources as $resource)
			{
				if (in_array($resource, $this->resources))
				{
					$this->actions[$resource] = null;
				}
			}
			
			return true;
		}

		return false;
	}


	/**
	 * Unauthorized an action, this should be called from within a controller (included in all Hybrid\Controller classes).
	 *
	 * @access  public
	 * @param   string   $resource    A string of resource name
	 * @param   bool     $rest        Boolean value to define weither it's a restful call or a normal http call
	 * @return  Closure
	 * @throws  \FuelException
	 */
	public function unauthorized($resource, $rest = false)
	{
		\Lang::load('autho', 'autho');

		if ( ! array_key_exists($resource, $this->actions))
		{
			throw new \FuelException(__METHOD__.": Can't fetch NULL resources.");
		}

		$set_content_type = true;
		$action           = $this->actions[$resource];
		$response         = \Response::forge(\Lang::get('autho.unauthorized', array(), 'Unauthorized'), 401);

		// run the callback action
		if ($action instanceof \Closure and true !== $rest)
		{
			$callback = $action();

			if ($callback instanceof \Response)
			{
				$response = $callback;
			}
		}
		else
		{
			if ($rest === true)
			{
				if (true === \Request::is_hmvc())
				{
					$set_content_type = false;
				}
			}
			else 
			{
				throw new \HttpNotFoundException();
			}
		}

		\Event::shutdown();
		$response->send($set_content_type);
	}

	/**
	 * Assign single or multiple $roles + $resources to have $type access
	 * 
	 * @access  public
	 * @param   mixed   $roles          A string or an array of roles
	 * @param   mixed   $resources      A string or an array of resource name
	 * @param   string  $type
	 * @return  bool
	 * @throws  \FuelException
	 */
	public function allow($roles, $resources, $type = 'view') 
	{
		if ( ! in_array($type, static::$types)) 
		{
			throw new \FuelException(__METHOD__.": Type {$type} does not exist.");
		}

		if ( ! is_array($roles)) 
		{
			$roles = array($roles);
		}

		if ( ! is_array($resources)) 
		{
			$resources = array($resources);
		}

		foreach ($roles as $role) 
		{
			$role = \Inflector::friendly_title($role, '-', true);

			if ( ! in_array($role, $this->roles)) 
			{
				throw new \FuelException(__METHOD__.": Role {$role} does not exist.");

				continue;
			}

			foreach ($resources as $resource) 
			{
				$resource = \Inflector::friendly_title($resource, '-', true);

				if ( ! in_array($resource, $this->resources)) 
				{
					throw new \FuelException(__METHOD__.": Resource {$resource} does not exist.");

					continue;
				}

				$id = $role.'/'.$resource;

				$this->acl[$id] = $type;

				if ($this->registry instanceof Registry_Database)
				{
					$value = \Arr::merge(
						$this->registry->get("acl_".$this->name, array()), 
						array("{$role}" => array("{$resource}" => $type))
					);
					
					$this->registry->set("acl_".$this->name, $value);
				}
			}
		}

		return true;
	}

	/**
	 * Shorthand function to deny access for single or multiple $roles and $resouces
	 * 
	 * @access  public
	 * @param   mixed   $roles          A string or an array of roles
	 * @param   mixed   $resources      A string or an array of resource name
	 * @return  bool
	 */
	public function deny($roles, $resources) 
	{
		return $this->allow($roles, $resources, 'deny');
	}

}