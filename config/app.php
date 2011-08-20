<?php

return array(
    // application identity is a unique string to be used in cookie and etc.
    'identity'              => 'fuelapp',
    
    // set application name
    'site_name'             => 'FuelPHP Application',
    
    // set application tagline
    'site_tagline'          => 'Some fancy words',
    
    // set application into maintenance mode if value is set to true (default is false)
    'maintenance_mode'      => false,
    
    // set template file
    'template'              => array(
        'load_assets'           => false,
        'default_folder'        => 'default/',
        'default_filename'      => 'index',
        'auto_encode'           => false,
        'frontend'              => array(
            // you can set as many folder as possible
            'default'               => DOCROOT . 'themes/default/',
        )
    ),
    
    // available language for this application
    'available_language'    => array(
        'en',
    ),
    
    'auth'                  => array(
        // Set application to load Auth class.
        'enabled'               => true,
        
        // Auth to use `users_auth` table for user meta information, useful to keep `users` table is simple as possible.
        'use_auth'              => true, 
        
        // Auth to use `users_meta` table for user authentication information, useful to keep `users` table is simple as possible.                              
        'use_meta'              => true,

        // Auth to enable Facebook Connect. 
        'use_facebook'          => false,

        // Auth to enable Twitter Oauth.
        'use_twitter'           => false,

        'optional_fields'       => null,

        'verified_status'       => null,

        // Auth _route_ configuration
        '_route_'           => array(
            'registration'      => 'register',
            'after_login'       => '/',
            'after_logout'      => '/',
        ),
    ),

    'api'                   => array(
        'twitter'               => array(
            'consumer_key'          => '',
            'consumer_secret'       => '',
        ),
        'facebook'          => array(
            'app_id'            => '',
            'secret'            => '',
            'redirect_uri'      => '',
            'scope'             => '',
        ),
    ),
    
    // The salt used for password hashing.
    'salt'                  => null,
);