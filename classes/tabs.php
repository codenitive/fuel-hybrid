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
    
    protected static $instances = array();

    public static function forge($name = null, $config = array())
    {
        if (is_null($name))
        {
            $name = 'default';
        }

        if ( ! isset(static::$instances[$name]))
        {
            static::$instances[$name] = new static($name, $config);
        }

        return static::$instances[$name];
    }

    public static function instance($name)
    {
        return static::forge($name);
    }

    protected $tabs   = array();
    protected $name   = null;
    protected $config = array();

    protected function __construct($name, $config)
    {
        $this->name   = $name;
        $this->config = array_merge($this->config, $config);
    }

    public function append($title, $content = '')
    {
        return $this->add($title, $content, false);
    }

    public function prepend($title, $content = '')
    {
        return $this->add($title, $content, true);
    }

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

    public function __toString()
    {
        return $this->render();
    }

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