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
 * @category    Template_Abstract
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

abstract class Template_Abstract {

    /**
     * Template driver configuration
     *
     * @static
     * @access  protected
     * @var     array
     */
    protected static $config = null;
    
    /**
     * Load configurations
     *
     * @static
     * @access  public
     * @return  void
     */
    public static function _init()
    {
        if (is_null(static::$config))
        {
            \Config::load('app', 'app');
            static::$config = \Config::get('app.template', array());
        }
    }

    /**
     * Folder location
     *
     * @access  protected
     * @var     string
     */
    protected $folder       = 'default';

    /**
     * Filename
     *
     * @access  protected
     * @var     string
     */
    protected $filename     = 'index';

    /**
     * Adapter \Fuel\Core\View
     *
     * @access  public
     * @var     object
     */
    public $view            = null;

    /**
     * Load asset as subfolder of template
     *
     * @access  public
     * @return  self
     * @throws  \Fuel_Exception
     */
    public function load_assets() 
    {
        $folder_path = $this->folder . 'assets/';

        if (!\is_dir($folder_path))
        {
            throw new \Fuel_Exception("Unable to load assets at {$folder_path}");
        }
        else
        {
            $folder_path = str_replace(DOCROOT, '', $folder_path);
            \Asset::add_path($folder_path);
        }

        return $this;
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
        if (!\is_dir($path))
        {
            throw new \Fuel_Exception("Path {$path} does not appear to a valid folder");
        }
        else 
        {
            $this->folder = $path;
        }

        return $this;
    }

    /**
     * Set filename location
     *
     * @access  public
     * @return  self
     */
    public function set_filename($filename = null)
    {
        if (!empty($filename))
        {
            $this->filename = $filename;
        }

        return $this;
    }

    /**
     * Set data
     *
     * @access  public
     * @return  self
     */
    public function set($data = array())
    {
        if (is_array($data) and count($data) > 0)
        {
            $this->view->set($data);
        }

        return $this;
    }

    /**
     * Load partial view
     *
     * @abstract
     * @access  public
     * @param   string  $filename
     * @param   array   $data
     * @return  void
     */
    public abstract function partial($filename, $data = null);

    /**
     * Render self::view
     *
     * @abstract
     * @access  public
     */
    public abstract function render();

}