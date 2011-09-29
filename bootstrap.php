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
    'Hybrid\\Acl'                  => __DIR__.'/classes/acl.php',
    'Hybrid\\Curl'                 => __DIR__.'/classes/curl.php',
    'Hybrid\\Factory'              => __DIR__.'/classes/factory.php',
    'Hybrid\\Input'                => __DIR__.'/classes/input.php',
    'Hybrid\\Html'                 => __DIR__.'/classes/html.php',
    'Hybrid\\Pagination'           => __DIR__.'/classes/pagination.php',
    'Hybrid\\Request'              => __DIR__.'/classes/request.php',
    'Hybrid\\Restful'              => __DIR__.'/classes/restserver.php',
    'Hybrid\\Restserver'           => __DIR__.'/classes/restserver.php',
    'Hybrid\\Swiftmail'            => __DIR__.'/classes/swiftmail.php',
    'Hybrid\\Uri'                  => __DIR__.'/classes/uri.php',
    'Hybrid\\View'                 => __DIR__.'/classes/view.php',
    
    'Hybrid\\Auth'                 => __DIR__.'/classes/auth.php',
    'Hybrid\\Auth_Controller'      => __DIR__.'/classes/auth/controller.php',
    'Hybrid\\Auth_Provider_Normal' => __DIR__.'/classes/auth/provider/normal.php',
    
    'Hybrid\\Auth_Driver'          => __DIR__.'/classes/auth/driver.php',
    'Hybrid\\Auth_Driver_User'     => __DIR__.'/classes/auth/driver/user.php',
    
    'Hybrid\\Auth_Strategy'        => __DIR__.'/classes/auth/strategy.php',
    'Hybrid\\Auth_Strategy_Normal' => __DIR__.'/classes/auth/strategy/normal.php',
    'Hybrid\\Auth_Strategy_OAuth'  => __DIR__.'/classes/auth/strategy/oauth.php',
    'Hybrid\\Auth_Strategy_OAuth2' => __DIR__.'/classes/auth/strategy/oauth2.php',
    
    'Hybrid\\Chart'                => __DIR__.'/classes/chart.php',
    'Hybrid\\Chart_Utility'        => __DIR__.'/classes/chart/utility.php',
    'Hybrid\\Chart_Area'           => __DIR__.'/classes/chart/area.php',
    'Hybrid\\Chart_Bar'            => __DIR__.'/classes/chart/bar.php',
    'Hybrid\\Chart_GeoMap'         => __DIR__.'/classes/chart/geomap.php',
    'Hybrid\\Chart_Line'           => __DIR__.'/classes/chart/line.php',
    'Hybrid\\Chart_Pie'            => __DIR__.'/classes/chart/pie.php',
    'Hybrid\\Chart_Scatter'        => __DIR__.'/classes/chart/scatter.php',
    'Hybrid\\Chart_Table'          => __DIR__.'/classes/chart/table.php',
    'Hybrid\\Chart_Timeline'       => __DIR__.'/classes/chart/timeline.php',
    
    'Hybrid\\Controller'           => __DIR__.'/classes/controller.php',
    'Hybrid\\Controller_Frontend'  => __DIR__.'/classes/controller/frontend.php',
    'Hybrid\\Controller_Template'  => __DIR__.'/classes/controller/template.php',
    'Hybrid\\Controller_Rest'      => __DIR__.'/classes/controller/rest.php',
    'Hybrid\\Controller_Hybrid'    => __DIR__.'/classes/controller/hybrid.php',
    
    'Hybrid\\Parser'               => __DIR__.'/classes/parser.php',
    'Hybrid\\Parser_Driver'        => __DIR__.'/classes/parser/driver.php',
    'Hybrid\\Parser_Markdown'      => __DIR__.'/classes/parser/markdown.php',
    
    'Hybrid\\Template'             => __DIR__.'/classes/template.php',
    'Hybrid\\Template_Driver'      => __DIR__.'/classes/template/driver.php',
    'Hybrid\\Template_Normal'      => __DIR__.'/classes/template/normal.php',
    'Hybrid\\Template_Frontend'    => __DIR__.'/classes/template/frontend.php',
));

/* End of file bootstrap.php */