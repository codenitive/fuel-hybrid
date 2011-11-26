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

return array(
	
	'normal'    => array(
		// Set application to load Auth class. 
		'enabled'         => true,
		
		// List of user fields to be used (default to array('status', 'full_name'))
		'optional_fields' => null,
		
		// Allow status to login based on `users`.`status` (default to ('verified'))
		'allowed_status'  => null,

		// Auth to use `users_meta` table for user meta information, useful to keep `users` table is simple as possible.
		'use_meta'        => true,

		// Auth to use `users_auth` table for user meta information, useful to keep `users` table is simple as possible.
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
		
		'openid' => array (
			'identifier_form_name' => 'openid_identifier',
			'ax_required' => array('contact/email', 'namePerson/first', 'namePerson/last'),
			'ax_optional' => array('namePerson/friendly', 'birthDate', 'person/gender', 'contact/country/home'),
		),
	
	),

	/**
	 * mysql_timestamp
	 *
	 * Set default mysql_timestamp option for authentications table
	 *
	 * Available values:
	 * null     No timestamp
	 * false    Use time()
	 * true     Use datetime
	 */
	'mysql_timestamp' => null,

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
	'salt' => null,

	/**
	 * hash_type
	 *
	 * Set hashing method (md5, sha1, crypt_hash)
	 */
	'hash_type' => 'sha1',

	/**
	 * expiration
	 *
	 * Set default number of seconds before Cookie expired
	 *
	 * Available values:
	 * -1   Most maximum time
	 *  0   Until browser is turn off
	 * >0   offset to current time()    
	 */
	'expiration' => 0,

	/**
	 * verify_user_agent
	 *
	 * Verify User Agent in Hash
	 */
	'verify_user_agent' => false,
	
);