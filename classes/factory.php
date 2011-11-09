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
 * @category    Factory
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Factory 
{
	private static $identity = null;
	private static $language = 'en';

	/**
	 * Initiate application configuration
	 * 
	 * @static
	 * @access  public
	 */
	public static function _init() 
	{
		// initiate this only once
		if (null !== static::$identity) 
		{
			return;
		}
		
		\Config::load('app', 'app');
		\Config::load('hybrid', 'hybrid');

		static::$identity = \Config::get('app.identity');

		if (\Fuel::$env !== \Fuel::PRODUCTION and true === \Config::get('hybrid.profiling', false))
		{
			static::profiling();
		}

		if (\Config::get('app.maintenance_mode') == true) 
		{
			static::maintenance_mode();
		}

		$lang = \Session::get(static::$identity.'_lang');

		if (null !== $lang) 
		{
			\Config::set('language', $lang);
			static::$language = $lang;
		} 
		else 
		{
			static::$language = \Config::get('language');
		}

		\Event::trigger('load_language');
		\Event::trigger('load_acl');
	}

	/**
	 * Check for maintenance mode
	 * 
	 * @static
	 * @access  protected
	 * @throws  \FuelException
	 */
	protected static function maintenance_mode() 
	{
		// This ensures that show_404 is only called once.
		static $call_count = 0;
		$call_count++;

		if ($call_count > 1) 
		{
			throw new \FuelException('It appears your _maintenance_mode_ route is incorrect.  Multiple Recursion has happened.');
		}

		if (\Config::get('routes._maintenance_mode_') === null) 
		{
			throw new \FuelException('It appears your _maintenance_mode_ route is null.');
		} 
		else 
		{
			$request = \Request::forge(\Config::get('routes._maintenance_mode_'))->execute();
			$response = $request->response();
			$response->send(true);
			\Event::shutdown();
			exit();
		}
	}

	/**
	 * Get application codename
	 *
	 * @static
	 * @access  public
	 * @return  string
	 */
	public static function get_identity() 
	{
		return static::$identity;
	}

	/**
	 * Get application language setup
	 *
	 * @static
	 * @access  public
	 * @return  string
	 */
	public static function get_language() 
	{
		return static::$language;
	}

	/**
	 * Import file by look inside current hybrid folder instead of import() unusable structure.
	 *
	 * @static
	 * @access  public
	 * @param   string  $path
	 * @param   string  $folder
	 * @return  void
	 */
	public static function import($path, $folder = 'classes')
	{
		$dir_path = __DIR__.'/../';
		$path     = str_replace('/', DIRECTORY_SEPARATOR, $path);
		require_once $dir_path.$folder.DIRECTORY_SEPARATOR.$path.'.php';
	}

	/**
	 * Dynamically load profiling using $_GET['profiler'] = 1;
	 *
	 * @static
	 * @access  protected
	 * @return  void
	 */
	protected static function profiling()
	{
		$profiler     = \Session::get('_profiler', \Config::get('profiling', false));
		$get_profiler = \Input::get('profiler', null);

		if (null !== $get_profiler)
		{
			$profiler = (int) $get_profiler === 1 ? true : false;
		}

		switch ($profiler)
		{
			case 1 :
				\Fuel::$profiling = true;
				\Profiler::init();
			break;
		}

		\Session::set('_profiler', $profiler);
	}
	
}