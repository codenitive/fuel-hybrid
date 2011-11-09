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

namespace Hybrid;

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Chart_GeoMap
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Chart_GeoMap extends Chart_Driver 
{
	public function __construct() 
	{
		parent::__construct();

		$this->set_options(\Config::get('chart.geomap', array()));
	}

	public function render($width = '100%', $height = '300px') 
	{
		$columns    = $this->columns;
		$rows       = $this->rows;

		$this->set_options('width', $width);
		$this->set_options('height', $height);

		$options    = json_encode($this->options);

		$id         = 'geomap_'.md5($columns.$rows.time().microtime());

		return <<<SCRIPT
<div id="{$id}" style="width:{$width}px; height:{$height}px;"></div>
<script type="text/javascript">
google.load('visualization', '1', {'packages': ['geomap']});

google.setOnLoadCallback(draw_{$id});
function draw_{$id}() {
	var data = new google.visualization.DataTable();
	{$columns}
	{$rows}
	
	var geomap = new google.visualization.GeoMap(document.getElementById('{$id}'));
	geomap.draw(data, {$options});
};
</script>
SCRIPT;
	}

}

