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
 * @category    Request
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
 
class Request extends \Fuel\Core\Request {

    /**
     * Generates a new `curl` request without going through HTTP connection, 
     * this allow user session can be shared between both request `client` and `server`. 
     * 
     * The request is then set to be the active request. 
     *
     * Usage:
     *
     * <code>\Hybrid\Request::connect('GET controller/method?hello=world');</code>
     *
     * @access  public
     * @param   string  $uri - The URI of the request
     * @param   array   $dataset - Set a dataset for GET, POST, PUT or DELETE
     * @return  object  \Hybrid\Request instance
     */
    public static function connect($uri, $dataset = array()) 
    {
        $uri_segments = explode(' ', $uri);
        $type         = Input::method();

        if (in_array(strtoupper($uri_segments[0]), array('DELETE', 'POST', 'PUT', 'GET'))) 
        {
            $uri  = $uri_segments[1];
            $type = $uri_segments[0];
        }

        $query_dataset = array();
        $query_string  = parse_url($uri);

        if (isset($query_string['query'])) 
        {
            $uri = $query_string['path'];
            parse_str($query_string['query'], $query_dataset);
        }

        $dataset = array_merge($query_dataset, $dataset);

        logger(\Fuel::L_INFO, 'Creating a new Request with URI = "' . $uri . '"', __METHOD__);

        static::$active = new static($uri, true, $dataset, $type);

        if ( ! static::$main) 
        {
            logger(\Fuel::L_INFO, 'Setting main Request', __METHOD__);
            static::$main = static::$active;
        }

        return static::$active;
    }

    /**
     * Request dataset
     * 
     * @access  protected
     * @var     array
     */
    protected $request_data     = array();
    
    /**
     * Request method
     * 
     * @access  protected
     * @var     string
     */
    protected $request_method   = '';

    /**
     * Creates the new Request object by getting a new URI object, then parsing
     * the uri with the Route class. Once constructed we need to save the method 
     * and GET/POST/PUT or DELETE dataset
     * 
     * @param   string  $uri - The URI of the request
     * @param   bool    $route -if true use routes to process the URI
     * @param   string  $type - GET|POST|PUT|DELETE
     * @param   array   $dataset 
     */
    public function __construct($uri, $route, $dataset = array(), $type = null) 
    {
        parent::__construct($uri, $route);

        // store this construct method and data staticly
        if ( ! is_null($type))
        {
            $this->request_method = $type;
            $this->request_data   = $dataset;
        }

        $this->response         = NULL;
    }

    /**
     * This executes the request and sets the output to be used later. 
     * Cleaning up our request after executing \Request::execute()
     * 
     * Usage:
     * 
     * <code>$exec = \Hybrid\Request::connector('PUT controller/method?hello=world')->execute();
     * \Debug::dump($exec);</code>
     * 
     * @param   array|null  $method_params  An array of parameters to pass to the method being executed
     * @return  object      containing $data and HTTP Response $status
     * @see     \Request::execute()
     */
    public function execute($method_params = null) 
    {
        // Since this just a imitation of curl request, \Hybrid\Input need to know the 
        // request method and data available in the connection.
        Input::connect($this->request_method, $this->request_data);

        $execute = parent::execute($method_params);

        // We need to clean-up any request object transfered to \Hybrid\Input so that
        // any following request to \Hybrid\Input will redirected to \Fuel\Core\Input
        Input::disconnect();
        $this->request_method   = '';
        $this->request_data     = array();

        return $execute;
    }

}