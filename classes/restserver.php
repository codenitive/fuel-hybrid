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
 * @category    Restserver
 * @author      Mior Muhammad Zaki <crynobone@gmail.com>
 */

class Restserver 
{
	/** 
	 * List all supported methods, the first will be the default format
	 * 
	 * @static
	 * @access  protected
	 * @var     array 
	 */
	protected static $supported_formats = array(
		'xml'        => 'application/xml',
		'rawxml'     => 'application/xml',
		'json'       => 'application/json',
		'jsonp'      => 'text/javascript',
		'serialized' => 'application/vnd.php.serialized',
		'php'        => 'text/plain',
		'html'       => 'text/html',
		'csv'        => 'application/csv'
	);
	
	/**
	 * Regular Expression pattern to detect based on file extension
	 * 
	 * @static
	 * @access  public
	 * @var     string 
	 */
	public static $pattern = '';

	/**
	 * Load the configuration before anything else.
	 * 
	 * @static
	 * @access  public
	 * @return  void
	 */
	public static function _init()
	{
		static::$pattern = sprintf('/\.(%s)$/', implode('|', array_keys(static::$supported_formats)));
		\Config::load('rest', true);
	}
	
	/**
	 * A shortcode to initiate this class as a new object
	 * 
	 * @static
	 * @access  public
	 * @param   array   $data
	 * @param   int     $http_code
	 * @return  static 
	 */
	public static function forge($data = array(), $http_code = 200)
	{
		return new static($data, $http_code);
	}

	/**
	 * A shortcode to initiate this class as a new object
	 * 
	 * @static
	 * @access  public
	 * @param   array   $data
	 * @param   int     $http_code
	 * @return  static 
	 */
	public static function make($data = array(), $http_code = 200)
	{
		return static::forge($data, $http_code);
	}

	/**
	 * Shortcode to self::forge().
	 *
	 * @deprecated  1.3.0
	 * @static
	 * @access  public
	 * @param   array   $data
	 * @param   int     $http_code
	 * @return  self::forge()
	 */
	public static function factory($data = array(), $http_code = 200)
	{
		\Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
		
		return static::forge($data, $http_code);
	}
	
	/**
	 * Check whether current request is rest
	 * 
	 * @static
	 * @access  public
	 * @return  bool
	 */
	public static function is_rest_call()
	{
		$pattern  = static::$pattern;
		$resource = \Request::active()->action;

		// Check if a file extension is used
		return preg_match($pattern, $resource, $matches) or '' != static::detect_format();
	}
	
	/**
	 * Get content-type
	 * 
	 * @static
	 * @access  public
	 * @param   string $format
	 * @return  string
	 */
	public static function content_type($format)
	{
		if ( ! array_key_exists($format, static::$supported_formats))
		{
			$format = 'html';
		}
		
		return static::$supported_formats[$format];
	}
	
	/**
	 * Run authentication
	 * 
	 * @static
	 * @access  public
	 */
	public static function auth()
	{
		switch (\Config::get('rest.auth'))
		{
			case 'basic' :
				static::prepare_basic_auth();
			break;
			
			case 'digest' :
				static::prepare_digest_auth();
			break;
		}
	}
	
	/**
	 * Initiate a new object
	 * 
	 * @access  public
	 * @param   array   $data
	 * @param   int     $http_code
	 */
	public function __construct($data = array(), $http_code = 200)
	{
		$this->data        = $data;
		$this->http_status = $http_code;
	}
	
	/**
	 * Rest format to be used
	 * 
	 * @access  protected
	 * @var     string
	 */
	protected $rest_format = null;
	
	/**
	 * Dataset for output
	 * 
	 * @access  protected
	 * @var     array 
	 */
	protected $data = array();
	
	/**
	 * HTTP Response status
	 * 
	 * @access  protected
	 * @var     int
	 */
	protected $http_status = 200;
	
	/**
	 * Set the rest format
	 * 
	 * @access  public
	 * @param   string  $rest_format
	 * @return  Restserver 
	 */
	public function format($rest_format = '')
	{
		if (null === $rest_format or empty($rest_format))
		{
			return $this;
		}

		$rest_format = trim(strtolower($rest_format));
		
		if (in_array($rest_format, static::$supported_formats))
		{
			$this->rest_format = $rest_format;
		}
		else
		{
			throw new \FuelException(__METHOD__.": {$rest_format} is not a valid REST format.");
		}
		
		return $this;
	}
	
	/**
	 * Execute the Rest request and return the output
	 * 
	 * @access  public
	 * @return  object
	 */
	public function execute()
	{
		if (empty($this->data))
		{
			$this->http_status = 404;
		}
		
		$pattern          = static::$pattern;
		$resource         = \Request::active()->action;
		
		$format           = $this->rest_format;
		$response         = new \stdClass();
		$response->status = $this->http_status;
		
		// Check if a file extension is used
		if (preg_match($pattern, $resource, $matches)) 
		{
			// Remove the extension from arguments too
			$resource = preg_replace($pattern, '', $resource);
			
			$format   = $matches[1];
		} 
		
		if (null === $format)
		{
			// Which format should the data be returned in?
			$format = $this->detect_format();
		}
		
		$response->format = $format;
		
		// If the format method exists, call and return the output in that format
		if (method_exists('\\Format', 'to_'.$format))
		{
			$response->body = \Format::forge($this->data)->{'to_'.$format}();
		}

		// Format not supported, output directly
		else 
		{
			$response->body = (string) $this->data;
		}
		
		return $response;
	}
	
	/**
	 * Check user login
	 * 
	 * @static
	 * @access  public
	 * @param   string  $username
	 * @param   mixed   $password
	 * @return  bool 
	 */
	protected static function check_login($username = '', $password = null)
	{
		if (empty($username))
		{
			return false;
		}

		$valid_logins = \Config::get('rest.valid_logins');

		if ( ! array_key_exists($username, $valid_logins))
		{
			return false;
		}

		// If actually null (not empty string) then do not check it
		if ($password !== null and $valid_logins[$username] != $password)
		{
			return false;
		}

		return true;
	}
	
	/**
	 * Prepare authentication to use Basic auth
	 * 
	 * @static
	 * @access  protected
	 */
	protected static function prepare_basic_auth()
	{
		$username = null;
		$password = null;

		// mod_php
		if (Input::server('PHP_AUTH_USER'))
		{
			$username = Input::server('PHP_AUTH_USER');
			$password = Input::server('PHP_AUTH_PW');
		}

		// most other servers
		elseif (Input::server('HTTP_AUTHENTICATION'))
		{
			if (strpos(strtolower(Input::server('HTTP_AUTHENTICATION')), 'basic') === 0)
			{
				list($username, $password) = explode(':', base64_decode(substr(Input::server('HTTP_AUTHORIZATION'), 6)));
			}
		}

		if ( ! static::check_login($username, $password))
		{
			static::force_login();
		}
	}

	/**
	 * Prepare authentication to use Digest auth
	 * 
	 * @static
	 * @access  protected
	 */
	protected static function prepare_digest_auth()
	{
		$uniqid = uniqid(""); // Empty argument for backward compatibility
		// We need to test which server authentication variable to use
		// because the PHP ISAPI module in IIS acts different from CGI
		if (Input::server('PHP_AUTH_DIGEST'))
		{
			$digest_string = Input::server('PHP_AUTH_DIGEST');
		}
		elseif (Input::server('HTTP_AUTHORIZATION'))
		{
			$digest_string = Input::server('HTTP_AUTHORIZATION');
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
			static::force_login($uniqid);
		}

		// We need to retrieve authentication informations from the $auth_data variable
		preg_match_all('@(username|nonce|uri|nc|cnonce|qop|response)=[\'"]?([^\'",]+)@', $digest_string, $matches);
		$digest = array_combine($matches[1], $matches[2]);

		if ( ! array_key_exists('username', $digest) or ! static::check_login($digest['username']))
		{
			static::force_login($uniqid);
		}

		$valid_logins   = \Config::get('rest.valid_logins');
		$valid_pass     = $valid_logins[$digest['username']];

		// This is the valid response expected
		$A1             = md5($digest['username'].':'.\Config::get('rest.realm').':'.$valid_pass);
		$A2             = md5(strtoupper(Input::method()).':'.$digest['uri']);
		$valid_response = md5($A1.':'.$digest['nonce'].':'.$digest['nc'].':'.$digest['cnonce'].':'.$digest['qop'].':'.$A2);

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
	 * @static
	 * @access  protected
	 * @return  string
	 */
	protected static function detect_format()
	{
		// A format has been passed as an argument in the URL and it is supported
		$format = Input::param('format');

		if ($format and static::$supported_formats[$format])
		{
			return $format;
		}

		// Otherwise, check the HTTP_ACCEPT (if it exists and we are allowed)
		$http_accept = Input::server('HTTP_ACCEPT');
		if (\Config::get('rest.ignore_http_accept') === true and $http_accept)
		{

			// Check all formats against the HTTP_ACCEPT header
			foreach (array_keys(static::$supported_formats) as $format)
			{
				// Has this format been requested?
				if (strpos($http_accept, $format) !== false)
				{
					// If not HTML or XML assume its right and send it on its way
					if ($format != 'html' and $format != 'xml')
					{
						return $format;
					}

					// HTML or XML have shown up as a match
					else
					{
						// If it is truely HTML, it wont want any XML
						if ($format == 'html' and strpos($http_accept, 'xml') === false)
						{
							return $format;
						}

						// If it is truely XML, it wont want any HTML
						elseif ($format == 'xml' and strpos($http_accept, 'html') === false)
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
	 * @static
	 * @access  protected
	 * @return  string
	 */
	protected static function detect_lang()
	{
		if ( ! $lang = Input::server('HTTP_ACCEPT_LANGUAGE'))
		{
			return null;
		}

		// They might have sent a few, make it an array
		if (strpos($lang, ',') !== false)
		{
			$langs        = explode(',', $lang);
			
			$return_langs = array();

			foreach ($langs as $lang)
			{
				// Remove weight and strip space
				list($lang)     = explode(';', $lang);
				$return_langs[] = trim($lang);
			}

			return $return_langs;
		}

		// Nope, just return the string
		return $lang;
	}
	
	/**
	 * Force user login
	 * 
	 * @static
	 * @access  protected
	 * @param   string  $nonce 
	 */
	protected static function force_login($nonce = '')
	{
		header('HTTP/1.0 401 Unauthorized');
		header('HTTP/1.1 401 Unauthorized');

		$realm = \Config::get('rest.realm');

		switch (\Config::get('rest.auth'))
		{
			case 'basic' :
				header('WWW-Authenticate: Basic realm="'.$realm.'"');
			break;
			
			case 'digest' :
				header('WWW-Authenticate: Digest realm="'.$realm.'" qop="auth" nonce="'.$nonce.'" opaque="'.md5($realm).'"');
			break;
		}

		exit('Not authorized.');
	}
	
}