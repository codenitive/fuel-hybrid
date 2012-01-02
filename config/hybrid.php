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
	// DB Table List
	'tables'          => array(
		'users' => array(
			'user'  => 'users',
			'meta'  => 'users_meta',
			'auth'  => 'users_auths',
			'group' => 'users_roles',
		),
		'group'    => 'roles',
		'registry' => 'options',
		'social'   => 'authentications',
	),

	// Alway profiling trigger using ?profiler=1 and disable with ?profiler=0
	'profiling' => false,

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

	// Tab class configuration
	'widget' => array(
		'tab' => array(
			'prefix'   => 'tab_',
			'template' => array(
				'wrapper_open'  => '<div id=":id">',
				'wrapper_close' => '</div>',
				'item_open'    => '<ul class="tabs">',
				'item_close'   => '</ul>',
				'item'          => '<li :active><a href="#:slug">:title</a></li>',
				'content_open'  => '<div class="pill-content">',
				'content_close' => '</div>',
				'content'       => '<div id=":slug" :active>:content</div>',
			),
		),
		'breadcrumb' => array(
			'prefix'   => 'breadcrumb_',
			'template' => array(
				'wrapper_start' => '<ul id=":id" class="breadcrumb">',
				'wrapper_end'   => '</ul>',
				'item'          => '<li :active><a href=":content">:title</a></li>',
				'divider'       => '<span class="divider">/</span>'
			),
		),
	),

	// Pagination class configuration
	'pagination' => array(
		'template' => array(
			'wrapper_start'  => '<div class="pagination"><ul>',
			'wrapper_end'    => '</ul></div>',
			'page_start'     => '<li class=":state"><a href=":url">',
			'page_end'       => '</a></li>',
			'previous_start' => '<li class="prev :state"><a href=":url">',
			'previous_end'   => '</a></li>',
			'previous_mark'  => '&laquo; ',
			'next_start'     => '<li class="next :state"><a href=":url">',
			'next_end'       => '</a></li>',
			'next_mark'      => ' &raquo;',
			'state'          => array(
				'previous_next' => array(
					'active'   => '',
					'disabled' => 'disabled',
				),
				'current_page' => 'active',
			),
		),
	),
	
);