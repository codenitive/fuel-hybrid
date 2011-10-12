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
    
    protected static $default = 'EUR';
    
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
    
    protected static $service = "http://www.google.com/ig/calculator?hl=en&q={AMOUNT}{FROM}=?{TO}";
    
    public static $currencies_value = array();
    
    /**
     * Autoload for Currency, it loads all currency data from service provider
     * 
     * @static
     * @access public
     * @return void
     */
    public static function _init()
    {
        try
        {
            static::$currencies_value = \Cache::get('currency.'.static::$default);
        }
        catch(\CacheNotFoundException $e)
        {   
            $search = array('{AMOUNT}', '{FROM}', '{TO}');
            
            if (function_exists('curl_init'))
            {
                foreach (static::$currencies as $cur => $name)
                {
                    $replace = array('1', static::$default, $cur);
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_HEADER, 0);
                    curl_setopt($curl, CURLOPT_USERAGENT, 'Fuel PHP framework - Agent class (http://fuelphp.com)');                 
                    curl_setopt($curl, CURLOPT_URL, str_replace($search, $replace, static::$service));
                    $data = curl_exec($curl);
                    curl_close($curl);
                    $data = explode('"', $data);
                    $data = explode(' ', $data[3]);
                    var_dump($data[0]); 
                    $data = $data[0];
                    static::$currencies_value[$cur] = (float) $data;
                }
            }
            else
            {
                foreach (static::$currencies as $cur => $name)
                {
                    $replace = array('1', static::$default, $cur);
                    $data = file_get_contents(str_replace($search, $replace, static::$service));
                    $data = \Format::forge($data, 'json')->to_array();
                    static::$currencies_value[$cur] = (float) $data['rhs'];
                }
            }
            
            \Cache::set('currency.'.static::$default, static::$currencies_value);
        }
    }
    
    /**
     * Initiate a new Currency class
     * 
     * @static
     * @access  public
     * @return  Currency
     * @param float $amount amount to convert
     * @param string $from Currency to convert from
     * @param integer $round automatic round the currency, defaults to 2 digits
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
     * @param float $amount amount to convert
     * @param string $from Currency to convert from
     * @param integer $round automatic round the currency, defaults to 2 digits
     * @return  self::forge()
     */
    public static function factory($amount, $from = null, $round = 2)
    {
        \Log::warning('This method is deprecated. Please use a forge() instead.', __METHOD__);
        
        return static::forge($amount, $from, $round);
    }
    
    protected $round;
    
    protected $from;
    
    protected $amount = 0.00;
    
    public function __construct($amount, $from = null, $round = 2)
    {
        $this->round = $round;
        if ($from === null or ! $from)
        {
            $this->from = strtoupper(static::$default);
        }
        else 
        {
            $this->from = strtoupper($from);    
        }
        
        $this->amount = (float)$amount;
        return $this;
    }
    
    public function __call($method, $args)
    {
        if ( ! strpos(strtolower($method), 'to_') === 0)
        {
            throw new \FuelException(__CLASS__.'::'.$method.' not exists, use ::to_{currency}');
        }
        
        $currency = strtoupper(str_replace('to_', '', $method));        
        if ( ! array_key_exists($currency, static::$currencies))
        {
            throw new \FuelException(__CLASS__." Currency $currency dont exists");
        }
        
        
        if ($this->from === static::$default)
        {
            return (float) round($this->amount * static::$currencies_value[$currency], $this->round);
        }

        $val = $this->amount / static::$currencies_value[$this->from];
        return (float) round($val * static::$currencies_value[$currency], $this->round);
        
        return (float) 0;
    }
}
