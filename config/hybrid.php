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
            // default expiration (null = no expiration)
            'expiration' => 86400,
            
            // default storage driver
            'driver'     => 'file',

            // specific configuration settings for the file driver
            'file'  => array(
                'path'  =>  '',  // if empty the default will be application/cache/
            ),

            // specific configuration settings for the memcached driver
            'memcached'  => array(
                'cache_id'  => 'fuel',  // unique id to distinquish fuel cache items from others stored on the same server(s)
                'servers'   => array(   // array of servers and portnumbers that run the memcached service
                    array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 100)
                ),
            ),

            // specific configuration settings for the apc driver
            'apc'  => array(
                'cache_id'  => 'fuel',  // unique id to distinquish fuel cache items from others stored on the same server(s)
            ),

            // specific configuration settings for the redis driver
            'redis'  => array(
                'database'  => 'default'  // name of the redis database to use (as configured in config/db.php)
            ),
        ),
    ),

    // Tabs class configuration
    'tabs' => array(
        'prefix'   => 'tabs_',
        'template' => array(
            'wrapper_open'  => '<div id=":id">',
            'wrapper_close' => '</div>',
            'title_open'    => '<ul class="tabs">',
            'title_close'   => '</ul>',
            'title'         => '<li :active><a href="#:slug">:title</a></li>',
            'content_open'  => '<div class="pill-content">',
            'content_close' => '</div>',
            'content'       => '<div id=":slug" :active>:content</div>',
        ),
    ),

    // Pagination class configuration
    'pagination' => array(
        'template' => array(
            'wrapper_start'  => '<div class="pagination"> <ul>',
            'wrapper_end'    => '</ul> </div>',
            'page_start'     => '<li> ',
            'page_end'       => ' </li>',
            'previous_start' => '<li class="prev"> ',
            'previous_end'   => ' </li>',
            'previous_mark'  => '&laquo; ',
            'next_start'     => '<li class="next"> ',
            'next_end'       => ' </li>',
            'next_mark'      => ' &raquo;',
            'active_start'   => '<li class="active"><a href="#"> ',
            'active_end'     => ' </a></li>',
            'disabled'       => array(
                'previous_start' => '<li class="prev disabled"><a href="#">',
                'previous_end'   => '</a></li>',
                'next_start'     => '<li class="next disabled"><a href="#">',
                'next_end'       => '</a></li>',
            ),
        ),
    ),
    
);