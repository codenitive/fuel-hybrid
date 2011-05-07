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
 * @category    Controller_Frontend
 * @abstract
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
abstract class Controller_Frontend extends \Hybrid\Controller {

	/**
	 * Page template
	 * 
	 * @access	public
	 * @var		string
	 */
	public $template = null;
	
	/**
	 * Auto render template
	 * 
	 * @access	public
	 * @var		bool	
	 */
	public $auto_render = true;

	/**
	 * This method will be called after we route to the destinated method
	 * 
	 * @access	public
	 */
	public function before() 
	{
		$this->_prepare_template();

		return parent::before();
	}
	
	/**
	 * Takes pure data and optionally a status code, then creates the response
	 * 
	 * @param	array	$data
	 * @param	int		$http_code
	 */
	protected function response($data = array(), $http_code = 200) 
	{
		$this->response->status = $http_code;

		if (is_array($data) and count($data) > 0)
		{
			foreach ($data as $key => $value)
			{
				$this->template->set($key, $value);
			}
		}
	}

	/**
	 * This method will be called after we route to the destinated method
	 * 
	 * @access	public
	 */
	public function after() 
	{
		$this->_render_template();
		
		return parent::after();
	}
	
	/**
	 * Prepare template
	 * 
	 * @access	protected
	 */
	protected function _prepare_template()
	{
		$theme_path = \Config::get('app.frontend.template');

		if (null === $theme_path) 
		{
			$theme_path = DOCROOT . 'themes/default/';
			\Config::set('app.frontend.template', $theme_path);
		}

		\Hybrid\View::set_path($theme_path);

		\Asset::add_path($theme_path . 'assets/');

		if (true === $this->auto_render) 
		{
			$this->template = \Hybrid\View::factory();
			$this->template->auto_encode(false);
			$this->template->set_filename('index');
		}
	}
	
	/**
	 * Render template
	 * 
	 * @access	protected
	 */
	protected function _render_template()
	{
		//we dont want to accidentally change our site_name
		$this->template->site_name = \Config::get('app.site_name');
		
		if ($this->auto_render === true) 
		{
			$this->response->body($this->template->render());
		}
	}

}