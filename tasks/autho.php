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

use Oil\Exception;
use Oil\Generate;

/**
 * Setup commandline:
 *      php oil refine autho
 *
 * @package  hybrid
 */
class Autho 
{
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
		\Package::load('orm');
		\Config::load('autho', 'autho');
		\Config::load('hybrid', 'hybrid');
		
		$has_error = false;

		if (false === \Config::get('autho.normal', false))
		{
			\Cli::write('Please update your APPPATH/config/autho.php, you\'re running on an outdated configuration', 'red');
			$has_error = true;
		}

		$class_name = \Inflector::classify(\Config::get('hybrid.tables.users.meta', 'users_meta'), true);

		if ((true === class_exists("\Model_{$class_name}") or true === class_exists("\Model\{$class_name}")) and false === \Config::get('autho.normal.use_meta', false))
		{
			\Cli::write('Please set autho.normal.use_meta to TRUE in APPPATH/config/autho.php', 'red');
			$has_error = true;
		}

		$class_name = \Inflector::classify(\Config::get('hybrid.tables.users.auth', 'users_auths'), true);

		if ((true === class_exists("\Model_{$class_name}") or true === class_exists("\Model\{$class_name}")) and false === \Config::get('autho.normal.use_auth', false))
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
	public static function install($install = null)
	{
		\Package::load('orm');
		\Config::load('autho', 'autho');
		\Config::load('hybrid', 'hybrid');

		\Cli::write("Start Installation", "green");

		if (true === $install or 'all' === $install)
		{
			$install = array('config', 'user', 'role', 'social');
		}
		else {
			$install = array($install);
		}

		if (in_array('config', $install))
		{
			static::install_config('autho');
			static::install_config('app');
		}

		if (in_array('user', $install))
		{
			static::install_user();
		}

		if (in_array('role', $install))
		{
			static::install_role();
		}

		if (in_array('social', $install))
		{
			static::install_social();
		}

		static::execute();
	}

	protected static $queries = array();

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
					throw new Exception("APPPATH/config/{$file}.php could not be written.");
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
		$class_name = \Inflector::classify(\Config::get('hybrid.tables.users.user', 'users'), true);

		if (true === class_exists("\Model_{$class_name}") or true === class_exists("\Model\{$class_name}"))
		{
			throw new Exception("Model {$class_name} already exist, skipping this process");
		}

		$user_model = array(
			\Inflector::singularize(\Config::get('hybrid.tables.users.user', 'users')),
			'user_name:string[100]',
			'full_name:string[200]',
			'email:string[150]',
		);

		$auth_model = array();
		$meta_model = array();

		if ('y' === \Cli::prompt("Would you like to install `user.auth` table?", array('y', 'n')))
		{
			$auth_model[] = \Inflector::singularize(\Config::get('hybrid.tables.users.auth', 'users_auths'));
			$auth_model[] = \Inflector::singularize(\Config::get('hybrid.tables.users.user', 'users')).'_id:int';
			$auth_model[] = 'password:string[50]';
		}
		else
		{
			$user_model[] = 'password:string[50]';
		}

		if ('y' === \Cli::prompt("Would you like to install `user.meta` table?", array('y', 'n')))
		{
			$meta_model[] = \Inflector::singularize(\Config::get('hybrid.tables.users.meta', 'users_meta'));
			$meta_model[] = \Inflector::singularize(\Config::get('hybrid.tables.users.user', 'users')).'_id:int';
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
		$class_name = \Inflector::classify(\Config::get('hybrid.tables.group', 'roles'), true);

		if (true === class_exists("\Model_{$class_name}") or true === class_exists("\Model\{$class_name}"))
		{
			throw new Exception("Model {$class_name} already exist, skipping this process");
		}

		if ('y' === \Cli::prompt("Would you like to install `group` table?", array('y', 'n')))
		{
			static::queue(array(
				\Inflector::singularize(\Config::get('hybrid.tables.group', 'roles')),
				'name:string',
				'active:tinyint[1]',
			));

			static::queue(array(
				\Inflector::singularize(\Config::get('hybrid.tables.users.group', 'users_roles')),
				\Inflector::singularize(\Config::get('hybrid.tables.users.user', 'users')).'_id:int',
				\Inflector::singularize(\Config::get('hybrid.tables.group', 'roles')).'_id:int',
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
	protected static function install_social()
	{
		$class_name = \Inflector::classify(\Config::get('hybrid.tables.social', 'authentications'), true);

		if (true === class_exists("\Model_{$class_name}") or true === class_exists("\Model\{$class_name}"))
		{
			throw new Exception("Model {$class_name} already exist, skipping this process");
		}

		if ('y' === \Cli::prompt("Would you like to install `social` table?", array('y', 'n')))
		{
			static::queue(array(
				\Inflector::singularize(\Config::get('hybrid.tables.social', 'authentications')),
				\Inflector::singularize(\Config::get('hybrid.tables.users.user', 'users')).'_id:int',
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
			$name = array_shift($data);

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
		if (empty(static::$queries))
		{
			\Cli::write("Nothing to generate", "red");
		}
		elseif ('y' === \Cli::prompt("Confirm Generate Model and Migration?", array('y', 'n')))
		{
			foreach (static::$queries as $data)
			{
				Generate::model($data);
				Generate::$create_files = array();
			}
		}
	}
		
}