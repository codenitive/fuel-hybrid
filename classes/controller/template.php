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
 * @category    Controller_Template
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
abstract class Controller_Template extends \Fuel\Core\Controller_Template {

	public $template = 'themes/default';

	final protected function _acl($resource, $type = null) 
	{
		$status = \Hybrid\Acl::access_status($resource, $type);

		switch ($status) 
		{
			case 401 :
				\Request::show_404();
			break;
		}
	}

	public function before() 
	{
		$this->language = \Hybrid\Factory::get_language();
		$this->user = \Hybrid\Acl_User::get();

		\Event::trigger('controller_before');
		
		$this->_prepare_template();

		return parent::before();
	}

	public function after() 
	{
		\Event::trigger('controller_after');

		$this->_render_template();

		return parent::after();
	}
	
	/**
	 * Takes pure data and optionally a status code, then creates the response
	 * 
	 * @param	array		$data
	 * @param	int			$http_code
	 */
	protected function response($data = array(), $http_code = 200) 
	{
		$this->response->status = $http_code;

		if (is_array($data) && count($data) > 0)
		{
			foreach ($data as $key => $value)
			{
				$this->template->set($key, $value);
			}
		}
	}
	
	protected function _prepare_template()
	{
		$file = \Config::get('app.template');

		if (is_file(APPPATH . 'views/themes/' . $file . '.php')) 
		{
			$this->template = 'themes/' . $file;
		}
	}
	
	protected function _render_template()
	{
		//we dont want to accidentally change our site_name
		$this->template->site_name = \Config::get('app.site_name');
	}

}