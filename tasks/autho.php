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

namespace Fuel\Tasks;

/**
 * Setup commandline:
 *      php oil refine autho
 *
 * @package  hybrid
 */
class Autho {

	/**
	 * Send command with runtime option:
	 *      php oil refine autho --install
	 *      php oil refine autho --help
	 *      php oil refine autho --test
	 *
	 * @static
	 * @access  public
	 * @return  void
	 */
	public static function run()
	{
		$install = \Cli::option('i') or \Cli::option('install');
		$help    = \Cli::option('h') or \Cli::option('help');
		$test    = \Cli::option('t') or \Cli::option('test');

		switch (true)
		{
			case $install :
				static::install();
			break;

			case $test :
				static::test();
			break;

			case $help :
			default :
				static::help();
			break;
		}
	}

	/**
	 * Test Hybrid package installation, make sure app/config/app.php is using the right configuration
	 *
	 * @static
	 * @access  public
	 * @return  void
	 */
	public static function test()
	{
		\Config::load('autho', 'autho');
		
		$has_error = false;

		if (false === \Config::get('autho.normal', false))
		{
			\Cli::write('Please update your APPPATH/config/autho.php, you\'re running on an outdated configuration', 'red');
			$has_error = true;
		}

		if ((true === class_exists("\Model_Users_Metum") or true === class_exists("\Model\Users_Metum")) and false === \Config::get('autho.normal.use_meta', false))
		{
			\Cli::write('Please set autho.normal.use_meta to TRUE in APPPATH/config/autho.php', 'red');
			$has_error = true;
		}

		if ((true === class_exists("\Model_Users_Auth") or true === class_exists("\Model\Users_Auth")) and false === \Config::get('autho.normal.use_auth', false))
		{
			\Cli::write('Please set app.auth.use_auth to TRUE in APPPATH/config/autho.php', 'red');
			$has_error = true;
		}

		if (null === \Config::get('autho.salt'))
		{
			\Cli::write('Please provide autho.salt secret key in APPPATH/config/autho.php', 'red');
			$has_error = true;
		}

		if (false === $has_error)
		{
			\Cli::write('Your application is correctly configured', 'green');
		}
	}

	/**
	 * Show help menu
	 *
	 * @static
	 * @access  public
	 * @return  void
	 */
	public static function help()
	{
		echo <<<HELP

Usage:
	php oil refine autho

Runtime options:
	-h, [--help]      # Show option
	-i, [--install]   # Install configuration file and user model/migrations script
	-t, [--test]      # Test installation to match configuration

Description:
	The 'oil refine autho' command can be used in several ways to facilitate quick development, help with
	user database generation and installation

HELP;

	}

	/**
	 * Run all installation
	 *
	 * @static
	 * @access  public
	 * @return  void
	 */
	public static function install()
	{
		\Cli::write("Start Installation", "green");

		static::install_config('autho');
		static::install_config('app');
		static::install_user();
	}
	/**
	 * Install configuration file
	 *
	 * @static
	 * @access  protected
	 * @return  void
	 */
	protected static function install_config($file = 'autho')
	{
		$path = APPPATH.'config'.DS.$file.'.php';

		$content = file_get_contents(PKGPATH.'hybrid/config/'.$file.'.php');

		switch(true)
		{
			case (true === is_file($path) and 'y' === \Cli::prompt("Overwrite APPPATH/config/{$file}.php?", array('y', 'n'))) :
			case (false === is_file($path)) : 
				 $path = pathinfo($path);

				try
				{
					\File::update($path['dirname'], $path['basename'], $content);
					\Cli::write("Created config: APPPATH/config/{$file}.php", 'green');
				}
				catch (\File_Exception $e)
				{
					throw new \FuelException("APPPATH/config/{$file}.php could not be written.");
				}
			break;

			default :
			
			break;
		}
	}

	/**
	 * Install user table
	 *
	 * @static
	 * @access  protected
	 * @return  void
	 */
	protected static function install_user()
	{
		if (true === class_exists("\Model_User") or true === class_exists("\Model\User"))
		{
			throw new \FuelException("Model User already exist, skipping this process");
		}

		$user_model = array(
			'user',
			'user_name:string[100]',
			'full_name:string[200]',
			'email:string[150]',
		);

		$auth_model = array();
		$meta_model = array();

		if ('y' === \Cli::prompt("Would you like to install `users_auth` table?", array('y', 'n')))
		{
			$auth_model[] = 'users_auth';
			$auth_model[] = 'user_id:int';
			$auth_model[] = 'password:string[50]';
		}
		else
		{
			$user_model[] = 'password:string[50]';
		}

		if ('y' === \Cli::prompt("Would you like to install `users_meta` table?", array('y', 'n')))
		{
			$meta_model[] = 'users_metum';
			$meta_model[] = 'user_id:int';
		}

		$user_model[] = 'status:enum[unverified,verified,banned,deleted]';

		if ('y' === \Cli::prompt("Confirm Generate Model and Migration for User?", array('y', 'n')))
		{

			\Oil\Generate::model($user_model);
			\Oil\Generate::$create_files = array();

			if (!empty($auth_model))
			{
				\Oil\Generate::model($auth_model);
				\Oil\Generate::$create_files = array();
			}

			if (!empty($meta_model))
			{
				\Oil\Generate::model($meta_model);
				\Oil\Generate::$create_files = array();
			}

			\Oil\Generate::model(array(
				'role',
				'name:string',
				'active:tinyint[1]',
			));
			\Oil\Generate::$create_files = array();

			\Oil\Generate::model(array(
				'users_role',
				'user_id:int',
				'role_id:int',
			));
			\Oil\Generate::$create_files = array();

			\Oil\Generate::model(array(
				'authentication',
				'user_id:int',
				'provider:string[50]',
				'uid:string',
				'access_token:string',
				'expires:int[12]',
				'refresh_token:string',
				'secret:string',
			));
			\Oil\Generate::$create_files = array();
		}
	}
		
}