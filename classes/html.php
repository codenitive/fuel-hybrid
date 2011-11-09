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
 * @category    Html
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Html extends \Fuel\Core\Html 
{
	/**
	 * Generates a html heading tag
	 *
	 * @param   string          heading text
	 * @param   int             1 through 6 for h1-h6
	 * @param   array|string    tag attributes
	 * @return  string
	 */
	public static function h($content = '', $num = 1, $attr = false)
	{
		return html_tag('h'.$num, $attr, $content);
	}
	
	/**
	 * Generates a html title tag
	 *
	 * @static
	 * @access  public
	 * @param   string  $content        page title
	 * @param   array   $attr
	 * @return  string
	 */
	public static function title($content = '', $attr = array()) 
	{
		$title = \Config::get('app.site_name');

		if ( ! empty($content) and is_string($content)) 
		{
			$title = sprintf('%s &mdash; %s', $content, $title);
		}

		return html_tag('title', $attr, $title);
	}

	/**
	 * Generates a html break tag
	 *
	 * @param   int             number of times to repeat the br
	 * @param   array|string    tag attributes
	 * @return  string
	 */
	public static function br($num = 1, $attr = false)
	{
		return str_repeat(html_tag('br', $attr), $num);
	}

	/**
	 * Generates a html horizontal rule tag
	 *
	 * @param   array|string    tag attributes
	 * @return  string
	 */
	public static function hr($attr = false)
	{
		return html_tag('hr', $attr);
	}
	
	/**
	 * Generates a ascii code for non-breaking whitespaces
	 *
	 * @param   int     number of times to repeat
	 * @return  string
	 */
	public static function nbs($num = 1)
	{
		return str_repeat('&nbsp;', $num);
	}

	/**
	 * Generates a html5 header tag or div with id "header"
	 *
	 * @param   string  header content
	 * @param   array   tag attributes
	 * @return  string
	 */
	public static function header($content = '', $attr = array())
	{
		if (static::$html5)
		{
			return html_tag('header', $attr, $content);
		}
		else
		{
			return html_tag('div', array_merge(array('id' => 'header'), $attr), $content);
		}
	}

}