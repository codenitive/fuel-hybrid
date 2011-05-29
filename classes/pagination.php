<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		MIT License
 * @copyright	2010 - 2011 Fuel Development Team
 */

namespace Hybrid;

class Pagination {

	/**
	 * The current page
	 * 
	 * @access		public
	 * @staticvar	int
	 */
	public static $current_page = null;

	/**
	 * The offset that the current page starts at
	 * 
	 * @access		public
	 * @staticvar	int
	 */
	public static $offset = 0;

	/**
	 * The number of items per page
	 * 
	 * @access		public
	 * @staticvar	int
	 */
	public static $per_page = 10;

	/**
	 * The number of total pages
	 * 
	 * @access		public
	 * @staticvar	int
	 */
	public static $total_pages = 0;

	/**
	 * The total number of items
	 * 
	 * @access		protected
	 * @staticvar	int
	 */
	protected static $total_items = 0;

	/**
	 * The total number of links to show
	 * 
	 * @access		protected
	 * @staticvar	int
	 */
	protected static $num_links = 5;

	/**
	 * The URI segment containg page number
	 * 
	 * @access		protected
	 * @staticvar	int
	 */
	protected static $uri_segment = 3;

	/**
	 * The pagination URL
	 * 
	 * @access		protected
	 * @staticvar	mixed
	 */
	protected static $pagination_url;
	
	/**
	 * The pagination URL (after the page number URI segment)
	 * 
	 * @access		protected
	 * @staticvar	mixed
	 */
	protected static $suffix_url;

	/**
	 * Init
	 *
	 * Loads in the config and sets the variables
	 *
	 * @access	public
	 * @return	void
	 */
	public static function _init()
	{
		$config = \Config::get('pagination', array());

		static::set_config($config);
	}

	/**
	 * Set Config
	 *
	 * Sets the configuration for pagination
	 *
	 * @access	public
	 * @param	array	$config The configuration array
	 * @return	void
	 */
	public static function set_config(array $config)
	{
		static::$current_page = null;
		
		foreach ($config as $key => $value)
		{
			static::${$key} = $value;
		}

		static::initialize();
	}

	/**
	 * Prepares vars for creating links
	 *
	 * @access	public
	 * @return	array    The pagination variables
	 */
	protected static function initialize()
	{

		static::$total_pages = ceil(static::$total_items / static::$per_page) ?: 1;

		is_null(static::$current_page) and static::$current_page = (int) \URI::segment(static::$uri_segment);

		if (static::$current_page > static::$total_pages)
		{
			static::$current_page = static::$total_pages;
		}
		elseif (static::$current_page < 1)
		{
			static::$current_page = 1;
		}

		// The current page must be zero based so that the offset for page 1 is 0.
		static::$offset = (static::$current_page - 1) * static::$per_page;
	}

	/**
	 * Creates the pagination links
	 *
	 * @access public
	 * @return mixed    The pagination links
	 */
	public static function create_links()
	{
		if (static::$total_pages == 1)
		{
			return '';
		}

		$pagination = '';
		$pagination .= '&nbsp;'.static::prev_link('&laquo; Previous').'&nbsp;&nbsp;';
		$pagination .= static::page_links();
		$pagination .= '&nbsp;'.static::next_link('Next &raquo;');

		return $pagination;
	}

	/**
	 * Pagination Page Number links
	 *
	 * @access	public
	 * @return	mixed	Markup for page number links
	 */
	public static function page_links()
	{
		if (static::$total_pages == 1)
		{
			return '';
		}
		
		$pagination = '';
		
		// Let's get the starting page number, this is determined using num_links
		$start = ((static::$current_page - static::$num_links) > 0) ? static::$current_page - (static::$num_links - 1) : 1;

		// Let's get the ending page number
		$end   = ((static::$current_page + static::$num_links) < static::$total_pages) ? static::$current_page + static::$num_links : static::$total_pages;

		for($i = $start; $i <= $end; $i++)
		{
			if (static::$current_page == $i)
			{
				$pagination .= '<b>'.$i.'</b>';
			}
			else
			{
				$url = ($i == 1) ? '' : '/'.$i;
				$pagination .= \Html::anchor(rtrim(static::$pagination_url, '/') . $url . '/' . static::$suffix_url, $i);
			}
		}

		return $pagination;
	}

	/**
	 * Pagination "Next" link
	 *
	 * @static
	 * @access	public
	 * @param	string	$value The text displayed in link
	 * @return	mixed	The next link
	 */
	public static function next_link($value)
	{
		if (static::$total_pages == 1)
		{
			return '';
		}

		if (static::$current_page == static::$total_pages)
		{
			return $value;
		}
		else
		{
			$next_page = static::$current_page + 1;
			return \Html::anchor(rtrim(static::$pagination_url, '/').'/'.$next_page . '/'. static::$suffix_url, $value);
		}
	}

	/**
	 * Pagination "Previous" link
	 *
	 * @static
	 * @access	public
	 * @param	string	$value The text displayed in link
	 * @return	mixed	The previous link
	 */
	public static function prev_link($value)
	{
		if (static::$total_pages == 1)
		{
			return '';
		}

		if (static::$current_page == 1)
		{
			return $value;
		}
		else
		{
			$previous_page = static::$current_page - 1;
			$previous_page = ($previous_page == 1) ? '' : '/' . $previous_page;
			return \Html::anchor(rtrim(static::$pagination_url, '/') . $previous_page . '/' . static::$suffix_url, $value);
		}
	}
	
	/**
	 * Build query string
	 * 
	 * @static
	 * @access	public
	 * @param	mixed	$values
	 * @return	string 
	 */
	public static function build_get_query($values) 
	{
		$dataset = array ();
		
		$check_get_input = function($value, & $dataset) 
		{
			$data = \Hybrid\Input::get($value);
			
			if (empty($data))
			{
				return false;
			}
			else 
			{
				array_push($dataset, sprintf('%s=%s', $value, $data));
				return;
			}
		};
		
		if (is_array($values))
		{
			foreach ($values as $value)
			{
				$check_get_input($value, $dataset);
			}
		}
		else 
		{
			$check_get_input($values, $dataset);
		}
		
		return '?' . implode('&', $dataset);
	}
}

/* End of file pagination.php */
