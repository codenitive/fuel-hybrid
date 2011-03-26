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
 * @category    Factory
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
class Factory {

	private static $_identity = null;
	private static $_language = 'en';

	/**
	 * Initiate application configuration
	 * 
	 * @access public
	 */
	public static function _init() {
		if (is_null(static::$_identity)) {
			\Config::load('app', true);

			static::$_identity = \Config::get('app.identity');

			if (\Config::get('app.maintenance_mode') == true) {
				static::_maintenance_mode();
			}

			$lang = \Session::get(static::$_identity . '_lang');

			if (!is_null($lang)) {
				\Config::set('language', $lang);
				static::$_language = $lang;
			} else {
				static::$_language = \Config::get('language');
			}

			\Event::trigger('load_language');
			\Event::trigger('load_acl');
		}
	}

	private static function _maintenance_mode() {
		// This ensures that show_404 is only called once.
		static $call_count = 0;
		$call_count++;

		if ($call_count > 1) {
			throw new \Fuel_Exception('It appears your _maintenance_mode_ route is incorrect.  Multiple Recursion has happened.');
		}


		if (\Config::get('routes._maintenance_mode_') === null) {
			throw new \Fuel_Exception('It appears your _maintenance_mode_ route is null.');
		} else {
			$request = \Request::factory(\Config::get('routes._maintenance_mode_'))->execute();
			exit($request->send_headers()->response());
		}
	}

	/**
	 * Get application codename
	 *
	 * @access public
	 * @return string
	 */
	public static function get_identity() {
		return static::$_identity;
	}

	public static function get_language() {
		return static::$_language;
	}

	public static function view($file, $data = null, $encode = null) {
		return \View::factory(static::$_language . DS . $file, $data, $encode);
	}

}
