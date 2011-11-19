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

return array (
	/**
	 * Configuration for Area Chart
	 * 
	 * @see http://code.google.com/apis/visualization/documentation/gallery/areachart.html 
	 */
	'area' => array (
		'axisTitlesPosition' => 'in',
		//'colors' => array ('#0077CC', '#EDAB1E', '#86B22B'),
		'fontSize' => 11,
		//'hAxis' => array ('showTextEvery' => 7),
		'legend' => 'top',
		'lineWidth' => 4,
		'pointSize' => 4,

	),
	
	/**
	 * Configuration for Bar Chart
	 * 
	 * @see http://code.google.com/apis/visualization/documentation/gallery/barchart.html 
	 */
	'area' => array (
		'axisTitlesPosition' => 'in',
		//'colors' => array ('#0077CC', '#EDAB1E', '#86B22B'),
		'fontSize' => 11,
		//'hAxis' => array ('showTextEvery' => 7),
		'legend' => 'top',
		'lineWidth' => 4,
		'pointSize' => 4,

	),


	/**
	 * Configuration for Area Chart
	 * 
	 * @see http://code.google.com/apis/visualization/documentation/gallery/areachart.html 
	 */
	'line' => array(
		'axisTitlesPosition' => 'in',
		//'colors' => array ('#0077CC', '#EDAB1E', '#86B22B'),
		'fontSize' => 11,
		//'hAxis' => array ('showTextEvery' => 7),
		'legend' => 'top',
		'lineWidth' => 4,
		'pointSize' => 4,
	),

	/**
	 * Configuration for Geo Map
	 * 
	 * @see http://code.google.com/apis/visualization/documentation/gallery/geomap.html
	 */
	'geomap' => array(
		'dataMode' => 'regions',
	),
	
	'pie' => array (),
	
	'scatter' => array (
		'axisTitlesPosition' => 'in',
		//'colors' => array ('#0077CC', '#EDAB1E', '#86B22B'),
		'fontSize' => 11,
		//'hAxis' => array ('showTextEvery' => 7),
		'legend' => 'top',
		'lineWidth' => 4,
		'pointSize' => 4,
	),
	
	/**
	 * Configuration for Table
	 * 
	 * @see http://code.google.com/apis/visualization/documentation/gallery/table.html
	 */
	'table' => array (),

	/**
	 * Configuration for Annotated Timeline Chart
	 * 
	 * @see http://code.google.com/apis/visualization/documentation/gallery/annotatedtimeline.html
	 */
	'timeline' => array (
		//'colors' => array('#0077CC', '#EDAB1E', '#86B22B'),
		'dateFormat' => 'MMM d',
		'displayAnnotations' => false,
		'thickness' => 3,
	),
	
);