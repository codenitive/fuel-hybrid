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
 * @category    Currency
 * @author      Ignacio Muñoz Fernandez <nmunozfernandez@gmail.com>
 */

class Currency
{
	/**
	 * @static
	 * @access  protected
	 * @var     string  Default currency
	 */
	protected static $default = 'EUR';
	
	/**
	 * @static
	 * @access  protected
	 * @var     array   List of available currencies
	 */
	protected static $currencies = array(
		'EUR' => 'Euro', 
		'USD' => 'United States Dollars', 
		'GBP' => 'United Kingdom Pounds', 
		'CAD' => 'Canada Dollars', 
		'AUD' => 'Australia Dollars', 
		'JPY' => 'Japan Yen', 
		'INR' => 'India Rupees', 
		'NZD' => 'New Zealand Dollars', 
		'CHF' => 'Switzerland Francs', 
		'ZAR' => 'South Africa Rand', 
		'DZD' => 'Algeria Dinars', 
		'USD' => 'America (United States) Dollars', 
		'ARS' => 'Argentina Pesos', 
		'AUD' => 'Australia Dollars', 
		'BHD' => 'Bahrain Dinars', 
		'BRL' => 'Brazil Reais', 
		'BGN' => 'Bulgaria Leva', 
		'CAD' => 'Canada Dollars', 
		'CLP' => 'Chile Pesos', 
		'CNY' => 'China Yuan Renminbi', 
		'CNY' => 'RMB (China Yuan Renminbi)', 
		'COP' => 'Colombia Pesos', 
		'CRC' => 'Costa Rica Colones', 
		'HRK' => 'Croatia Kuna', 
		'CZK' => 'Czech Republic Koruny', 
		'DKK' => 'Denmark Kroner', 
		'DOP' => 'Dominican Republic Pesos', 
		'EGP' => 'Egypt Pounds', 
		'EEK' => 'Estonia Krooni', 
		'EUR' => 'Euro', 
		'FJD' => 'Fiji Dollars', 
		'HKD' => 'Hong Kong Dollars', 
		'HUF' => 'Hungary Forint', 
		'ISK' => 'Iceland Kronur', 
		'INR' => 'India Rupees', 
		'IDR' => 'Indonesia Rupiahs', 
		'ILS' => 'Israel New Shekels', 
		'JMD' => 'Jamaica Dollars', 
		'JPY' => 'Japan Yen', 
		'JOD' => 'Jordan Dinars', 
		'KES' => 'Kenya Shillings', 
		'KRW' => 'Korea (South) Won', 
		'KWD' => 'Kuwait Dinars', 
		'LBP' => 'Lebanon Pounds', 
		'MYR' => 'Malaysia Ringgits', 
		'MUR' => 'Mauritius Rupees', 
		'MXN' => 'Mexico Pesos', 
		'MAD' => 'Morocco Dirhams', 
		'NZD' => 'New Zealand Dollars', 
		'NOK' => 'Norway Kroner', 
		'OMR' => 'Oman Rials', 
		'PKR' => 'Pakistan Rupees', 
		'PEN' => 'Peru Nuevos Soles', 
		'PHP' => 'Philippines Pesos', 
		'PLN' => 'Poland Zlotych', 
		'QAR' => 'Qatar Riyals', 
		'RON' => 'Romania New Lei', 
		'RUB' => 'Russia Rubles', 
		'SAR' => 'Saudi Arabia Riyals', 
		'SGD' => 'Singapore Dollars', 
		'SKK' => 'Slovakia Koruny', 
		'ZAR' => 'South Africa Rand', 
		'KRW' => 'South Korea Won', 
		'LKR' => 'Sri Lanka Rupees', 
		'SEK' => 'Sweden Kronor', 
		'CHF' => 'Switzerland Francs', 
		'TWD' => 'Taiwan New Dollars', 
		'THB' => 'Thailand Baht', 
		'TTD' => 'Trinidad and Tobago Dollars', 
		'TND' => 'Tunisia Dinars', 
		'TRY' => 'Turkey Lira', 
		'AED' => 'United Arab Emirates Dirhams', 
		'GBP' => 'United Kingdom Pounds', 
		'USD' => 'United States Dollars', 
		'VEB' => 'Venezuela Bolivares', 
		'VND' => 'Vietnam Dong', 
		'ZMK' => 'Zambia Kwacha', 
	);
	
	/**
	 * @static
	 * @access  protected
	 * @var     string  Use google API service
	 */
	protected static $service = "http://www.google.com/ig/calculator?hl=en&q={AMOUNT}{FROM}=?{TO}";

	/**
	 * Only load the configuration once
	 *
	 * @static
	 * @access  public
	 */
	public static function _init()
	{
		\Config::load('hybrid', 'hybrid');
		static::$default = \Config::get('hybrid.currency.default', static::$default);
	}
	
	/**
	 * Initiate a new Currency class
	 * 
	 * @static
	 * @access  public
	 * @param   float   $amount     amount to convert
	 * @param   string  $from       Currency to convert from
	 * @param   int     $round      automatic round the currency, defaults to 2 digits
	 * @return  object  Currency
	 */
	public static function forge($amount, $from = null, $round = 2)
	{
		return new static($amount, $from, $round);
	}

	/**
	 * Initiate a new Currency class
	 * 
	 * @static
	 * @access  public
	 * @param   float   $amount     amount to convert
	 * @param   string  $from       Currency to convert from
	 * @param   int     $round      automatic round the currency, defaults to 2 digits
	 * @return  object  Currency
	 */
	public static function make($amount, $from = null, $round = 2)
	{
		return static::forge($amount, $from, $round);
	}
	
	/**
	 * Shortcode to self::forge().
	 *
	 * @deprecated  1.3.0
	 * @static
	 * @access  public
	 * @param   float   $amount     amount to convert
	 * @param   string  $from       Currency to convert from
	 * @param   int     $round      automatic round the currency, defaults to 2 digits
	 * @return  self::forge()
	 */
	public static function factory($amount, $from = null, $round = 2)
	{
		\Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
		
		return static::forge($amount, $from, $round);
	}

	/**
	 * @access  protected
	 * @var     array   Currency rates
	 */
	protected $currency_rates = array();
	
	/**
	 * @access  protected
	 * @var     int     Precision rounding
	 */
	protected $round;
	
	/**
	 * @access  protected
	 * @var     string  Currency of self::$amount
	 */
	protected $from;
	
	/**
	 * @access  protected
	 * @var     Given amount
	 */
	protected $amount = 0.00;
	
	/**
	 * Construct a new instance
	 *
	 * @access  protected
	 * @param   float   $amount     amount to convert
	 * @param   string  $from       Currency to convert from
	 * @param   int     $round      automatic round the currency, defaults to 2 digits
	 * @return  object  Currency
	 */
	public function __construct($amount, $from = null, $round = 2)
	{
		// in situation where from is null, set from to default
		if ($from === null or ! $from)
		{
			$from = static::$default;
		}
		
		$this->from   = strtoupper($from);
		$this->round  = $round;
		$this->amount = (float) $amount;

		return $this;
	}

	/**
	 * Loads all currency rate data from service provider
	 * 
	 * @access  protected
	 * @param   string  $from_currency  A string name of currency available in static::$currencies
	 * @return  void
	 */
	protected function fetch_currency_rate($from_currency)
	{
		\Cache::forge('hybrid.currency.'.$from_currency, \Config::get('hybrid.currency.cache', array()));

		if ( ! array_key_exists($from_currency, static::$currencies))
		{
			throw new \FuelException(__METHOD__.": Unable to use unknown currency {$from_currency}");
		}

		try
		{
			$this->currency_rates = \Cache::get('hybrid.currency.'.$from_currency);
		}
		catch (\CacheNotFoundException $e)
		{   
			$search = array('{AMOUNT}', '{FROM}', '{TO}');
			
			// load data for each currency, this might take awhile
			foreach (static::$currencies as $cur => $name)
			{
				$replace = array('1', $from_currency, $cur);
				$url     = str_replace($search, $replace, static::$service);
				
				try
				{
					$data = Curl::get($url)
						->setopt(array(
							CURLOPT_BINARYTRANSFER => 1,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_MAXREDIRS      => 5,
							CURLOPT_HEADER         => 0,
							CURLOPT_USERAGENT      => "Fuel PHP framework - \Hybrid\Currency class",
						))
						->execute();

					$body = $data->body;
				}
				catch (\FuelException $e)
				{
					$body = file_get_contents($url);     
				}
				
				// this is rather hackish, the return body from either Curl or file_get_contents can't be use directly with json_decode
				foreach (array("lhs", "rhs", "error", "icc") as $key)
				{
					$body = str_replace($key.":", '"'.$key.'":', $body);
				}
				
				// need to decode this first
				$data = json_decode($body);
				
				if (null !== $data and false !== $data->icc)
				{
					$conversion = \Format::forge($body, 'json')->to_array();
					$tmp        = explode(' ', $conversion['rhs']);
					$rate       = array_shift($tmp);

					$this->currency_rates[$cur] = (float) $rate;
				}
			}

			\Cache::set('hybrid.currency.'.$from_currency, $this->currency_rates);
		}
	}

	/**
	 * Convert to a currency
	 *
	 * @access  public
	 * @param   str     $currency   A string name of currency available in static::$currencies
	 * @return  float
	 * @throws  \FuelException
	 */
	public function convert_to($to_currency)
	{
		$from_currency = $this->from;

		if ( ! array_key_exists($to_currency, static::$currencies))
		{
			throw new \FuelException(__METHOD__.": Currency {$to_currency} does not exists.");
		}

		// This is no brainer, does not need to convert if from and to currency is the same.
		if ($from_currency === $to_currency)
		{
			return (float) $this->amount;
		}

		// fetch currency rate from provider, or load from cache if it's available
		$this->fetch_currency_rate($this->from);

		// we fetch the latest currency but if for instance there no conversion rate available between the two, throw an exception
		if ( ! array_key_exists($to_currency, $this->currency_rates))
		{
			throw new \FuelException(__METHOD__.": Currency {$to_currency} is not available to convert from {$from_currency}");
		}

		return (float) round($this->amount * $this->currency_rates[$to_currency], $this->round);
	}
	
	/**
	 * Capture magic method, at the moment expect on to_{currency}()
	 *
	 * @access  public
	 * @return  float
	 * @see     self::convert_to()
	 * @throws  \FuelException
	 */
	public function __call($method, $args)
	{
		if ( ! strpos(strtolower($method), 'to_') === 0)
		{
			throw new \FuelException(__CLASS__.'::'.$method.' does not exist, use ::to_{currency}().');
		}
		
		$currency = strtoupper(str_replace('to_', '', $method));
		return $this->convert_to($currency);
	}

}