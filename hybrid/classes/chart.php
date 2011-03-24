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
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * Google APIs Visualization Library Class
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Chart
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
abstract class Chart {

	public static function _init() {
		\Config::load('visualization', true);
	}

	/**
	 * A shortcode to initiate this class as a new object
	 * 
	 * @static
	 * @access public
	 * @return static 
	 */
	public static function factory() {
		return new static();
	}

	protected $options = array();
	protected $hAxis = 'string';
	protected $columns = '';
	protected $rows = '';

	/**
	 * Clean-up private property on new object
	 * 
	 * @access public
	 */
	public function __construct() {
		$this->clear();
	}

	/**
	 * Run the clean-up
	 * 
	 * @access public
	 * @return boolean
	 */
	public function clear() {
		$this->options = array();
		$this->columns = '';
		$this->rows = '';

		return true;
	}

	/**
	 * Set columns information
	 * 
	 * @param array $data 
	 */
	public function set_columns($data = array()) {
		$this->columns = '';

		$count = 0;

		if (count($data) > 0) {
			foreach ($data as $key => $value) {
				if ($count === 0) {
					$this->hAxis = $value;
				}

				$this->columns .= "data.addColumn('{$value}', '{$key}');\r\n";
				$count++;
			}
		}
	}

	/**
	 * Set chart options
	 * 
	 * @param mixed $name
	 * @param mixed $value
	 * @return boolean 
	 */
	public function set_options($name, $value = '') {
		if (is_null($name)) {
			return false;
		}

		if (is_array($name)) {
			foreach ($name as $key => $value) {
				$this->options[$key] = $value;
			}

			return true;
		}

		if (is_string($name)) {
			$this->options[$name] = $value;
		}

		return true;
	}

	/**
	 * Set rows information
	 * 
	 * @param array $data 
	 */
	public function set_rows($data = array()) {
		$this->rows = "";
		$dataset = '';

		$x = 0;
		$y = 0;

		if (count($data) > 0) {
			foreach ($data as $key => $value) {
				if ($this->hAxis == 'date') {
					$key = $this->parse_date($key);
				} else {
					$key = sprintf("'%s'", $key);
				}

				$dataset .= "data.setValue({$x}, {$y}, " . $key . ");\r\n";

				foreach ($value as $k => $v) {
					$y++;
					$dataset .= "data.setValue({$x}, {$y}, {$v});\r\n";
				}
				$x++;
				$y = 0;
			}
		}
		$this->rows .= "data.addRows(" . $x . ");\r\n{$dataset}";
	}

	/**
	 * Parse PHP Date Object into JavaScript new Date() format
	 * 
	 * @access protected
	 * @param date $date
	 * @return string 
	 */
	protected function parse_date($date) {
		$key = strtotime($date);
		return 'new Date(' . date('Y', $key) . ', ' . (date('m', $key) - 1) . ', ' . date('d', $key) . ')';
	}

	/**
	 * Generate the chart
	 * 
	 * @abstract
	 * @access public
	 */
	public abstract function generate($width, $height);
}