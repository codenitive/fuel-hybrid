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
 * @category    Controller_Frontend
 * @abstract
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
 
abstract class Controller_Frontend extends Controller {

    /**
     * Page template
     * 
     * @access  public
     * @var     string
     */
    public $template = 'frontend';
    
    /**
     * Auto render template
     * 
     * @access  public
     * @var     bool    
     */
    public $auto_render = true;

    /**
     * This method will be called after we route to the destinated method
     * 
     * @access  public
     * @return  void
     */
    public function before() 
    {
        $this->prepare_template();

        return parent::before();
    }

    /**
     * Takes pure data and optionally a status code, then creates the response
     * 
     * @access  protected
     * @param   array   $data
     * @param   int     $http_code
     * @return  void
     */
    protected function response($data = array(), $http_code = 200) 
    {
        $this->response->status = $http_code;

        $this->template->set($data);
    }

    /**
     * This method will be called after we route to the destinated method
     * 
     * @access  public
     * @param   mixed   $response
     * @return  void
     */
    public function after($response) 
    {
        return parent::after($this->render_template($response));
    }
    
    /**
     * Prepare template
     * 
     * @access  protected
     * @return  void
     */
    protected function prepare_template()
    {
        if (true === $this->auto_render)
        {
            $this->template = Template::forge($this->template);
        }
    }
    
    /**
     * Render template
     * 
     * @access  protected
     * @param   mixed   $response
     * @return  void
     */
    protected function render_template($response)
    {
        //we dont want to accidentally change our site_name
        $this->template->set(array('site_name' => \Config::get('app.site_name')));
        
        if (true === $this->auto_render and ! $response instanceof \Response)
        {
            $response       = $this->response;
            $response->body = $this->template;
        }

        return $response;
    }

}