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
        'auto_filter'           => false,
        'frontend'              => array(
            // you can set as many folder as possible
            'default'               => DOCROOT . 'themes/default/',
        )
    ),
    
    // available language for this application
    'available_language'    => array(
        'en',
    ),
    
);