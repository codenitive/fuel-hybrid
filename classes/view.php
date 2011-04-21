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
 * @category    View
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
class View extends \Fuel\Core\View {

	protected static $_path = '';

	/**
	 * Set the global path.
	 * 
	 *     \Hybrid\View::set_path($path);
	 * 
	 * @param string $path 
	 */
	public static function set_path($path) 
	{
		static::$_path = $path;
	}

	/**
	 * Sets the view filename.
	 *
	 *     $view->set_filename($file);
	 *
	 * @param   string  view filename
	 * @return  View
	 * @throws  View_Exception
	 */
	public function set_filename($file) 
	{
		switch (true) 
		{
			case ($path = $this->_find_file($file)) :
				break;
			case ($path = \Fuel::find_file('views', $file, '.php', false, false)) :
				break;
			default :
				throw new \View_Exception('The requested view could not be found: ' . \Fuel::clean_path($file));
		}

		// Store the file path locally
		$this->_file = $path;

		return $this;
	}

	/**
	 * Use custom view path if available, eitherwise just return false so we can use 
	 * \Fuel::find_file()
	 *
	 * @param string $file
	 * @return mixed
	 */
	private function _find_file($file) 
	{
		if (empty(static::$_path) || !\is_file(static::$_path . $file . '.php'))
		{
			return null;
		}
 
		return static::$_path . $file . '.php';
	}

}