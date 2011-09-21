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

class Template_Frontend extends Template_Driver {

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
        $this->set_theme($theme);

        if (!empty($filename) and $filename !== '_default_')
        {
            $this->set_filename($filename);
        }
        else 
        {
            $this->set_filename(static::$config['default_filename']);
        }

        $this->view = \Hybrid\View::factory();
    }

    /**
     * Set theme location
     *
     * @access  public
     * @return  self
     * @throws  \Fuel_Exception
     */
    public function set_theme($theme = null)
    {
        $available_folders = array_keys(static::$config['frontend']);

        if (empty($available_folders))
        {
            throw new \Fuel_Exception("\\Hybrid\\Template configuration is not completed");
        }

        if (is_null($theme) or $theme === '_default_')
        {
            $theme = 'default';
        }

        if (in_array(trim(strval($theme)), $available_folders))
        {
            $this->set_folder(static::$config['frontend'][$theme]);
        }
        else
        {
            throw new \Fuel_Exception("Requested Template folder is not available");
        }

        return $this;
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
        $this->load_assets();
        
        $view = \Hybrid\View::factory();
        $view->set_path($this->folder);
        $view->set_filename($filename);
        $view->auto_encode(static::$config['auto_encode']);

        if (is_array($data) and count($data) > 0)
        {
            $view->set($data);
        }

        $view->set('TEMPLATE_FOLDER', $this->folder, false);
        $view->set('template', $this, false);
        
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
        $this->load_assets();

        $this->view->set_path($this->folder);
        $this->view->set_filename($this->filename);
        $this->view->auto_encode(static::$config['auto_encode']);

        $this->view->set('TEMPLATE_FOLDER', $this->folder, false);
        $this->view->set('template', $this, false);

        return $this->view->render();
    }

}