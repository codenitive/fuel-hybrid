<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package     Fuel
 * @version     1.0
 * @author      Dan Horrigan <http://dhorrigan.com>
 * @license     MIT License
 * @copyright   2010 - 2011 Fuel Development Team
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
 * @category    Pagination
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Pagination extends \Fuel\Core\Pagination 
{
    /**
     * @var array The HTML for the display
     */
    public static $template = array(
        'wrapper_start'  => '<div class="pagination"> ',
        'wrapper_end'    => ' </div>',
        'page_start'     => '<span class="page-links"> ',
        'page_end'       => ' </span>',
        'previous_start' => '<span class="previous"> ',
        'previous_end'   => ' </span>',
        'previous_mark'  => '&laquo; ',
        'next_start'     => '<span class="next"> ',
        'next_end'       => ' </span>',
        'next_mark'      => ' &raquo;',
        'active_start'   => '<span class="active"> ',
        'active_end'     => ' </span>',
        'disabled'       => array(
            'previous_start' => '<li class="prev disabled"><a href="#">',
            'previous_end'   => '</a></li>',
            'next_start'     => '<li class="next disabled"><a href="#">',
            'next_end'       => '</a></li>',
        ),
    );
    
    /**
     * The pagination URL (after the page number URI segment)
     * 
     * @access      protected
     * @staticvar   mixed
     */
    protected static $suffix_url;

    /**
     * Pagination Page Number links
     *
     * @access  public
     * @return  mixed   Markup for page number links
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
                $pagination .= static::$template['active_start'].$i.static::$template['active_end'];
            }
            else
            {
                $url = ($i == 1) ? '' : '/'.$i;
                $pagination .= static::$template['page_start'].\Html::anchor(rtrim(static::$pagination_url, '/').$url.'/'.static::$suffix_url, $i).static::$template['page_end'];
            }
        }

        return $pagination;
    }

    /**
     * Pagination "Next" link
     *
     * @static
     * @access  public
     * @param   string  $value The text displayed in link
     * @return  mixed   The next link
     */
    public static function next_link($value)
    {
        if (static::$total_pages == 1)
        {
            return '';
        }

        if (static::$current_page == static::$total_pages)
        {
            return static::$template['disabled']['next_start'].$value.static::$template['next_mark'].static::$template['disabled']['next_end'];
        }
        else
        {
            $next_page = static::$current_page + 1;
            return static::$template['next_start'].\Html::anchor(rtrim(static::$pagination_url, '/').'/'.$next_page.'/'. static::$suffix_url, $value.static::$template['next_mark']).static::$template['next_end'];
        }
    }

    /**
     * Pagination "Previous" link
     *
     * @static
     * @access  public
     * @param   string  $value The text displayed in link
     * @return  mixed   The previous link
     */
    public static function prev_link($value)
    {
        if (static::$total_pages == 1)
        {
            return '';
        }

        if (static::$current_page == 1)
        {
            return static::$template['disabled']['previous_start'].static::$template['previous_mark'].$value.static::$template['disabled']['previous_end'];
        }
        else
        {
            $previous_page = static::$current_page - 1;
            $previous_page = ($previous_page == 1) ? '' : '/'.$previous_page;
            return static::$template['previous_start'].\Html::anchor(rtrim(static::$pagination_url, '/').$previous_page.'/'.static::$suffix_url, static::$template['previous_mark'].$value).static::$template['previous_end'];
        }
    }
    
    /**
     * Build query string
     * 
     * @static
     * @access  public
     * @deprecated
     * @param   mixed   $values
     * @return  string 
     */
    public static function build_get_query($values) 
    {
        return Uri::build_get_query($values);
    }

}