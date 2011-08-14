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
 * @category    Template_Frontend
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Template_Frontend extends Template_Abstract {

    /**
     * Initiate a new template using factory
     *
     * Example:
     * <code>$template = \Hybrid\Template_Frontend::factory();</code>
     *
     * @static
     * @access  public
     * @param   string  $theme
     * @param   string  $filename
     * @return  void
     */
    public static function factory($folder = null, $filename = null)
    {
        return new static($folder, $filename);
    }

    /**
     * Initiate a new template object
     *
     * @access  public
     * @param   string  $theme
     * @param   string  $filename
     * @return  void
     * @throws  \Fuel_Exception
     */
    public function __construct($theme = null, $filename = null)
    {
        $available_folders = array_keys(static::$config['frontend']);

        if (empty($available_folders))
        {
            throw new \Fuel_Exception("\\Hybrid\\Template configuration is not completed");
        }

        if (in_array(trim(strval($theme)), $available_folders))
        {
            $this->folder = static::$config['frontend'][$theme];
        }
        else
        {
            throw new \Fuel_Exception("Requested Template folder is not available");
        }

        if (!empty($filename))
        {
            $this->filename = $filename;
        }
        else 
        {
            $this->filename = static::$config['default_filename'];
        }
        
        if (!!static::$config['load_assets'])
        {
            $this->load_assets();
        }

        $this->view = \Hybrid\View::factory();
    }

    /**
     * Load partial view
     *
     * @access  public
     * @param   string  $filename
     * @param   array   $data
     * @return  string
     */
    public function partial($filename, $data = null)
    {
        $view = \Hybrid\View::factory();
        $view->set_path($this->folder);
        $view->set_filename($filename);
        $view->auto_encode(static::$config['auto_encode']);

        if (is_array($data) and count($data) > 0)
        {
            $view->set($data);
        }
        
        return $view->render();
    }

    /**
     * Render self::view
     *
     * @access  public
     * @return  string
     */
    public function render()
    {
        $this->view->set_path($this->folder);
        $this->view->set_filename($this->filename);
        $this->view->auto_encode(static::$config['auto_encode']);
        $this->view->set(array(
            'template' => $this,
        ));

        return $this->view->render();
    }

}