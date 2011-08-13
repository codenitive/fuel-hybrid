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
class Html extends \Fuel\Core\Html {
    
    /**
     * Generates a html title tag
     *
     * @static
     * @access  public
     * @param   string  $content page title
     * @return  string
     */
    public static function title($content = '', $attributes = array()) 
    {
        $title = \Config::get('app.site_name');

        if (!empty($content) and is_string($content)) 
        {
            $title = sprintf('%s &mdash; %s', $content, $title);
        }

        return html_tag('title', $attributes, $title);
    }
    
}
