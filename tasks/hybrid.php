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
 *      php oil refine hybrid
 *
 * @package  hybrid
 */
class Hybrid {

    /**
     * Send command with runtime option:
     *      php oil refine hybrid --install
     *      php oil refine hybrid --help
     *      php oil refine hybrid --test
     *
     * @static
     * @access  public
     * @return  void
     */
    public static function run()
    {
        $install    = \Cli::option('i') or \Cli::option('install');
        $help       = \Cli::option('h') or \Cli::option('help');
        $test       = \Cli::option('t') or \Cli::option('test');

        switch (true)
        {
            case $install :
                static::install();
            break;

            case $help :
                static::help();
            break;

            case $test :
                static::test();
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
        \Config::load('app', 'app');
        
        $has_error = false;

        if (true === \class_exists('\\Model_Users_Metum') and false === \Config::get('app.auth.use_meta', false))
        {
            \Cli::write('Please set app.auth.use_meta to TRUE in APPPATH/config/app.php', 'red');
            $has_error = true;
        }

        if (true === \class_exists('\\Model_Users_Auth') and false === \Config::get('app.auth.use_auth', false))
        {
            \Cli::write('Please set app.auth.use_auth to TRUE in APPPATH/config/app.php', 'red');
            $has_error = true;
        }

        if (true === \Config::get('app.auth.use_facebook', false))
        {
            if ('' === \Config::get('app.api.facebook.app_id', null))
            {
                \Cli::write('Please provide app.api.facebook.app_id in APPPATH/config/app.php', 'red');
                $has_error = true;
            }

            if ('' === \Config::get('app.api.facebook.secret', null))
            {
                \Cli::write('Please provide app.api.facebook.secret in APPPATH/config/app.php', 'red');
                $has_error = true;
            }
        }

        if (true === \Config::get('app.auth.use_twitter', false))
        {
            if ('' === \Config::get('app.api.twitter.consumer_key', ''))
            {
                \Cli::write('Please provide app.api.twitter.consumer_key in APPPATH/config/app.php', 'red');
                $has_error = true;
            }

            if ('' === \Config::get('app.api.twitter.consumer_secret', ''))
            {
                \Cli::write('Please provide app.api.twitter.consumer_secret in APPPATH/config/app.php', 'red');
                $has_error = true;
            }
        }

        if ('' === \Config::get('app.salt', ''))
        {
            \Cli::write('Please provide app.salt secret key in APPPATH/config/app.php', 'red');
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
  php oil refine hybrid

Runtime options:
  -h, [--help]      # Show option
  -i, [--install]   # Install configuration file and user model/migrations script
  -t, [--test]      # Test installation to match configuration

Description:
  The 'oil refine hybrid' command can be used in several ways to facilitate quick development, help with
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

        static::install_config();
        static::install_user();
    }
    /**
     * Install configuration file
     *
     * @static
     * @access  protected
     * @return  void
     */
    protected static function install_config()
    {
        $file = 'app';
        $path = APPPATH.'config'.DS.$file.'.php';

        $content = file_get_contents(PKGPATH.'hybrid/config/app.php');

        switch(true)
        {
            case (true === \is_file($path) and 'y' === \Cli::prompt("Overwrite APPPATH/config/{$file}.php?", array('y', 'n'))) :
            case (false === \is_file($path)) : 
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
     * Install user table
     *
     * @static
     * @access  protected
     * @return  void
     */
    protected static function install_user()
    {
        if (true === \class_exists('\\Model_User'))
        {
            \Cli::write("Model User already exist, skipping this process", 'red');
        }

        $user_model = array(
            'user',
            'user_name:string[100]',
            'full_name:string[200]',
            'email:string[150]',
        );

        $auth_model = array();
        $meta_model = array();
        $facebook   = false;
        $twitter    = false;

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

        if ('y' === \Cli::prompt("Would you like to install `user_meta` table?", array('y', 'n')))
        {
            $meta_model[] = 'users_metum';
            $meta_model[] = 'user_id:int';
        }

        if ('y' === \Cli::prompt("Would you like to use Facebook Connect?", array('y', 'n')))
        {
            $facebook   = true;
        }

        if ('y' === \Cli::prompt("Would you like to use Twitter OAuth?", array('y', 'n')))
        {
            $twitter    = true;
        }

        $user_model[] = 'status:enum[]';

        if ('y' === \Cli::prompt("Confirm Generate Model and Migration for User?", array('y', 'n')))
        {
            \Oil\Generate::model($user_model);

            if (!empty($auth_model))
            {
                \Oil\Generate::model($auth_model);
            }

            if (!empty($meta_model))
            {
                \Oil\Generate::model($meta_model);
            }

            if (true === $facebook)
            {
                \Oil\Generate::model(array(
                    'facebook',
                    'facebook_name:string[200]',
                    'first_name:string[100]',
                    'last_name:string[100]',
                    'facebook_url:string[255]'
                ));

                \Oil\Generate::model(array(
                    'users_facebook',
                    'user_id:int',
                    'facebook_id:int',
                ));
            }

            if (true === $twitter)
            {
                \Oil\Generate::model(array(
                    'twitter',
                    'twitter_name:string[200]',
                    'full_name:string[100]',
                    'profile_image:string[255]'
                ));

                \Oil\Generate::model(array(
                    'users_twitter',
                    'user_id:int',
                    'twitter_id:int',
                ));
            }
        }
    }
}