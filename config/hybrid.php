<?php

return array(

    // Template class configuration
    'template' => array(
        'load_assets'      => false,
        'default_folder'   => 'default/',
        'default_filename' => 'index',
        'auto_filter'      => false,
        'frontend'         => array(
            // you can set as many folder as possible
            'default' => DOCROOT . 'themes/default/',
        ),
    ),

    // Currency class configuration
    'currency'  => array(
        'default' => 'EUR',
        'cache'   => array(
            'expiration' => null,
        ),
    ),

    // Tabs class configuration

    // Pagination class configuration
);