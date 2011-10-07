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
 * @category    Template_ActiveAdmin
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

 class Template_ActiveAdmin extends Template {

    public static function factory()
    {
        return new static();
    }

    public function __construct()
    {
        
    }
    
    public static function breadcrumb()
    {
        
    }
 }