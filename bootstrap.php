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

Autoloader::add_classes(array (
	'Hybrid\\Curl'                      => __DIR__.'/classes/curl.php',
	'Hybrid\\Factory'                   => __DIR__.'/classes/factory.php',
	'Hybrid\\Input'                     => __DIR__.'/classes/input.php',
	'Hybrid\\Html'                      => __DIR__.'/classes/html.php',
	'Hybrid\\Pagination'                => __DIR__.'/classes/pagination.php',
	'Hybrid\\Request'                   => __DIR__.'/classes/request.php',
	'Hybrid\\Restful'                   => __DIR__.'/classes/restful.php',
	'Hybrid\\View'                      => __DIR__.'/classes/view.php',
	
	'Hybrid\\Acl'                       => __DIR__.'/classes/acl.php',
	'Hybrid\\Acl_User'                  => __DIR__.'/classes/acl/user.php',
	
	'Hybrid\\Controller'                => __DIR__.'/classes/controller.php',
	'Hybrid\\Controller_Frontend'       => __DIR__.'/classes/controller/frontend.php',
	'Hybrid\\Controller_Template'       => __DIR__.'/classes/controller/template.php',
	'Hybrid\\Controller_Rest'           => __DIR__.'/classes/controller/rest.php',
	'Hybrid\\Controller_Combine'        => __DIR__.'/classes/controller/combine.php',
	
	'Hybrid\\Chart'                     => __DIR__.'/classes/chart.php',
	'Hybrid\\Chart_Utility'             => __DIR__.'/classes/chart/utility.php',
	
	'Hybrid\\Chart_Area'                => __DIR__.'/classes/chart/area.php',
	'Hybrid\\Chart_Bar'                 => __DIR__.'/classes/chart/bar.php',
	'Hybrid\\Chart_Line'                => __DIR__.'/classes/chart/line.php',
	'Hybrid\\Chart_GeoMap'              => __DIR__.'/classes/chart/geomap.php',
	'Hybrid\\Chart_Pie'                 => __DIR__.'/classes/chart/pie.php',
	'Hybrid\\Chart_Scatter'             => __DIR__.'/classes/chart/scatter.php',
	'Hybrid\\Chart_Table'               => __DIR__.'/classes/chart/table.php',
	'Hybrid\\Chart_Timeline'            => __DIR__.'/classes/chart/timeline.php'
));

/* End of file bootstrap.php */