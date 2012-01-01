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

	protected $key_map = array();

	public function inititate() 
	{
		
		$registries = \DB::select('*')
			->from($this->name)
			->as_object()
			->execute();

		foreach ($registries as $registry)
		{
			$value = unserialize($registry->value);

			$this->set($registry->name, $value);
			$this->key_map[$option->name] = array(
				'id'       => $option->id,
				'checksum' => md5($option->value),
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

			if ($checksum === $value)
			{
				continue;
			}

			if (true === $is_new)
			{
				\DB::insert($this->name)->set(array(
					'name' => $option_key,
					'value' => $value,
				))->execute();
			}
			else
			{
				\DB::update($this->name)->set(array(
					'value' => $value,
				))->where('id', '=', $id)->execute(); 
			}
		}
	}
	}

}