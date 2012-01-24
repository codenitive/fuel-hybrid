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

use \Config;
use \FuelException;
use \Inflector;
use \Str;

/**
 * Hybrid 
 * 
 * A set of class that extends the functionality of FuelPHP without 
 * affecting the standard workflow when the application doesn't actually 
 * utilize Hybrid feature.
 * 
 * @package     Fuel
 * @subpackage  Hybrid
 * @category    Widget_Breadcrumb
 * @author      dbpolito <contato@dbpolito.net>
 * @link        https://github.com/dbpolito/Fuel-Breadcrumb/
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Widget_Breadcrumb extends Widget_Driver 
{
	/**
	 * Type
	 *
	 * @access  protected
	 * @var     string
	 */
	protected $type = 'breadcrumb';

	/**
	 * Add a new item, prepending or appending
	 *
	 * @access  public
	 * @param   string  $title
	 * @param   string  $content
	 * @param   bool    $prepend
	 * @return  self
	 */
	public function add($title, $content = '', $prepend = false)
	{
		if (empty($title))
		{
			throw new FuelException(__METHOD__.": Unable to add empty breadcrumb.");
		}

		if (empty($content) or ! strval($content))
		{
			$content = '';
		}

		$data = (object) array(
			'title'   => $title,
			'slug'    => Inflector::friendly_title($title, '-', true),
			'content' => Uri::create($content),
		);

		if (true === $prepend)
		{
			array_unshift($this->items, $data);
		}
		else
		{
			array_push($this->items, $data);
		}

		return $this;
	}

	/**
	 * Render Tab as a view
	 *
	 * @access  public
	 * @return  string
	 */
	public function render()
	{
		$template = $this->config['template'];
		$content  = '';

		$last_breadcrumb = (count($this->items) - 1);

		foreach ($this->items as $count => $item)
		{
			$active  = ($count === $last_breadcrumb ? 'class="active"' : '');
			$divider = ($count !== $last_breadcrumb ? $template['divider'] : '');

			$content .= Str::tr($template['item'], array(
				'active' => $active, 
				'content' => $item->content, 
				'title' => $item->title,
				'divider' => $divider
			));
		}
		
		$prefix  = Config::get('hybrid.widget.breadcrumb.prefix', '');

		return Str::tr($template['wrapper_open'], array('id' => $prefix.ltrim($this->name, $prefix))).$content.$template['wrapper_close'];
	}

}