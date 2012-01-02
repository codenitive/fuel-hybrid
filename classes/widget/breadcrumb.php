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
			throw new \FuelException(__METHOD__.": Unable to add empty tab.");
		}

		if (empty($content) or ! strval($content))
		{
			$content = '';
		}

		$data = (object) array(
			'title'   => $title,
			'content' => \Ur::create($content),
		);

		if (true === $prepend)
		{
			array_shift($this->items, $data);
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

		$active_breadcrumb = (count($this->items) - 1);

		foreach ($this->items as $count => $item)
		{
			$active  = ($count === $active_breadcrumb ? 'class="active"' : '');
			$content .= \Str::tr($template['item'], array('active' => $active, 'content' => $item->content, 'title' => $item->title));
		}
		
		$prefix  = \Config::get('hybrid.widget.breadcrumb.prefix', '');

		return \Str::tr($template['wrapper_open'], array('id' => $prefix.ltrim($this->name, $prefix))).$content.$template['wrapper_close'];
	}

}