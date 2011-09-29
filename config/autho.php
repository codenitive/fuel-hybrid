<?php

return array(
    
    'normal'    => array(
        'enabled'         => true,
        'optional_fields' => null,
        'allowed_status'  => null,
        'use_meta'        => true,
        'use_auth'        => true,
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

    'salt'      => null,
);