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
 * @category    View
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class View extends \Fuel\Core\View 
{
    /**
     * @static
     * @access  protected
     * @var     string
     */
    protected static $file_path = '';

    /**
     * Set the global path.
     * 
     * example:
     * <code>
     *     \Hybrid\View::set_path($path);
     * </code>
     * 
     * @static
     * @access  public
     * @param   string  $path 
     * @return  void
     */
    public static function set_path($path) 
    {
        static::$file_path = $path;
    }

    /**
     * Sets the view filename.
     *
     * example:
     * <code>
     *     $view->set_filename($file);
     * </code>
     * 
     * @static
     * @access  public
     * @param   string  $file   a string of view filename
     * @return  self
     * @throws  FuelException
     */
    public function set_filename($file) 
    {
        // locate the view file
        switch (true) 
        {
            case ($path = \Finder::search('views', static::$file_path.$file.'.'.$this->extension)) : break;
            default :
                // set find_file's one-time-only search paths
                \Finder::instance()->flash($this->request_paths);
                
                if (($path = \Finder::search('views', $file, '.'.$this->extension, false, false)) === false)
                {
                    throw new \FuelException('The requested view could not be found: '.\Fuel::clean_path($file));
                }
            
        }

        // Store the file path locally
        $this->file_name = $path;

        return $this;
    }

}