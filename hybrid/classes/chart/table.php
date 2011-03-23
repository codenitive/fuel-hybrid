<?php

/**
 * Fuel
 *
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
 * Google APIs Visualization Library Class
 * 
 * @package FuelPHP
 * @subpackage Google APIs Visualization
 * @category Chart
 * @author Mior Muhammad Zaki <crynobone@gmail.com>
 */
class Chart_Table extends Table {

	public function __construct() {
		parent::__construct();

		$this->set_options(\Config::get('visualization.chart.table', array()));
	}

	public function generate($width = '100%', $height = '300px') {
		$columns = $this->columns;
		$rows = $this->rows;

		$this->set_options('width', $width);
		$this->set_options('height', $height);

		$options = json_encode($this->options);

		$id = 'table_' . md5($columns . $rows . time());

		return <<<SCRIPT
<div id="{$id}" style="width:{$width}; height:{$height};"></div>
<script type="text/javascript">
google.load("visualization", "1", {packages:["table"]});
google.setOnLoadCallback(draw_{$id});
function draw_{$id}() {
	var data = new google.visualization.DataTable();
	{$columns}
	{$rows}
	
	var chart = new google.visualization.Table(document.getElementById('{$id}'));
	chart.draw(data, {$options});
};
</script>
SCRIPT;
	}

}