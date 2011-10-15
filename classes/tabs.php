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
 * @category    Tabs
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

 class Tabs {
    
    /**
     * Cache Tabs instance so we can reuse it on multiple request.
     * 
     * @static
     * @access  protected
     * @var     array
     */
    protected static $instances = array();

    /**
     * Initiate a new Tabs instance.
     * 
     * @static
     * @access  public
     * @param   string  $name
     * @param   array   $config
     * @return  object  Tabs
     */
    public static function forge($name = null, $config = array())
    {
        if (null === $name)
        {
            $name = 'default';
        }

        if ( ! isset(static::$instances[$name]))
        {
            static::$instances[$name] = new static($name, $config);
        }

        return static::$instances[$name];
    }

    /**
     * Get cached instance, or generate new if currently not available.
     *
     * @static
     * @access  public
     * @return  Tabs
     * @param   string  $name
     * @see     self::forge()
     */
    public static function instance($name)
    {
        return static::forge($name);
    }

    /**
     * List of tabs
     *
     * @access  protected
     * @var     array
     */
    protected $tabs   = array();
    
    /**
     * Name of this instance
     *
     * @access  protected
     * @var     string
     */
    protected $name   = null;
    
    /**
     * Configuration
     *
     * @access  protected
     * @var     array
     */
    protected $config = array();

    /**
     * Construct a new instance
     *
     * @access  protected
     * @param   string  $name
     * @param   array   $config
     * @return  void
     */
    protected function __construct($name, $config)
    {
        $this->name   = $name;
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Append a new tab
     *
     * @access  public
     * @param   string  $title
     * @param   string  $content
     * @return  self
     * @see     self::add()
     */
    public function append($title, $content = '')
    {
        return $this->add($title, $content, false);
    }

    /**
     * Prepend a new tab
     *
     * @access  public
     * @param   string  $title
     * @param   string  $content
     * @return  self
     * @see     self::add()
     */
    public function prepend($title, $content = '')
    {
        return $this->add($title, $content, true);
    }

    /**
     * Add a new tab, prepending or appending
     *
     * @access  public
     * @param   string  $title
     * @param   string  $content
     * @param   bool    $prepend
     * @return  self
     */
    public function add($title, $content = '', $prepend = false)
    {
        if (empty($title))
        {
            throw new \FuelException("\Hybrid\Tabs: Unable to add empty tab.");
        }

        if (empty($content) or ! strval($content))
        {
            $content = '';
        }

        $data = (object) array(
            'title'   => $title,
            'slug'    => \Inflector::friendly_title($title, '-', true),
            'content' => $content,
        );

        if (true === $prepend)
        {
            array_shift($this->tabs, $data);
        }
        else
        {
            array_push($this->tabs, $data);
        }

        return $this;
    }

    /**
     * Render self::view
     *
     * @access  public
     * @see     self::render()
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Render Tabs as a view
     *
     * @access  public
     * @return  string
     */
    public function render()
    {
        $title   = '<ul class="tabs">';
        $content = '<div class="pill-content">';

        foreach ($this->tabs as $count => $tab)
        {
            $active = ($count === 0 ? 'class="active"' : '');
            $title .= \Str::tr('<li :active><a href="#:slug">:title</a></li>', array('active' => $active, 'slug' => $tab->slug, 'title' => $tab->title));
            $content .= \Str::tr('<div :active id=":slug">:content</div>', array('active' => $active, 'slug' => $tab->slug, 'content' => $tab->content));
        }

        $title   .= '</ul>';
        $content .= '</div>';

        return '<div id="tab_'.ltrim($this->name, 'tab_').'">'.$title.$content.'</div>';
    }

 }