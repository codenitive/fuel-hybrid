<?php

/**
 * Faces.MY (Get Listed. Get Hired) 
 *  
 * @package     Hybrid 
 * @category    Tasks
 * @version     0.1.0
 * @since       0.1.0
 * @author      Faces.MY Development Team <hello@faces.my>
 */

namespace Fuel\Tasks;

/**
 * Setup commandline:
 *      php oil refine hybrid
 *
 * @package  app
 */
class Hybrid {

    public static function run()
    {
        \Cli::write("Start Installation", "green");

        static::install_user();
    }

    public static function install_user()
    {
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

        if ('y' === \Cli::prompt("Would you like to use Twitter Oauth?", array('y', 'n')))
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