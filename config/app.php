<?php

return array(
	// application identity is a unique string to be used in cookie and etc.
	'identity'				=> 'fuelapp',
	
	// set application name
	'site_name'				=> 'FuelPHP Application',
	
	// set application tagline
	'site_tagline'			=> 'Some fancy words',
	
	// set application into maintenance mode if value is set to true (default is false)
	'maintenance_mode' 		=> false,
	
	// set template file
	'template' 				=> array(
		'load_assets'			=> false,
		'default_filename'		=> 'index',
		'auto_encode'			=> false,
		'folders'				=> array(
			// you can set as many folder as possible
			'default' 				=> APPPATH . 'views/themes/default/',
			'frontend'				=> DOCROOT . 'themes/default/',
		)
	),
	
	// available language for this application
	'available_language' 	=> array(
		'en',
	),
	
	'user_table' 			=> array(
		'use_meta' 				=> true,
		'use_auth' 				=> true,
		'use_twitter'			=> false,
		'use_facebook' 			=> false,
	),

	'api' 					=> array(
		'twitter' 				=> array(
			'consumer_key' 			=> '',
			'consumer_secret' 		=> '',
		),
		'facebook' 			=> array(
			'app_id' 			=> '',
			'secret' 			=> '',
			'redirect_uri'		=> '',
			'scope'				=> '',
		),
		'_redirect' 		=> array(
			'registration' 		=> 'register',
			'after_login' 		=> '/',
			'after_logout' 		=> '/',
		),
	),
	
	'salt' 					=> 's8g5MgO5kVtEEmc_o0rP0UfI',
);<?php

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
abstract class Controller_Template extends \Fuel\Core\Controller {

	/**
	 * Page template
	 * 
	 * @access	public
	 * @var		string
	 */
	public $template = 'default';
	
	/**
	 * Auto render template
	 * 
	 * @access	public
	 * @var		bool	
	 */
	public $auto_render = true;

	/**
	 * Run ACL check and redirect user automatically if user doesn't have the privilege
	 * 
	 * @access	public
	 * @param	mixed	$resource
	 * @param	string	$type 
	 */
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

	/**
	 * This method will be called after we route to the destinated method
	 * 
	 * @access	public
	 */
	public function before() 
	{
		$this->language = \Hybrid\Factory::get_language();
		//$this->user = \Hybrid\Acl_User::get();

		\Event::trigger('controller_before');
		
		$this->_prepare_template();

		return parent::before();
	}

	/**
	 * This method will be called after we route to the destinated method
	 * 
	 * @access	public
	 */
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

		$this->template->set($data);
	}
	
	/**
	 * Prepare template
	 * 
	 * @access	protected
	 */
	protected function _prepare_template()
	{
		$this->template = \Hybrid\Template::factory($this->template);
	}
	
	/**
	 * Render template
	 * 
	 * @access	protected
	 */
	protected function _render_template()
	{
		//we dont want to accidentally change our site_name
		$this->template->set(array('site_name' => \Config::get('app.site_name')));
		
		if ($this->auto_render === true)
		{
			$this->response->body($this->template->render());
		}
	}

}