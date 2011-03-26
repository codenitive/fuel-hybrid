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
 * @category    Controller_Frontend
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
abstract class Controller_Frontend extends \Hybrid\Controller {

	/**
	 * @var string page template
	 */
	public $template = null;
	/**
	 * @var boolean auto render template
	 * */
	public $auto_render = true;

	public function before() {
		$theme_path = \Config::get('app.frontend.template');

		if (null === $theme_path) {
			$theme_path = DOCROOT . 'themes/default/';
			\Config::set('app.frontend.template', $theme_path);
		}

		\Hybrid\View::set_path($theme_path);

		\Asset::add_path($theme_path . 'assets/');

		if (true === $this->auto_render) {
			$this->template = \Hybrid\View::factory();
			$this->template->auto_encode(false);
			$this->template->set_filename('index');
		}

		return parent::before();
	}

	public function after() {
		if ($this->auto_render === true) {
			$this->output = $this->template->render();
		}

		return parent::after();
	}

}