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
 * Google APIs Visualization Library Class
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Chart_Driver
 * @abstract
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Chart_Driver 
{
	/**
	 * Load config file
	 * 
	 * @static
	 * @access  public
	 */
	public static function _init() 
	{
		\Config::load('chart', true);
	}

	/**
	 * A shortcode to initiate this class as a new object
	 * 
	 * @static
	 * @access  public
	 * @return  static 
	 */
	public static function forge() 
	{
		return new static();
	}

	/**
	 * Shortcode to self::forge().
	 *
	 * @deprecated  1.3.0
	 * @static
	 * @access  public
	 * @return  self::forge()
	 */
	public static function factory()
	{
		\Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
		
		return static::forge();
	}

	protected $options = array();
	protected $hAxis   = 'string';
	protected $columns = '';
	protected $rows    = '';

	/**
	 * Clean-up private property on new object
	 * 
	 * @access  public
	 */
	public function __construct() 
	{
		$this->clear();
	}

	/**
	 * Run the clean-up
	 * 
	 * @access  public
	 * @return  bool
	 */
	public function clear() 
	{
		$this->options = array();
		$this->columns = '';
		$this->rows    = '';

		return $this;
	}

	/**
	 * Set columns information
	 * 
	 * @access  public
	 * @param   array   $data 
	 */
	public function set_columns($data = array()) 
	{
		$this->columns = '';
		
		$count         = 0;

		if (count($data) > 0) 
		{
			foreach ($data as $key => $value) 
			{
				if ($count === 0) 
				{
					$this->hAxis = $value;
				}
				
				if (is_numeric($key))
				{
					$key = 'string';
				}
				
				$this->columns .= "data.addColumn('{$value}', '{$key}');\r\n";
				$count++;
			}
		}

		return $this;
	}

	/**
	 * Set chart options
	 * 
	 * @access  public
	 * @param   mixed   $name
	 * @param   mixed   $value
	 * @return  bool
	 */
	public function set_options($name, $value = '') 
	{
		if (is_array($name)) 
		{
			foreach ($name as $key => $value) 
			{
				$this->options[$key] = $value;
			}
		}
		elseif (is_string($name) and ! empty($name)) 
		{
			$this->options[$name] = $value;
		}
		else
		{
			throw new \FuelException(__METHOD__.' require \$name to be set.');
		}

		return $this;
	}

	/**
	 * Set rows information
	 * 
	 * @access  public
	 * @param   array   $data 
	 */
	public function set_rows($data = array()) 
	{
		$this->rows = "";
		$dataset = '';

		$x = 0;
		$y = 0;

		if (count($data) > 0) 
		{
			foreach ($data as $key => $value) 
			{
				if ($this->hAxis == 'date') 
				{
					$key = $this->parse_date($key);
				} 
				else 
				{
					$key = sprintf("'%s'", $key);
				}

				$dataset .= "data.setValue({$x}, {$y}, ".$key.");\r\n";

				foreach ($value as $k => $v) 
				{
					$y++;
					$dataset .= "data.setValue({$x}, {$y}, {$v});\r\n";
				}
				$x++;
				$y = 0;
			}
		}
		
		$this->rows .= "data.addRows(".$x.");\r\n{$dataset}";

		return $this;
	}

	/**
	 * Parse PHP Date Object into JavaScript new Date() format
	 * 
	 * @access  protected
	 * @param   date    $date
	 * @return  string 
	 */
	protected function parse_date($date) 
	{
		$key = strtotime($date);
		return 'new Date('.date('Y', $key).', '.(date('m', $key) - 1).', '.date('d', $key).')';
	}

	/**
	 * Render self
	 *
	 * @abstract
	 * @access  public
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Render the chart
	 * 
	 * @abstract
	 * @access  public
	 * @param   int     $width
	 * @param   int     $height
	 */
	public abstract function render($width, $height);

	/**
	 * Generate the chart
	 * 
	 * @deprecated
	 * @access  public
	 * @param   int     $width
	 * @param   int     $height
	 */
	public function generate()
	{
		\Log::warning('This method is deprecated. Please use a render() instead.', __METHOD__);

		return $this->render();
	}
	
}