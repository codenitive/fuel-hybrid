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
 * @category    Controller
 * @abstract
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Controller extends \Fuel\Core\Controller 
{
	/**
	 * Set whether the request is either rest or template
	 * 
	 * @access  protected
	 * @var     bool
	 */
	protected $is_rest_call = true;

	/**
	 * Rest format to be used
	 * 
	 * @access  protected
	 * @var     string
	 */
	protected $rest_format = null;
	
	/**
	 * Set the default content type using PHP Header
	 * 
	 * @access  protected
	 * @var     bool
	 */
	protected $set_content_type = true;
	
	/**
	 * Page template
	 * 
	 * @access  public
	 * @var     string
	 */
	public $template = null;
	
	/**
	 * Auto render template
	 * 
	 * @access  public
	 * @var     bool    
	 */
	public $auto_render = true;

	/**
	 * Run ACL check and redirect user automatically if user doesn't have the privilege
	 * 
	 * @access  public
	 * @param   mixed   $resource
	 * @param   string  $type 
	 * @param   string  $name
	 * @throws  HttpNotFoundException
	 */
	final protected function acl($resource, $type = null, $name = null) 
	{
		$status = Acl::make($name)->access_status($resource, $type);
		
		switch ($status) 
		{
			case 401 :
				if (true === $this->is_rest_call)
				{
					\Lang::load('autho', 'autho');
					$this->response(array('text' => \Lang::get('autho.no_privilege')), 401);
					$this->response->send($this->set_content_type);
					\Event::shutdown();
					exit();
				}
				else
				{
					throw new \HttpNotFoundException();
				}
			break;
		}
	}

	/**
	 * This method will be called before we route to the destinated method
	 * 
	 * @access  public
	 * @return  void
	 */
	public function before() 
	{
		$this->is_rest_call = Restserver::is_rest_call();
		$this->language     = Factory::get_language();
		$this->user         = Auth::make('user')->get();

		if (true === $this->is_rest_call)
		{
			\Fuel::$profiling = false;
		}

		\Event::trigger('controller_before');

		if (false === $this->is_rest_call)
		{
			$this->prepare_template();
		}
		else 
		{
			$this->prepare_rest();
		}

		return parent::before();
	}

	/**
	 * This method will be called after we route to the destinated method
	 * 
	 * @access  public
	 * @param   mixed   $response
	 * @return  Response
	 */
	public function after($response) 
	{
		\Event::trigger('controller_after');
		
		if (false === $this->is_rest_call)
		{
			$response = $this->render_template($response);
		}
		else 
		{
			$response = $this->render_rest($response);
		}

		return parent::after($response);
	}

	/**
	 * Requests are not made to methods directly The request will be for an "object".
	 * this simply maps the object and method to the correct Controller method.
	 * 
	 * @access  public
	 * @param   Request $resource
	 * @param   array   $arguments
	 * @return  void
	 */
	public function router($resource, $arguments) 
	{
		$pattern           = Restserver::$pattern;
		
		// Remove the extension from arguments too
		$resource          = preg_replace($pattern, '', $resource);
		
		// If they call user, go to $this->post_user();
		$controller_method = strtolower(Input::method()).'_'.$resource;
		
		if (method_exists($this, $controller_method) and true === $this->is_rest_call) 
		{
			call_user_func(array($this, $controller_method));
		}
		elseif (method_exists($this, 'action_'.$resource)) 
		{
			if (true === $this->is_rest_call)
			{
				$this->response->status = 404;
				return;
			}

			call_user_func_array(array($this, 'action_'.$resource), $arguments);
		}
		else 
		{
			if (true === $this->is_rest_call)
			{
				$this->response->status = 404;
				return;
			}
			else
			{
				throw new \HttpNotFoundException();
			}
		}
	}

	/**
	 * Takes pure data and optionally a status code, then creates the response
	 * 
	 * @access  protected
	 * @param   array   $data
	 * @param   int     $http_code
	 * @return  void
	 */
	protected function response($data = array(), $http_code = 200) 
	{
		if (true === $this->is_rest_call)
		{
			$rest_server = Restserver::make($data, $http_code)
				->format($this->rest_format)
				->execute();
			
			$this->response->body   = $rest_server->body;
			$this->response->status = $rest_server->status;

			if (true === $this->set_content_type) 
			{
				// Set the correct format header
				$this->response->set_header('Content-Type', Restserver::content_type($rest_server->format));
			}
		}
		else 
		{
			$this->response->status = $http_code;
			
			if (null !== $this->template)
			{
				$this->template->set($data);
			}
			else 
			{
				$this->response->body = $data;
			}
		}
	}
	
	/**
	 * Prepare template
	 * 
	 * @access  protected
	 */
	protected function prepare_template()
	{
		if (true === $this->auto_render)
		{
			if (null !== $this->template)
			{
				$this->template = Template::make($this->template);
			}
		}
	}
	
	/**
	 * Render the template
	 * 
	 * @access  protected
	 * @param   mixed   $response
	 */
	protected function render_template($response)
	{
		if (null !== $this->template)
		{
			//we dont want to accidentally change our site_name
			$this->template->set(array('site_name' => \Config::get('app.site_name')));
		}

		if (true === $this->auto_render and ! $response instanceof \Response)
		{
			$response = $this->response;

			if (null !== $this->template)
			{
				$response->body = $this->template->render();
			}
		}

		return $response;
	}
	
	/**
	 * Prepare Rest request
	 * 
	 * @access  protected
	 */
	protected function prepare_rest()
	{
		if (Request::main() !== Request::active()) 
		{
			$this->set_content_type = false;
		}

		$this->template = null;

		Restserver::auth();
	}
	
	/**
	 * Render Rest request
	 * 
	 * @access  protected
	 * @param   mixed   $response
	 */
	protected function render_rest($response) 
	{
		if ( ! $response instanceof \Response)
		{
			$response = $this->response;    
		}

		return $response;
	}
	
}