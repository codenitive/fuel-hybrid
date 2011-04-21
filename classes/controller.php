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
 * @category    Controller
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
abstract class Controller extends \Fuel\Core\Controller {

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

		return parent::before();
	}

	public function after() 
	{
		\Event::trigger('controller_after');

		return parent::after();
	}

}