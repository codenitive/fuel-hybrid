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
 * @category    Controller_Rest
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
 
abstract class Controller_Rest extends \Fuel\Core\Controller 
{    
    /**
     * Rest format to be used
     * 
     * @access  protected
     * @var     string
     */
    protected $rest_format          = null;
    
    /**
     * Set the default content type using PHP Header
     * 
     * @access  protected
     * @var     bool
     */
    protected $set_content_type     = true;

    /**
     * Run ACL check and redirect user automatically if user doesn't have the privilege
     * 
     * @access  public
     * @param   mixed   $resource
     * @param   string  $type 
     * @param   string  $name
     */
    final protected function acl($resource, $type = null, $name = null) 
    {
        $status = Acl::instance($name)->access_status($resource, $type);

        switch ($status) 
        {
            case 401 :
                $this->response(array('text' => "You doesn't have privilege to do this action"), 401);
                print $this->response->body;
                exit();
            break;
        }
    }

    /**
     * This method will be called after we route to the destinated method
     * 
     * @access  public
     */
    public function before() 
    {
        $this->language   = Factory::get_language();
        $this->user       = Auth::instance('user')->get();
        \Fuel::$profiling = false;

        \Event::trigger('controller_before');
        
        if (Request::main() !== Request::active()) 
        {
            $this->set_content_type = false;
        }
        
        Restserver::auth();

        return parent::before();
    }

    /**
     * This method will be called after we route to the destinated method
     * 
     * @access  public
     * @param   mixed   $response
     */
    public function after($response) 
    {
        \Event::trigger('controller_after');
        
        if ( ! $response instanceof \Response)
        {
            $response = $this->response;    
        }

        return parent::after($response);
    }

    /**
     * Requests are not made to methods directly The request will be for an "object".
     * this simply maps the object and method to the correct Controller method.
     * 
     * @param   Request $resource
     * @param   array   $arguments
     */
    public function router($resource, $arguments) 
    {
        $pattern            = Restserver::$pattern;
        
        // Remove the extension from arguments too
        $resource           = preg_replace($pattern, '', $resource);
        
        // If they call user, go to $this->post_user();
        $controller_method  = strtolower(Input::method()).'_'.$resource;
        
        if (method_exists($this, $controller_method)) 
        {
            call_user_func(array($this, $controller_method));
        }
        else 
        {
            $this->response->status = 404;
            return ;
        }
    }

    /**
     * Takes pure data and optionally a status code, then creates the response
     * 
     * @param   array   $data
     * @param   int     $http_code
     */
    protected function response($data = array(), $http_code = 200) 
    {
        $rest_server   = Restserver::forge($data, $http_code)
            ->format($this->rest_format)
            ->execute();
        
        $this->response->body   = $rest_server->body;
        $this->response->status = $rest_server->status;
        
        if (true === $this->set_content_type) 
        {
            // Set the correct format header
            $this->response->set_header('Content-Type', Restserver::content_type($rest_server->format));
        }
    }
}