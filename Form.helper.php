<?php
/**
 * This file is part of the CarteBlanche PHP framework
 * (c) Pierre Cassat and contributors
 * 
 * Sources <http://github.com/php-carteblanche/form-tool>
 *
 * License Apache-2.0
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('__xss_clean')) 
{
	function __xss_clean( $str=null )
	{
		if (empty($str)) return;
		$str = str_replace(array('<?', '?'.'>'),  array('&lt;?', '?&gt;'), $str);
		return $str;
	}
}

if (!function_exists('required')) 
{
	function required($str=null) 
	{
		return !empty($str);
	}
}

/**
 * Validate a datetime string
 * @param string $datetime The datetime string to validate
 * @return boolean true if this is a datetime
 */
if (!function_exists('is_datetime')) 
{
	function is_datetime($datetime=null) 
	{
		if (is_null($datetime) || !$datetime || !is_string($datetime)) return;
		
		list($date, $time) = explode(' ', $datetime);
		if ($date && strlen($date)>0 && $time && strlen($time)>0)
		{
			if (false===is_date($date))
				return false;
			if (false===is_time($time))
				return false;
			return true;
		}
		return false;
	}
}

/**
 * Validate a date string
 * @param string $date The date string to validate
 * @return boolean true if this is a date
 */
if (!function_exists('is_date')) 
{
	function is_date($date=null) 
	{
		if (is_null($date) || !$date || !is_string($date)) return;
		// dd/mm/yyyy mm/dd/yyyy
		if (0!=preg_match('/^[0-3][0-9]\/[0-3][0-9]\/(?:[0-9][0-9])?[0-9][0-9]$/',$date))
			return true;
		// yyyy/dd/mm
		if (0!=preg_match('/^(?:[0-9][0-9])?[0-9][0-9]\/[0-3][0-9]\/[0-3][0-9]$/',$date))
			return true;
		// yyyy-dd-mm yyyy-mm-dd
		if (0!=preg_match('/^(?:[0-9][0-9])?[0-9][0-9]-[0-3][0-9]-[0-3][0-9]$/',$date))
			return true;
		return false;
	}
}

/**
 * Validate a time string
 * @param string $time The time string to validate
 * @return boolean true if this is a time
 */
if (!function_exists('is_time')) 
{
	function is_time($time=null) 
	{
		if (is_null($time) || !$time || !is_string($time)) return;
		// hh:ii:ss
		if (0!=preg_match('/^[0-9][0-9]:[0-9][0-9]:[0-9][0-9]$/',$time))
			return true;
		return false;
	}
}

/**
 * Validate an email adress
 * @param string $email The string to validate
 * @return boolean true if this is an email
 */
if (!function_exists('is_email')) 
{
	function is_email($email=null) 
	{
		if (is_null($email) || !$email || !is_string($email)) return;
		return preg_match('/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/',$email);
	}
}

/**
 * Validate an URL
 * @param string $url The string to validate
 * @param bollean/string $localhost Is it locally (useful for validating 'http://localhost ...') (false by default) - You can specify a string to check
 * @param array $protocols Table of Internet protocols to verify (by default : 'http', 'https', 'ftp')
 * @return boolean true if this is a URL in one of the specified protocols
 */
if (!function_exists('is_url')) 
{
	function is_url($url=null, $protocols=array('http','https','ftp'), $localhost=false) 
	{ 
		if (is_null($url) || !$url || !is_string($url)) return;
		if ($localhost){
			if (!is_string($localhost)) $localhost = 'localhost';
			if (substr_count($url, $localhost)) return true;
		}
		return preg_match("/^[".join('|', $protocols)."]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i",$url); 
	}
}


/**
 * Validate that a string is lengthed under than a minlength
 * @param string $str The string to validate
 * @param num $maxlength The minlength length for the string (100 by default)
 * @return boolean true if the string passes the test
 */
if (!function_exists('minlength')) 
{
	function minlength($str=null, $minlength=10) 
	{
		if (is_null($str) || !$str || !is_string($str)) return;
		return strlen($str)>=$minlength;
	}
}

/**
 * Validate that a string is lengthed under than a maximum
 * @param string $str The string to validate
 * @param num $maxlength The maximum length for the string (100 by default)
 * @return boolean true if the string passes the test
 */
if (!function_exists('maxlength')) 
{
	function maxlength($str=null, $maxlength=100) 
	{
		if (is_null($str) || !$str || !is_string($str)) return;
		return strlen($str)<=$maxlength;
	}
}

/**
 * Validate that a string is numeric
 * @param string $str The string to validate
 * @return boolean true if the string passes the test
 */
if (!function_exists('is_numeric')) 
{
	function is_numeric($num=null) 
	{
		if (is_null($num) || !$num) return;
		return is_numeric($num);
	}
}

/**
 * Validate that a file content is of a specific type
 * @param string $str The string to validate
 * @param string $type The file type to test
 * @return boolean true if the string passes the test
 */
if (!function_exists('acceptedFiles')) 
{
	function acceptedFiles($str=null, $type='text') 
	{
		if (strpos($type, '/'))
			$type = array_shift( explode('/', $type) );
		$_fct = 'is_'.$type;
		if (function_exists($_fct))
		{
			return $_fct( $str );
		}
		return true;
	}
}

/**
 * Validate that a file content (uploaded) is an image
 * @param string $str The string to validate
 * @return boolean true if the string passes the test
 */
if (!function_exists('is_image')) 
{
	function is_image($str=null) 
	{
		$_f = \CarteBlanche\Library\File::createFromContent( $str );
		return !empty($_f) ? $_f->isImage() : false;
	}
}

// Endfile