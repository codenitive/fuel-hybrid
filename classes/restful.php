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
 * @category    Restful
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */
class Restful {
	
	// List all supported methods, the first will be the default format
	protected static $_supported_formats = array(
		'xml' => 'application/xml',
		'rawxml' => 'application/xml',
		'json' => 'application/json',
		'serialize' => 'application/vnd.php.serialized',
		'php' => 'text/plain',
		'html' => 'text/html',
		'csv' => 'application/csv'
	);
	
	public static $pattern = '';

	public static function _init()
	{
		static::$pattern = sprintf('/\.(%s)$/', implode('|', array_keys(static::$_supported_formats)));
		\Config::load('rest', true);
	}
	
	public static function factory($data = array(), $http_code = 200)
	{
		return new static($data, $http_code);
	}
	
	public static function is_rest()
	{
		$pattern = static::$pattern;
		$resource = \Request::active()->action;

		// Check if a file extension is used
		if (preg_match($pattern, $resource, $matches) || static::_detect_format() != '')
		{
			return true;
		}
		else 
		{
			return false;
		}
	}
	
	public static function content_type($format)
	{
		if (!array_key_exists($format, static::$_supported_formats))
		{
			$format = 'html';
		}
		
		return static::$_supported_formats[$format];
	}
	
	public static function auth()
	{
		if (\Config::get('rest.auth') == 'basic')
		{
			static::_prepare_basic_auth();
		}
		elseif (\Config::get('rest.auth') == 'digest')
		{
			static::_prepare_digest_auth();
		}
	}
	
	public function __construct($data = array(), $http_code = 200)
	{
		$this->_data = $data;
		$this->_http_status = $http_code;
	}
	
	protected $rest_format = null; // Set this in a controller to use a default format
	private $_data = array();
	private $_http_status = 200;
	
	public function format($rest_format = '')
	{
		$rest_format = trim(strtolower($rest_format));
		
		if (in_array($rest_format, static::$_supported_formats))
		{
			$this->rest_format = $rest_format;
		}
		
		return $this;
	}
	
	public function execute()
	{
		if (empty($this->_data))
		{
			$this->_http_status = 404;
		}
		
		$pattern = static::$pattern;
		$resource = \Request::active()->action;
		
		$format = $this->rest_format;
		$response = new \stdClass();
		$response->status = $this->_http_status;
		
		// Check if a file extension is used
		if (preg_match($pattern, $resource, $matches)) 
		{
			// Remove the extension from arguments too
			$resource = preg_replace($pattern, '', $resource);

			$format = $matches[1];
		} 
		
		if (is_null($format))
		{
			// Which format should the data be returned in?
			$format = $this->_detect_format();
		}
		
		$response->format = $format;
		
		// If the format method exists, call and return the output in that format
		if (method_exists('\\Format', 'to_'.$format))
		{
			$response->body = \Format::factory($this->_data)->{'to_'.$format}();
		}

		// Format not supported, output directly
		else 
		{
			$response->body = (string) $this->_data;
		}
		
		return $response;
	}
	
	protected static function _check_login($username = '', $password = null)
	{
		if (empty($username))
		{
			return false;
		}

		$valid_logins = & \Config::get('rest.valid_logins');

		if (!array_key_exists($username, $valid_logins))
		{
			return false;
		}

		// If actually null (not empty string) then do not check it
		if ($password !== null && $valid_logins[$username] != $password)
		{
			return false;
		}

		return true;
	}
	
	protected static function _prepare_basic_auth()
	{
		$username = null;
		$password = null;

		// mod_php
		if (\Hybrid\Input::server('PHP_AUTH_USER'))
		{
			$username = \Hybrid\Input::server('PHP_AUTH_USER');
			$password = \Hybrid\Input::server('PHP_AUTH_PW');
		}

		// most other servers
		elseif (\Hybrid\Input::server('HTTP_AUTHENTICATION'))
		{
			if (strpos(strtolower(\Hybrid\Input::server('HTTP_AUTHENTICATION')), 'basic') === 0)
			{
				list($username, $password) = explode(':', base64_decode(substr(\Hybrid\Input::server('HTTP_AUTHORIZATION'), 6)));
			}
		}

		if (!static::_check_login($username, $password))
		{
			static::_force_login();
		}
	}

	protected static function _prepare_digest_auth()
	{
		$uniqid = uniqid(""); // Empty argument for backward compatibility
		// We need to test which server authentication variable to use
		// because the PHP ISAPI module in IIS acts different from CGI
		if (\Hybrid\Input::server('PHP_AUTH_DIGEST'))
		{
			$digest_string = \Hybrid\Input::server('PHP_AUTH_DIGEST');
		}
		elseif (\Hybrid\Input::server('HTTP_AUTHORIZATION'))
		{
			$digest_string = \Hybrid\Input::server('HTTP_AUTHORIZATION');
		}
		else
		{
			$digest_string = "";
		}

		/* The $_SESSION['error_prompted'] variabile is used to ask
		  the password again if none given or if the user enters
		  a wrong auth. informations. */
		if (empty($digest_string))
		{
			static::_force_login($uniqid);
		}

		// We need to retrieve authentication informations from the $auth_data variable
		preg_match_all('@(username|nonce|uri|nc|cnonce|qop|response)=[\'"]?([^\'",]+)@', $digest_string, $matches);
		$digest = array_combine($matches[1], $matches[2]);

		if (!array_key_exists('username', $digest) || !static::_check_login($digest['username']))
		{
			static::_force_login($uniqid);
		}

		$valid_logins = & \Config::get('rest.valid_logins');
		$valid_pass = $valid_logins[$digest['username']];

		// This is the valid response expected
		$A1 = md5($digest['username'] . ':' . \Config::get('rest.realm') . ':' . $valid_pass);
		$A2 = md5(strtoupper(\Hybrid\Input::method()) . ':' . $digest['uri']);
		$valid_response = md5($A1 . ':' . $digest['nonce'] . ':' . $digest['nc'] . ':' . $digest['cnonce'] . ':' . $digest['qop'] . ':' . $A2);

		if ($digest['response'] != $valid_response)
		{
			header('HTTP/1.0 401 Unauthorized');
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}
	}

	/**
	 * Detect which format should be used to output the data
	 * 
	 * @return string
	 */
	protected static function _detect_format()
	{
		// A format has been passed as an argument in the URL and it is supported
		if (\Hybrid\Input::get_post('format') && static::$_supported_formats[\Hybrid\Input::get_post('format')])
		{
			return \Hybrid\Input::get_post('format');
		}

		// Otherwise, check the HTTP_ACCEPT (if it exists and we are allowed)
		if (\Config::get('rest.ignore_http_accept') === false && \Hybrid\Input::server('HTTP_ACCEPT'))
		{
			// Check all formats against the HTTP_ACCEPT header
			foreach (array_keys(static::$_supported_formats) as $format)
			{
				// Has this format been requested?
				if (strpos(\Hybrid\Input::server('HTTP_ACCEPT'), $format) !== false)
				{
					// If not HTML or XML assume its right and send it on its way
					if ($format != 'html' && $format != 'xml')
					{
						return $format;
					}

					// HTML or XML have shown up as a match
					else
					{
						// If it is truely HTML, it wont want any XML
						if ($format == 'html' && strpos(\Hybrid\Input::server('HTTP_ACCEPT'), 'xml') === false)
						{
							return $format;
						}

						// If it is truely XML, it wont want any HTML
						elseif ($format == 'xml' && strpos(\Hybrid\Input::server('HTTP_ACCEPT'), 'html') === false)
						{
							return $format;
						}
					}
				}
			}
		} // End HTTP_ACCEPT checking
	}
	
	/**
	 * Detect language(s) should be used to output the data
	 * 
	 * @return string
	 */
	protected static function _detect_lang()
	{
		if (!$lang = \Hybrid\Input::server('HTTP_ACCEPT_LANGUAGE'))
		{
			return null;
		}

		// They might have sent a few, make it an array
		if (strpos($lang, ',') !== false)
		{
			$langs = explode(',', $lang);

			$return_langs = array();
			$i = 1;
			foreach ($langs as $lang)
			{
				// Remove weight and strip space
				list($lang) = explode(';', $lang);
				$return_langs[] = trim($lang);
			}

			return $return_langs;
		}

		// Nope, just return the string
		return $lang;
	}
	
	/**
	 * 
	 * @param string $nonce 
	 */
	protected static function _force_login($nonce = '')
	{
		header('HTTP/1.0 401 Unauthorized');
		header('HTTP/1.1 401 Unauthorized');

		if (\Config::get('rest.auth') == 'basic')
		{
			header('WWW-Authenticate: Basic realm="' . \Config::get('rest.realm') . '"');
		}
		elseif (\Config::get('rest.auth') == 'digest')
		{
			header('WWW-Authenticate: Digest realm="' . \Config::get('rest.realm') . '" qop="auth" nonce="' . $nonce . '" opaque="' . md5(\Config::get('rest.realm')) . '"');
		}

		exit('Not authorized.');
	}
	
}