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

use \Fuel\Core\HttpNotFoundException;

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
 * @category    Acl
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class AclUnauthorizedException extends HttpNotFoundException
{
	protected $instance = null;

	protected $resource = null;

	protected $rest  = false;

	public function __construct($acl, $resource = null, $rest = false)
	{
		if ($acl instanceof Acl)
		{
			$this->instance = $acl;
		}
		elseif (is_string($acl))
		{
			$this->instance = Acl::make($acl);
		}

		$this->resource = $resource;

		if (true === $rest)
		{
			$this->rest = true;
		}

		parent::__construct('Unauthorized', 401);
	}
	/**
	 * When this type of exception isn't caught this method is called by
	 * Error::exception_handler() to deal with the problem.
	 */
	public function handle()
	{
		$action           = null;
		$set_content_type = true;

		if (null !== $this->instance)
		{
			$action = $this->instance->action($this->resource);
		}

		if (true === $this->rest)
		{
			if (true === \Request::is_hmvc())
			{
				$set_content_type = false;
			}

			\Lang::load('autho', 'autho');
			$this->response(array('text' => \Lang::get('autho.unauthorized')), 401);
		}
		else
		{
			if ($action instanceof \Closure)
			{
				$action();
			}
			else
			{
				throw new \HttpNotFoundException();
			}
		}

		\Event::shutdown();
		$response->send(true);
	}
}

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
	 * Shortcode to self::make().
	 *
	 * @deprecated  1.2.0
	 * @static
	 * @access  public
	 * @param   string  $name
	 * @return  object  Acl
	 * @see     self::make()
	 */
	public static function factory($name = null)
	{
		\Log::warning('This method is deprecated. Please use a make() instead.', __METHOD__);
		
		return static::make($name);
	}
	
	/**
	 * Shortcode to self::make().
	 * 
	 * @static
	 * @access  public
	 * @param   string  $name
	 * @return  self::make()
	 */
	public static function forge($name = null)
	{
		return static::make($name);
	}

	/**
	 * Get cached instance, or generate new if currently not available.
	 *
	 * @deprecated  1.2.0
	 * @static
	 * @access  public
	 * @param   string  $name
	 * @return  object  Acl
	 * @see     self::make()
	 */
	public static function instance($name = null)
	{
		\Log::warning('This method is deprecated. Please use a make() instead.', __METHOD__);
		
		return static::make($name);
	}

	/**
	 * Initiate a new Acl instance.
	 * 
	 * @static
	 * @access  public
	 * @param   string  $name
	 * @return  Acl
	 */
	public static function make($name = null)
	{
		if (null === $name)
		{
			$name = 'default';
		}

		if ( ! isset(static::$instances[$name]))
		{
			static::$instances[$name] = new static();
		}

		return static::$instances[$name];
	}

	/**
	 * Construct a new object.
	 *
	 * @access  protected
	 */
	protected function __construct() {}

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
	 * @param   mixed   $resource
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
	 * @param   mixed   $resource
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
	   return Auth::has_roles($check_roles);
	}

	/**
	 * Add new user roles to the this instance
	 * 
	 * @access  public
	 * @param   mixed   $roles
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
	 * @param   mixed   $resources
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
					$this->add_action(array("{$resource}" => $action));
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Add an action (or callback) if a ACL return access to resource as unavailable
	 *
	 * @access  public
	 * @param   mixed     $resources
	 * @param   \Closure  $action
	 * @return  bool
	 */
	public function add_action($resources, $action = null)
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
					$this->actions[$resource] = null;
				}
			}
			
			return true;
		}

		return false;
	}

	/**
	 * Return available action for a resource, default to null
	 *
	 * @access  public
	 * @param   string   $resource
	 * @return  Closure
	 * @throws  \FuelException
	 */
	public function action($resource)
	{
		if ( ! array_key_exists($resource, $this->actions))
		{
			throw new \FuelException(__METHOD__.": Can't fetch NULL resources.");
		}

		return $this->actions[$resource];
	}

	/**
	 * Assign single or multiple $roles + $resources to have $type access
	 * 
	 * @access  public
	 * @param   mixed   $roles
	 * @param   mixed   $resources
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
			}
		}

		return true;
	}

	/**
	 * Shorthand function to deny access for single or multiple $roles and $resouces
	 * 
	 * @access  public
	 * @param   mixed   $roles
	 * @param   mixed   $resources
	 * @return  bool
	 */
	public function deny($roles, $resources) 
	{
		return $this->allow($roles, $resources, 'deny');
	}

}