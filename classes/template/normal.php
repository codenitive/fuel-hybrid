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
 * @category    Template_Normal
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Template_Normal extends Template_Driver {

    /**
     * Initiate a new template using factory
     *
     * Example:
     * <code>$template = \Hybrid\Template_Normal::factory();</code>
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
    public function __construct($folder = null, $filename = null)
    {
        // Assets shouldn't be added in APPPATH/views at all
        if (!empty($folder) and $folder !== '_default_')
        {
            $this->set_folder($folder);
        }
        elseif (isset(static::$config['default_folder']))
        {
            $this->set_folder(static::$config['default_folder']);
        }

        if (!empty($filename) and $filename !== '_default_')
        {
            $this->set_filename($filename);
        }
        elseif (isset(static::$config['default_filename']))
        {
            $this->set_filename(static::$config['default_filename']);
        }

        $this->view = \View::factory();
    }

    /**
     * Assets shouldn't be added in APPPATH/views at all
     *
     * @access  private
     * @return  self
     * @throws  \Fuel_Exception
     */
    public function load_assets($forced_load = false)
    {
      throw new \Fuel_Exception("No asset loading for \\Hybrid\\Template_Normal");
    }

    /**
     * Set folder location
     *
     * @access  public
     * @return  self
     * @throws  \Fuel_Exception
     */
    public function set_folder($path = null)
    {
        // this is not the best way of doing it, the request is not cached and going to be slow
        // if there's a lot of paths and files
        $files = \Fuel::list_files('views/' . $path, '*.php');

        if (empty($files))
        {
            throw new \Fuel_Exception("Path {$path} does not appear to a valid folder or contain any View files");
        }
        else 
        {
            $this->folder = $path;
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
        $view = \View::factory();
        $view->set_filename(rtrim($this->folder, '/') . '/' . $filename);
        $view->auto_encode(static::$config['auto_encode']);

        if (is_array($data) and count($data) > 0)
        {
            $view->set($data);
        }

        $view->set('TEMPLATE_FOLDER', $this->folder, false);

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
        $this->view->set_filename(rtrim($this->folder, '/') . '/' . $this->filename);
        $this->view->auto_encode(static::$config['auto_encode']);
        $this->view->set('TEMPLATE_FOLDER', $this->folder, false);

        return $this->view->render();
    }

}