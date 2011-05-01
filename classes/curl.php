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
	 *
	 * @param string $uri
	 * @param array $dataset
	 * @return static 
	 */
	public static function factory($uri, $dataset = array())
	{
		$uri_segments = explode(' ', $uri);
		$type = 'GET';

		if (in_array(strtoupper($uri_segments[0]), array('DELETE', 'POST', 'PUT', 'GET'))) 
		{
			$uri = $uri_segments[1];
			$type = $uri_segments[0];
		}

		$dataset = array_merge(static::_query_string($uri), $dataset);

		return new static($uri, $dataset, $type);
	}
	
	/**
	 *
	 * @param type $uri
	 * @param type $dataset
	 * @return static 
	 */
	public static function get($uri, $dataset)
	{
		$dataset = array_merge(static::_query_string($uri), $dataset);
		
		return new static($uri, $dataset, 'GET');
	}
	
	/**
	 *
	 * @param type $uri
	 * @param type $dataset
	 * @return static 
	 */
	public static function post($uri, $dataset)
	{
		return new static($uri, $dataset, 'POST');
	}
	
	/**
	 *
	 * @param type $url
	 * @param type $dataset
	 * @return static 
	 */
	public static function put($url, $dataset)
	{
		return new static($uri, $dataset, 'PUT');
	}
	
	/**
	 *
	 * @param type $url
	 * @param type $dataset
	 * @return static 
	 */
	public static function delete($url, $dataset)
	{
		return new static($uri, $dataset, 'DELETE');
	}
	
	/**
	 *
	 * @param type $uri
	 * @return array 
	 */
	protected static function _query_string($uri)
	{
		$query_dataset = array();
		$query_string = parse_url($uri);

		if (isset($query_string['query'])) 
		{
			$uri = $query_string['path'];
			parse_str($query_string['query'], $query_dataset);
		}
		
		return $query_dataset;
	}
	
	protected $_request_uri = '';
	protected $_instance = null;
	protected $_request_data = array();
	protected $_request_method = '';
	
	/**
	 *
	 * @param type $uri
	 * @param type $dataset
	 * @param type $type 
	 */
	public function __construct($uri, $dataset = array(), $type = array())
	{
		$this->_request_uri = $uri;
		$this->_request_method = $type;
		$this->_request_data = $dataset;
		$this->_instance = curl_init();
	}
	
	/**
	 *
	 * @param type $option
	 * @param type $value
	 * @return Curl 
	 */
	public function setopt($option, $value)
	{
		if (is_array($option))
		{
			foreach ($option as $key => $value)
			{
				curl_setopt($this->_instance, $key, $value);
			}
		}
		
		if (is_string($option) and isset($value))
		{
			curl_setopt($this->_instance, $option, $value);
		}
		
		return $this;
	}
	
	/**
	 *
	 * @return object
	 */
	public function execute()
	{
		$response = new \stdClass();
		
		curl_setopt($this->_instance, CURLOPT_URL, $this->_request_uri.'?'.http_build_query($this->_request_data, '', '&')); 
		$info = curl_getinfo($this->_instance);
		
		$response->body = curl_exec($this->_instance);
		$response->status = $info['http_code'];
		
		// clean up curl session
		curl_close($this->_instance);
		
		return $response;
	}
	
}
