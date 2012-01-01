<?php

namespace Fuel\Tasks;

use Oil\Generate;

class Registry
{
	public static function run()
	{
		static::generate();
	}

	/**
	 * Install registries table
	 *
	 * @static
	 * @access  protected
	 * @return  void
	 */
	public static function generate($table_name = null)
	{
		$table_name or $table_name = \Config::get('hybrid.tables.registry', 'options');
		$class_name = \Inflector::classify($table_name, true);

		if ('y' === \Cli::prompt("Would you like to install `registry.{$table_name}` table?", array('y', 'n')))
		{
			Generate::migration(array(
				'create_'.$table_name,
				'name:string[255]',
				'value:longtext',
			));
			Generate::$create_files = array();
		}
	}
}