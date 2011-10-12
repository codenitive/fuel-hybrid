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
 * @category    Uri
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Uri extends \Fuel\Core\Uri 
{
    /**
     * Build query string
     * 
     * @static
     * @access  public
     * @param   mixed   $values
     * @param   string  $start_with     Default string set to ?
     * @return  string 
     */
    public static function build_get_query($values, $start_with = '?') 
    {
        $dataset = array ();
        
        $check_get_input = function($value, & $dataset) 
        {
            $data = Input::get($value);
            
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
        
        return $start_with . implode('&', $dataset);
    }
    
}