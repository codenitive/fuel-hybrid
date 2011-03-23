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

		if ($this->auto_render === true) {
			$this->template = \Hybrid\View::factory();
		}

		\Asset::add_path($theme_path . 'assets/');

		$this->template->set_filename('index');

		return parent::before();
	}

	public function after() {
		if ($this->auto_render === true) {
			$this->output = $this->template->render();
		}

		return parent::after();
	}

}