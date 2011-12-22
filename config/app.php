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
	// Application identity is a unique string to be used in cookie and etc.
	'identity'              => 'fuelapp',
	
	// Application name
	'site_name'             => 'FuelPHP Application',
	
	// Set application into maintenance mode if value is set to true (default is false).
	'maintenance_mode'      => false,
	
	// List of available language for this application.
	'available_language'    => array(
		'en',
	),

	/**
	 * Providers
	 * 
	 * Providers such as Facebook, Twitter, etc all use different Strategies such as oAuth, oAuth2, etc.
	 * oAuth takes a key and a secret, oAuth2 takes a (client) id and a secret, optionally a scope.
	 */
	'providers' => array(

		'dropbox' => array(
			'key'    => '',
			'secret' => '',
		),
		
		'facebook' => array(
			'id'     => '',
			'secret' => '',
			'scope'  => 'email,offline_access',
		),

		'flickr' => array(
			'key'    => '',
			'secret' => '',
		),

		'foursquare' => array(
			'id'     => '',
			'secret' => '',
		),

		'github' => array(
			'id'     => '',
			'secret' => '',
		),

		'google' => array(
			'id'     => '',
			'secret' => '',
		),

		'instagram' => array(
			'id'     => '',
			'secret' => '',
		),

		'linkedin' => array(
			'key'    => '',
			'secret' => '',
		),
		
		'tumblr' => array(
			'key'    => '',
			'secret' => '',
		),

		'twitter' => array(
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
	
);