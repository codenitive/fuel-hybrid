<?php

return array(
	// application identity is a unique string to be used in cookie and etc.
	'identity'				=> 'fuelapp',
	
	// set application name
	'site_name'				=> 'FuelPHP Application',
	
	// set application tagline
	'site_tagline'			=> 'Some fancy words',
	
	// set application into maintenance mode if value is set to true (default is false)
	'maintenance_mode' 		=> false,
	
	// set template file
	'template' 				=> array(
		'load_assets'			=> false,
		'default_filename'		=> 'index',
		'auto_encode'			=> false,
		'folders'				=> array(
			// you can set as many folder as possible
			'default' 				=> APPPATH . 'views/themes/default/',
			'frontend'				=> DOCROOT . 'themes/default/',
		)
	),
	
	// available language for this application
	'available_language' 	=> array(
		'en',
	),
	
	'user_table' 			=> array(
		'use_meta' 				=> true,
		'use_auth' 				=> true,
		'use_twitter'			=> false,
		'use_facebook' 			=> false,
	),

	'api' 					=> array(
		'twitter' 				=> array(
			'consumer_key' 			=> '',
			'consumer_secret' 		=> '',
		),
		'facebook' 			=> array(
			'app_id' 			=> '',
			'secret' 			=> '',
			'redirect_uri'		=> '',
			'scope'				=> '',
		),
		'_redirect' 		=> array(
			'registration' 		=> 'register',
			'after_login' 		=> '/',
			'after_logout' 		=> '/',
		),
	),
	
	'salt' 					=> 's8g5MgO5kVtEEmc_o0rP0UfI',
);