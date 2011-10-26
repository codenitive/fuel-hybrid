<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package     Fuel
 * @version     1.0
 * @author      Dan Horrigan <http://dhorrigan.com>
 * @license     MIT License
 * @copyright   2010 - 2011 Fuel Development Team
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
 * @category    Pagination
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Pagination 
{
    /**
     * Cache Pagniation instance so we can reuse it on multiple request. 
     * 
     * @static
     * @access  protected
     * @var     array
     */
    protected static $instances = array();

    /**
     * Init
     *
     * Loads in the config and sets the variables
     *
     * @access  public
     * @return  void
     */
    public static function _init()
    {
        \Lang::load('pagination', true);
        \Config::load('hybrid', 'hybrid');
    }

    /**
     * Initiate a new Pagination instance.
     * 
     * @static
     * @access  public
     * @param   string  $name
     * @param   array   $config
     * @return  object  Pagination
     */
    public static function forge($name = null, $config = array())
    {
        if (is_array($name))
        {
            $config = $name;
            $name   = null;
        }

        if (null === $name)
        {
            $name = md5(\Request::active()->route->translation);
        }

        if ( ! isset(static::$instances[$name]))
        {
            static::$instances[$name] = new static($config);
        }

        return static::$instances[$name];
    }

    /**
     * Shortcode to self::forge().
     *
     * @deprecated  1.3.0
     * @static
     * @access  public
     * @param   string  $name
     * @param   array   $config
     * @return  object  Pagination
     */
    public static function factory($name = null, $config = array())
    {
        \Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
        
        return static::factory($name, $config);
    }

    protected function __construct($config = array()) 
    {
        $config = \Config::get('pagination', array()) + $config;
        $config = \Config::get('hybrid.pagination', array()) + $config;

        // configure configuration
        foreach ($config as $key => $value)
        {
            if ($key == 'template')
            {
                $this->template = array_merge($this->template, $config['template']);
                continue;
            }

            if (property_exists($this, $key))
            {
                $this->{$key} = $value;
            }
        }

        if ( ! empty($this->uri))
        {
            $segments = explode('/', str_replace(Uri::base(), '', $this->uri));
            $key = array_search(':page', $segments);

            if (false !== $key)
            {
                $this->uri_segment = intval($key) + 1;
            }

            $this->current_page = (int) \Uri::segment($this->uri_segment);
        }
        else
        {
            $get      = \Input::get();
            $segments = \Uri::segments();

            if (null === $this->uri_segment)
            {
                if ( ! isset($get['page']))
                {
                    $get['page'] = 1;
                }

                $this->current_page = (int) $get['page'];
                $get['page'] = ':page';
            }
            else 
            {
                // we need to build uri manually
                $key = ($this->uri_segment - 1);

                if (isset($segments[$key]))
                {
                    $this->current_page = (int) $segments[$key];
                }

                $key--;

                if ( ! isset($segments[$key]) and $key >= 0)
                {
                    $segments[$key] = \Request::active()->route->translation;
                }

                $key++;

                $segments[$key]     = ':page'; 
                $this->current_page = (int) \URI::segment($this->uri_segment);
            }
            

            if ( ! empty($get))
            {
                $get = '?'.http_build_query($get);
                $get = str_replace('page=%3Apage', 'page=:page', $get);
            }

            $this->uri = implode('/', $segments).$get;
        }

        // calculate current config
        $this->total_pages  = ceil($this->total_items / $this->per_page) ?: 1;

        if ($this->current_page > $this->total_pages)
        {
            $this->current_page = $this->total_pages;
        }
        elseif ($this->current_page < 1)
        {
            $this->current_page = 1;
        }

        // The current page must be zero based so that the offset for page 1 is 0.
        $this->offset = ($this->current_page - 1) * $this->per_page;
    }

    /**
     * The HTML for the display
     *
     * @access  protected
     * @var     array 
     */
    protected $template = array(
        'wrapper_start'  => '<div class="pagination"><ul>',
        'wrapper_end'    => '</ul></div>',
        'page_start'     => '<li class=":state">',
        'page_end'       => '</li>',
        'previous_start' => '<li class="prev :state">',
        'previous_end'   => '</li>',
        'previous_mark'  => '&laquo; ',
        'next_start'     => '<li class="next :state">',
        'next_end'       => '</li>',
        'next_mark'      => ' &raquo;',
        'state'          => array(
            'previous_next' => array(
                'active'   => '',
                'disabled' => 'disabled',
            ),
            'current_page' => 'active',
        ),
    );

    /**
     * The current page
     *
     * @access  protected
     * @var     integer
     */
    protected $current_page = null;

    /**
     * Uri segment 
     *
     * @access  protected
     * @var     integer
     */
    protected $uri_segment = 3;

    /**
     * The offset that the current page starts at
     *
     * @access  protected
     * @var     integer
     */
    protected $offset = 0;

    /**
     * The number of items per page
     *
     * @access  protected
     * @var     integer
     */
    protected $per_page = 10;

    /**
     * The number of total pages
     *
     * @access  protected
     * @var     integer
     */
    protected $total_pages = 0;

    /**
     * The number of total items
     *
     * @access  protected
     * @var     integer
     */
    protected $total_items = 0;

    /**
     * The total number of links to show
     *
     * @access  protected
     * @var     integer
     */
    protected $num_links = 5;
    
    /**
     * The pagination URL (after the page number URI segment)
     * 
     * @access  protected
     * @var     string
     */
    protected $uri = null;

    /**
     * Pagination Page Number links
     *
     * @access  public
     * @return  mixed   Markup for page number links
     */
    public function page_links()
    {
        if ($this->total_pages == 1)
        {
            return '';
        }
        
        $pagination = '';
        
        // Let's get the starting page number, this is determined using num_links
        $start = (($this->current_page - $this->num_links) > 0) ? $this->current_page - ($this->num_links - 1) : 1;

        // Let's get the ending page number
        $end   = (($this->current_page + $this->num_links) < $this->total_pages) ? $this->current_page + $this->num_links : $this->total_pages;

        for ($i = $start; $i <= $end; $i++)
        {
            $text = $i;

            if ($this->current_page == $i)
            {
                $pagination .= \Str::tr($this->template['page_start'].$text.$this->template['page_end'], array(
                    'state' => $this->template['state']['current_page'],
                    'url'   => '#',
                ));
            }
            else
            {   
                if (false === stripos('<a ', $this->template['page_start']))
                {
                    $text = '<a href=":url">'.$i.'</a>';
                }
                
                $pagination .= \Str::tr($this->template['page_start'].$text.$this->template['page_end'], array(
                    'state' => '',
                    'url'   => $this->build_url($i),
                ));
            }
        }

        return $pagination;
    }

    /**
     * Pagination "Next" link
     *
     * @access  public
     * @param   string  $value The text displayed in link
     * @return  mixed   The next link
     */
    public function next_link($value)
    {
        $text = $value.$this->template['next_mark'];

        if ($this->total_pages == 1)
        {
            return '';
        }

        if ($this->current_page == $this->total_pages)
        {
            return \Str::tr($this->template['next_start'].$text.$this->template['next_end'], array(
                'state' => $this->template['state']['previous_next']['disabled'],
                'url'   => '#',
            ));
        }
        else
        {
            $next_page = $this->current_page + 1;

            if (false === stripos('<a ', $this->template['next_start']))
            {
                $text = '<a href=":url">'.$text.'</a>';
            }

            return \Str::tr($this->template['next_start'].$text.$this->template['next_end'], array(
                'state' => $this->template['state']['previous_next']['disabled'],
                'url'   => $this->build_url($next_page),
            ));
        }
    }

    /**
     * Pagination "Previous" link
     *
     * @access  public
     * @param   string  $value The text displayed in link
     * @return  mixed   The previous link
     */
    public function prev_link($value)
    {
        $text = $this->template['previous_mark'].$value;

        if ($this->total_pages == 1)
        {
            return '';
        }

        if ($this->current_page == 1)
        {
            return \Str::tr($this->template['previous_start'].$text.$this->template['previous_end'], array(
                'state' => $this->template['state']['previous_next']['disabled'],
                'url'   => '#',
            ));
        }
        else
        {
            $previous_page = $this->current_page - 1;
            //$previous_page = ($previous_page == 1) ? '' : $previous_page;
            
            if (false === stripos('<a ', $this->template['previous_start']))
            {
                $text = '<a href=":url">'.$text.'</a>';
            }

            return \Str::tr($this->template['previous_start'].$text.$this->template['previous_end'], array(
                'state' => $this->template['state']['previous_next']['active'],
                'url'   => $this->build_url($previous_page),
            ));
        }
    }

    public function __get($name)
    {
        if (in_array($name, array('offset', 'per_page', 'current_page')))
        {
            return $this->{$name};
        }
        else
        {
            throw new \FuelException(__CLASS__."::{$name} is not accessible.");
        }
    }

    public function __toString()
    {
        return $this->render();
    }

    public function render()
    {
        if ($this->total_pages == 1)
        {
            return '';
        }

        $pagination  = $this->template['wrapper_start'];
        $pagination .= $this->prev_link(\Lang::get('pagination.previous'));
        $pagination .= $this->page_links();
        $pagination .= $this->next_link(\Lang::get('pagination.next'));
        $pagination .= $this->template['wrapper_end'];

        return $pagination;
    }

    protected function build_url($page_id)
    {
        return \Uri::create(\Str::tr($this->uri, array('page' => $page_id)));
    }
    
    /**
     * Build query string
     * 
     * @deprecated
     * @static
     * @access  public
     * @param   mixed   $values
     * @return  string 
     */
    public static function build_get_query($values) 
    {
        return Uri::build_get_query($values);
    }

}