<?php

/**
 * Fuel
 *
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
abstract class Controller_Rest extends \Fuel\Core\Controller_Rest {

	protected $set_content_type = true; // set the default content type using PHP Header

	final protected function _acl($resource, $type = null) {
		$status = \Hybrid\Acl::access_status($resource, $type);

		switch ($status) {
			case 401 :
				$this->response(array('text' => 'You doesn\'t have privilege to do this action'), 401);
				print $this->response;
				exit();
				break;
		}
	}

	public function before() {
		$this->language = \Hybrid\Factory::get_language();
		$this->user = \Hybrid\Acl_User::get();

		\Event::trigger('controller_before');

		if (\Hybrid\Request::main() !== \Hybrid\Request::active()) {
			$this->set_content_type = false;
		}

		return parent::before();
	}

	public function after() {
		\Event::trigger('controller_after');

		return parent::after();
	}

	/*
	 * Remap
	 *
	 * Requests are not made to methods directly The request will be for an "object".
	 * this simply maps the object and method to the correct Controller method.
	 */

	public function router($resource, $arguments) {
		$pattern = '/\.(' . implode('|', array_keys($this->_supported_formats)) . ')$/';

		// Check if a file extension is used
		if (preg_match($pattern, $resource, $matches)) {
			// Remove the extension from arguments too
			$resource = preg_replace($pattern, '', $resource);

			$this->request->format = $matches[1];
		} else {
			// Which format should the data be returned in?
			$this->request->format = $this->_detect_format();
		}

		// If they call user, go to $this->post_user();
		$controller_method = strtolower(\Hybrid\Input::method()) . '_' . $resource;
		
		if (method_exists($this, $controller_method)) {
			call_user_func(array($this, $controller_method));
		}
		else {
			$this->response->status = 404;
			return;
		}
	}

	/*
	 * response
	 *
	 * Takes pure data and optionally a status code, then creates the response
	 */

	protected function response($data = array(), $http_code = 200) {
		if (empty($data)) {
			$this->response->status = 404;
			return;
		}

		$this->response->status = $http_code;

		// If the format method exists, call and return the output in that format
		if (method_exists('Controller_Rest', '_format_' . $this->request->format)) {
			if ($this->set_content_type === true) {
				// Set the correct format header
				$this->response->set_header('Content-Type', $this->_supported_formats[$this->request->format]);
			}

			$this->response->body($this->{'_format_' . $this->request->format}($data));
		}

		// Format not supported, output directly
		else {
			$this->response->body((string) $data);
		}
	}

	/*
	 * Detect format
	 *
	 * Detect which format should be used to output the data
	 */

	private function _detect_format() {
		// A format has been passed as an argument in the URL and it is supported
		if (\Hybrid\Input::get_post('format') and $this->_supported_formats[\Hybrid\Input::get_post('format')]) {
			return \Hybrid\Input::get_post('format');
		}

		// Otherwise, check the HTTP_ACCEPT (if it exists and we are allowed)
		if (\Config::get('rest.ignore_http_accept') === false and \Hybrid\Input::server('HTTP_ACCEPT')) {
			// Check all formats against the HTTP_ACCEPT header
			foreach (array_keys($this->_supported_formats) as $format) {
				// Has this format been requested?
				if (strpos(\Hybrid\Input::server('HTTP_ACCEPT'), $format) !== false) {
					// If not HTML or XML assume its right and send it on its way
					if ($format != 'html' and $format != 'xml') {
						return $format;
					}

					// HTML or XML have shown up as a match
					else {
						// If it is truely HTML, it wont want any XML
						if ($format == 'html' and strpos(\Hybrid\Input::server('HTTP_ACCEPT'), 'xml') === false) {
							return $format;
						}

						// If it is truely XML, it wont want any HTML
						elseif ($format == 'xml' and strpos(\Hybrid\Input::server('HTTP_ACCEPT'), 'html') === false) {
							return $format;
						}
					}
				}
			}
		} // End HTTP_ACCEPT checking
		// Well, none of that has worked! Let's see if the controller has a default
		if (!empty($this->rest_format)) {
			return $this->rest_format;
		}

		// Just use the default format
		return \Config::get('rest.default_format');
	}

}