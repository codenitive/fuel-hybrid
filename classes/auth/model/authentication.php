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
 * @category    Auth_Model_Authentication
 * @deprecated
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Auth_Model_Authentication extends \Fuel\Core\Model_Crud
{
	protected static $_table_name = 'authentications';

	protected static function _timestamp()
	{
		$date = \Date::time();

		switch (\Config::get('autho.mysql_timestamp'))
		{ 
			case null :
			default :
				$date = null;
			break;

			case false :
				$date = $date->get_timestamp();
			break;

			case true :
				$date = $date->format('mysql');
			break;
		}
	}

	public static function update($config = array())
	{
		extract($config);

		$query = \DB::update(static::$_table_name);

		if ( ! empty($set))
		{
			$query->set($set);
		}
		
		if ( ! empty($where))
		{
			$query->where($where);
		}

		$query = $this->pre_update($query);
		$result = $query->execute(isset(static::$_connection) ? static::$_connection : null);


		return $this->post_update($result);
	}

	protected function pre_save($query)
	{
		if (($date = static::_timestamp()) !== null)
		{
			$query->set(array(
				'created_at' => $date,
				'updated_at' => $date,
			));		
		}

		return $query;
	}

	protected function pre_update($query)
	{
		if (($date = static::_timestamp()) !== null)
		{
			$query->set(array(
				'updated_at' => $date,
			));		
		}

		return $query;
	}

}