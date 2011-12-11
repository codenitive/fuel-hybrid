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
 * @category    Controller_Template
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Controller_Template extends \Fuel\Core\Controller 
{
	/**
	 * Page template
	 * 
	 * @access  public
	 * @var     string
	 */
	public $template = 'normal';
	
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
	 * @final
	 * @access  protected
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
				throw new \HttpNotFoundException();
			break;
		}
	}

	/**
	 * This method will be called after we route to the destinated method
	 * 
	 * @access  public
	 * @return  void
	 */
	public function before() 
	{
		$this->language = Factory::get_language();
		$this->user     = Auth::make('user')->get();

		\Event::trigger('controller_before');
		
		$this->prepare_template();

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

		return parent::after($this->render_template($response));
	}
	
	/**
	 * Takes pure data and optionally a status code, then creates the response
	 * 
	 * @access  protected
	 * @param   array       $data
	 * @param   int         $http_code
	 */
	protected function response($data = array(), $http_code = 200) 
	{
		$this->response->status = $http_code;

		$this->template->set($data);
	}
	
	/**
	 * Prepare template
	 * 
	 * @access  protected
	 * @return  void
	 */
	protected function prepare_template()
	{
		if (true === $this->auto_render)
		{
			$this->template = Template::make($this->template);
		}
	}
	
	/**
	 * Render template
	 * 
	 * @access  protected
	 * @param   mixed   $response
	 * @return  Response
	 */
	protected function render_template($response)
	{
		//we dont want to accidentally change our site_name
		$this->template->set(array('site_name' => \Config::get('app.site_name')));
		
		if (true === $this->auto_render and ! $response instanceof \Response)
		{
			$response       = $this->response;
			$response->body = $this->template->render();
		}

		return $response;
	}

}