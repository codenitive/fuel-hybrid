<?php

return array(
    
    'normal'    => array(
        'enabled'         => true,      // Set application to load Auth class. 
        'optional_fields' => null,      // List of user fields to be used (default to array('status', 'full_name'))
        'allowed_status'  => null,      // Allow status to login based on `users`.`status` (default to ('verified'))
        'use_meta'        => true,      // Auth to use `users_meta` table for user meta information, useful to keep `users` table is simple as possible.
        'use_auth'        => true,      // Auth to use `users_auth` table for user meta information, useful to keep `users` table is simple as possible.
    ),

    'urls' => array(
        'registration' => 'auth/register',
        'login'        => 'auth/login',
        'callback'     => 'auth/callback',
        
        'registered'   => 'auth/account',
        'logged_in'    => 'auth/account',
    ),

    /**
     * Providers
     * 
     * Providers such as Facebook, Twitter, etc all use different Strategies such as oAuth, oAuth2, etc.
     * oAuth takes a key and a secret, oAuth2 takes a (client) id and a secret, optionally a scope.
     */
    'providers' => array(
        
        'facebook' => array(
            'id'     => '',
            'secret' => '',
            'scope'  => 'email,offline_access',
        ),
        
        'twitter' => array(
            'key'    => '',
            'secret' => '',
        ),

        'dropbox' => array(
            'key'    => '',
            'secret' => '',
        ),

        'linkedin' => array(
            'key'    => '',
            'secret' => '',
        ),

        'flickr' => array(
            'key'    => '',
            'secret' => '',
        ),

        'youtube' => array(
            'key'   => '',
            'scope' => 'http://gdata.youtube.com',
        ),
    
    ),

    /**
     * link_multiple_providers
     * 
     * Can multiple providers be attached to one user account
     */
    'link_multiple_providers' => true,

    /**
     * salt
     *
     * Application salt for hashing
     */
    'salt'      => null,

    /**
     * expiration
     *
     * Set default number of seconds before Cookie expired
     */
    'expiration' => 0,

    /**
     * verify_user_agent
     *
     * Verify User Agent in Hash
     */
    'verify_user_agent' => false,
);