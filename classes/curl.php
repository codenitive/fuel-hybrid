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
 * @category    Curl
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
 
class Curl {
    
    /**
     * Initiate this class as a new object
     * 
     * @static
     * @access  public
     * @param   string  $uri
     * @param   array   $dataset
     * @return  static 
     */
    public static function forge($uri, $dataset = array())
    {
        $uri_segments   = explode(' ', $uri);
        $type           = 'GET';

        if (in_array(strtoupper($uri_segments[0]), array('DELETE', 'POST', 'PUT', 'GET'))) 
        {
            $uri        = $uri_segments[1];
            $type       = $uri_segments[0];
        }
        else
        {
            throw new \Fuel_Exception("\Hybrid\Curl: Provided {$uri} can't be processed.")
        }

        $dataset = array_merge(static::query_string($uri), $dataset);

        return new static($uri, $dataset, $type);
    }

    /**
     * Shortcode to self::forge().
     *
     * @deprecated  1.3.0
     * @static
     * @access  public
     * @param   string  $uri
     * @param   array   $dataset
     * @return  self::forge()
     */
    public static function factory($uri, $dataset = array())
    {
        \Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
        
        return static::forge($uri, $dataset);
    }
    
    /**
     * A shortcode to initiate this class as a new object using GET
     * 
     * @static
     * @access  public
     * @param   string  $uri
     * @param   array   $dataset
     * @return  static 
     */
    public static function get($uri, $dataset = array())
    {
        $dataset = array_merge(static::query_string($uri), $dataset);
        
        return new static($uri, $dataset, 'GET');
    }
    
    /**
     * A shortcode to initiate this class as a new object using POST
     * 
     * @static
     * @access  public
     * @param   string  $uri
     * @param   array   $dataset
     * @return  static 
     */
    public static function post($uri, $dataset = array())
    {
        return new static($uri, $dataset, 'POST');
    }
    
    /**
     * A shortcode to initiate this class as a new object using PUT
     * 
     * @static
     * @access  public
     * @param   string  $uri
     * @param   array   $dataset
     * @return  static 
     */
    public static function put($url, $dataset = array())
    {
        return new static($uri, $dataset, 'PUT');
    }
    
    /**
     * A shortcode to initiate this class as a new object using DELETE
     * 
     * @static
     * @access  public
     * @param   string  $uri
     * @param   array   $dataset
     * @return  static 
     */
    public static function delete($url, $dataset = array())
    {
        return new static($uri, $dataset, 'DELETE');
    }
    
    /**
     * Generate query string
     * 
     * @static
     * @access  protected
     * @param   string  $uri
     * @return  array 
     */
    protected static function query_string($uri)
    {
        $query_dataset = array();
        $query_string  = parse_url($uri);
        
        if (isset($query_string['query'])) 
        {
            $uri = $query_string['path'];
            parse_str($query_string['query'], $query_dataset);
        }
        
        return $query_dataset;
    }
    
    protected $request_uri      = '';
    protected $adapter          = null;
    protected $request_data     = array();
    protected $request_method   = '';
    
    /**
     * Construct a new object
     * 
     * @access  public
     * @param   string  $uri
     * @param   array   $dataset
     * @param   string  $type 
     */
    public function __construct($uri, $dataset = array(), $type = 'GET')
    {
        $this->request_uri    = $uri;
        $this->request_method = $type;
        $this->request_data   = $dataset;
        $this->adapter        = curl_init();

        $option = array();

        switch ($type)
        {
            case 'GET' :
                $option[CURLOPT_HTTPGET] = true;
            break;
            
            case 'POST' :
                $option[CURLOPT_POST]       = true;
                $option[CURLOPT_POSTFIELDS] = $dataset;
            break;   
        }

        $this->setopt($option);
    }
    
    /**
     * Set curl options
     * 
     * @access  public
     * @param   mixed   $option
     * @param   string  $value
     * @return  Curl 
     */
    public function setopt($option, $value = null)
    {
        if (is_array($option))
        {
            curl_setopt_array($this->adapter, $option);
        }
        elseif (is_string($option) and isset($value))
        {
            curl_setopt($this->adapter, $option, $value);
        }
        
        return $this;
    }
    
    /**
     * Execute the Curl request and return the output
     * 
     * @access  public
     * @return  object
     */
    public function execute()
    {
        $uri              = $this->request_uri . '?' . http_build_query($this->request_data, '', '&');
        curl_setopt($this->adapter, CURLOPT_URL, $uri); 
        
        $info             = curl_getinfo($this->adapter);
        
        $response         = new \stdClass();
        $response->body   = $response->raw_body = curl_exec($this->adapter);
        $response->status = $info['http_code'];
        $response->info   = $info;
        
        // clean up curl session
        curl_close($this->adapter);
        
        return $response;
    }
    
}