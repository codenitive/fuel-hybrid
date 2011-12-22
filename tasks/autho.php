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

use Oil\Generate;

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
		$install = \Cli::option('i', \Cli::option('install'));
		$help    = \Cli::option('h', \Cli::option('help'));
		$test    = \Cli::option('t', \Cli::option('test'));

		switch (true)
		{
			case null !== $install :
				static::install($install);
			break;

			case null !== $test :
				static::test();
			break;

			case null !== $help :
			default :
				static::help();
			break;
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
	 * Run all installation
	 *
	 * @static
	 * @access  public
	 * @return  void
	 */
	public static function install($install = true)
	{
		\Cli::write("Start Installation", "green");

		if (in_array($install, array(true, 'config')))
		{
			static::install_config('autho');
			static::install_config('app');
		}

		if (in_array($install, array(true, 'user')))
		{
			static::install_user();
		}

		if (in_array($install, array(true, 'role')))
		{
			static::install_role();
		}

		if (in_array($install, array(true, 'authentication')))
		{
			static::install_authentication();
		}
	}

	protected static $query = array();

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
	 * Install users table
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

		static::queue($user_model);

		static::queue($auth_model);

		static::queue($meta_model);
	}

	/**
	 * Install roles related table
	 *
	 * @static
	 * @access  protected
	 * @return  void
	 */
	protected static function install_role()
	{
		if ('y' === \Cli::prompt("Would you like to install `roles` table?", array('y', 'n')))
		{
			static::queue(array(
				'role',
				'name:string',
				'active:tinyint[1]',
			));

			static::queue(array(
				'users_role',
				'user_id:int',
				'role_id:int',
			));
		}
	}

	/**
	 * Install authentications table
	 *
	 * @static
	 * @access  protected
	 * @return  void
	 */
	protected static function install_authentication()
	{
		if (true === class_exists("\Model_Authentication") or true === class_exists("\Model\Authentication"))
		{
			throw new \FuelException("Model Authentication already exist, skipping this process");
		}

		if ('y' === \Cli::prompt("Would you like to install `authentications` table?", array('y', 'n')))
		{
			static::queue(array(
				'authentication',
				'user_id:int',
				'provider:string[50]',
				'uid:string',
				'access_token:string:null',
				'expires:int[12]:null',
				'refresh_token:string:null',
				'secret:string:null',
			));
		}
	}

	/**
	 * Add migration script to queue
	 *
	 * @static
	 * @access  protected
	 * @param   array      $data 
	 * @return  void
	 */
	protected static function queue($data)
	{
		if ( ! empty($data))
		{
			array_push(static::$queries, $data);
			$name = array_unshift($data);

			\Cli::write("Add script for {$name}", 'green');
		}
	}

	/**
	 * Execute all available migration
	 *
	 * @static
	 * @access  protected
	 * @return  void
	 */
	protected static function execute()
	{
		if ('y' === \Cli::prompt("Confirm Generate Model and Migration?", array('y', 'n')))
		{

			foreach (static::$queries as $data)
			{
				Generate::model($data);
				Generate::$create_files = array();
			}
		}
	}
		
}