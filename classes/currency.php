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
     * @var     Default currency
     */
    protected static $default = 'EUR';
    
    /**
     * @static
     * @access  protected
     * @var     List of available currencies
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
     * @var     Use google API service
     */
    protected static $service = "http://www.google.com/ig/calculator?hl=en&q={AMOUNT}{FROM}=?{TO}";
    
    /**
     * Initiate a new Currency class
     * 
     * @static
     * @access  public
     * @param   float   $amount amount to convert
     * @param   string  $from Currency to convert from
     * @param   int     $round automatic round the currency, defaults to 2 digits
     * @return  Currency
     */
    public static function forge($amount, $from = null, $round = 2)
    {
        return new static($amount, $from, $round);
    }
    
    /**
     * Shortcode to self::forge().
     *
     * @deprecated  1.3.0
     * @static
     * @access  public
     * @param   float   $amount amount to convert
     * @param   string  $from Currency to convert from
     * @param   int     $round automatic round the currency, defaults to 2 digits
     * @return  self::forge()
     */
    public static function factory($amount, $from = null, $round = 2)
    {
        \Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
        
        return static::forge($amount, $from, $round);
    }

    /**
     * @access  protected
     * @var     Currency rates
     */
    protected $currency_rates = array();
    
    protected $round;
    
    protected $from;
    
    protected $amount = 0.00;
    
    public function __construct($amount, $from = null, $round = 2)
    {
        if ($from === null or ! $from)
        {
            $from = strtoupper(static::$default);
        }
        
        $this->from   = strtoupper($from);
        $this->round  = $round;
        $this->amount = (float)$amount;

        return $this;
    }

    /**
     * Loads all currency rate data from service provider
     * 
     * @access protected
     * @param   string  $from_currency A string name of currency available in static::$currencies
     * @return void
     */
    protected function fetch_currency_rate($from_currency)
    {
        if ( ! array_key_exists($from_currency, static::$currencies))
        {
            throw new \FuelException("\Hybrid\Currency: Unable to use unknown currency {$from_currency}");
        }

        try
        {
            $this->currency_rates = \Cache::get('hybrid.currency.'.$from_currency);
            throw new \CacheNotFoundException();
        }
        catch(\CacheNotFoundException $e)
        {   
            $search = array('{AMOUNT}', '{FROM}', '{TO}');
            
            // load data for each currency, this might take awhile
            foreach (static::$currencies as $cur => $name)
            {
                $replace = array('1', $from_currency, $cur);
                
                if (function_exists('curl_init'))
                {
                    $data = Curl::get(str_replace($search, $replace, static::$service))
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
                else 
                {
                    $body = file_get_contents(str_replace($search, $replace, static::$service));     
                }
                
                // this is rather hackish, the return body from either Curl or file_get_contents can't be use directly with json_decode
                foreach (array("lhs", "rhs", "error", "icc") as $key)
                {
                    $body = str_replace($key.":", '"'.$key.'":', $body);
                }
                
                // need to decode this first
                $data = json_decode($body);
                
                if ( ! is_null($data) and $data->icc !== false)
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
     * @param   str     $currency A string name of currency available in static::$currencies
     * @return  float
     * @throws  \FuelException
     */
    public function convert_to($currency)
    {
        if ( ! array_key_exists($currency, static::$currencies))
        {
            throw new \FuelException(__CLASS__." Currency {$currency} dont exists.");
        }

        $this->fetch_currency_rate($this->from);

        if ( ! array_key_exists($currency, $this->currency_rate))
        {
            throw new \FuelException(__CLASS__." Currency {$currency} is not available to convert from ".$this->from);
        }

        // doesn't need to convert if from and to currency is the same.
        if ($this->from === $currency)
        {
            return (float) $this->amount;
        }

        return (float) round($this->amount * $this->currency_rates[$currency], $this->round);
    }
    
    public function __call($method, $args)
    {
        if ( ! strpos(strtolower($method), 'to_') === 0)
        {
            throw new \FuelException(__CLASS__.'::'.$method.' not exists, use ::to_{currency}().');
        }
        
        $currency = strtoupper(str_replace('to_', '', $method));
        return $this->convert_to($currency);
    }

}