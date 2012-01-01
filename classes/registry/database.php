<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.1
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
 * Registry Class
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Registry_Database
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Registry_Database extends Registry_Driver
{
	/**
	 * @access  protected
	 * @var     string  storage configuration, currently only support runtime.
	 */
	protected $storage = 'database';

	protected $table_name = null;

	protected $key_map = array();

	public function initiate() 
	{
		$this->table_name = \Arr::get($this->config, 'table_name', \Config::get('hybrid.tables.registry', $this->name));
		
		$registries = \DB::select('*')
			->from($this->table_name)
			->as_object()
			->execute();

		foreach ($registries as $registry)
		{
			$value = unserialize($registry->value);

			$this->set($registry->name, $value);
			$this->key_map[$registry->name] = array(
				'id'       => $registry->id,
				'checksum' => md5($registry->value),
			);
		}
	}

	public function shutdown() 
	{
		foreach ($this->data as $option_key => $option_value)
		{
			$is_new   = true;
			$id       = null;
			$checksum = '';
			
			if (array_key_exists($option_key, $this->key_map))
			{
				$is_new = false;
				extract($this->key_map[$option_key]);
			}

			$value = serialize($option_value);

			if ($checksum === md5($value))
			{
				continue;
			}

			\DB::select('name')->from($this->table_name)->where('name', '=', $option_key)->execute();

			if (true === $is_new and \DB::count_last_query() < 1)
			{
				\DB::insert($this->table_name)->set(array(
					'name' => $option_key,
					'value' => $value,
				))->execute();
			}
			else
			{
				\DB::update($this->table_name)->set(array(
					'value' => $value,
				))->where('id', '=', $id)->execute(); 
			}
		}
	}

}