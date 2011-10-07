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
 * @category    Config
 * @author      Ignacio "kavinsky" Muñoz Fernandez <nmunozfernandez@gmail.com>
 */
class Config_Ini extends Config_Driver
{
	public function load($file)
	{
		return parse_ini_file($file, true, INI_SCANNER_NORMAL);
	}
	
	/**
	 * 
	 */
	public function save($file, $config)
	{
		$ini_header = <<<INI
;
; Fuel is a fast, lightweight, community driven PHP5 framework.
;
; @package    Fuel
; @version    1.0
; @author     Fuel Development Team
; @license    MIT License
; @copyright  2010 - 2011 Fuel Development Team
; @link       http://fuelphp.com
;

INI;
		
	}
	
	/**
	 * normalize a Value by determining the Type
	 *
	 * @param string $value value
	 *
	 * @return string
	 */
    protected function normalizeValue($value)
    {
        if (is_bool($value)) {
            $value = $this->toBool($value);
            return $value;
        } elseif (is_numeric($value)) {
            return $value;
        }
        if (true) {
            $value = '"' . $value . '"';
        }
        return $value;
    }
	
	/**
	 * converts string to a representable Config Bool Format
	 *
	 * @param string $value value
	 *
	 * @return string
	 * @throws Config_Lite_Exception_UnexpectedValue when format is unknown
	 */
    public function toBool($value)
    {
        if ($value === true) {
            return 'true';
        }
        return 'false';
    }
	protected function buildOutputString($sectionsarray)
    {
        $content = '';
        $sections = '';
        $globals = '';
        if (!empty($sectionsarray)) {
            // 2 loops to write `globals' on top, alternative: buffer
            foreach ($sectionsarray as $section => $item) {
                if (!is_array($item)) {
                    $value = $this->normalizeValue($item);
                    $globals .= $section . ' = ' . $value . "\n";
                }
            }
            $content .= $globals;
            foreach ($sectionsarray as $section => $item) {
                if (is_array($item)) {
                    $sections .= "\n[" . $section . "]\n";
                    foreach ($item as $key => $value) {
                        if (is_array($value)) {
                            foreach ($value as $arrkey => $arrvalue) {
                                $arrvalue = $this->normalizeValue($arrvalue);
                                $arrkey = $key . '[' . $arrkey . ']';
                                $sections .= $arrkey . ' = ' . $arrvalue
                                            . "\n";
                            }
                        } else {
                            $value = $this->normalizeValue($value);
                            $sections .= $key . ' = ' . $value . "\n";
                        }
                    }
                }
            }
            $content .= $sections;
        }
        return $content;
    }
}
