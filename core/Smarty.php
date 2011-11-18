<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * @see libs/Smarty/Smarty.class.php
 * @link http://smarty.net
 */
require_once PIWIK_INCLUDE_PATH . '/libs/Smarty/Smarty.class.php';

/**
 * Smarty class
 *
 * @package Piwik
 * @subpackage Piwik_Smarty
 * @see Smarty, libs/Smarty/Smarty.class.php
 * @link http://smarty.net/manual/en/
 */
class Piwik_Smarty extends Smarty 
{
	function trigger_error($error_msg, $error_type = E_USER_WARNING)
	{
		throw new SmartyException($error_msg);
	}

	public function __construct($smConf = array(), $filter = true)
	{
		parent::__construct();

		if(count($smConf) == 0)
		{
			$smConf = Zend_Registry::get('config')->smarty;
		}
		foreach($smConf as $key => $value)
		{
			$this->$key = $value;
		}

		$this->template_dir = $smConf->template_dir->toArray();
		array_walk($this->template_dir, array("Piwik_Smarty","addPiwikPath"), PIWIK_INCLUDE_PATH);

		$this->plugins_dir = $smConf->plugins_dir->toArray();
		array_walk($this->plugins_dir, array("Piwik_Smarty","addPiwikPath"), PIWIK_INCLUDE_PATH);

		$this->compile_dir = $smConf->compile_dir;
		Piwik_Smarty::addPiwikPath($this->compile_dir, null, PIWIK_USER_PATH);

		$this->cache_dir = $smConf->cache_dir;
		Piwik_Smarty::addPiwikPath($this->cache_dir, null, PIWIK_USER_PATH);

		$error_reporting = $smConf->error_reporting;
		if($error_reporting != (string)(int)$error_reporting)
		{
			$error_reporting = self::bitwise_eval($error_reporting);
		}
		$this->error_reporting = $error_reporting;

		if($filter)
		{
			$this->load_filter('output', 'cachebuster');

			$use_ajax_cdn = Zend_Registry::get('config')->General->use_ajax_cdn;
			if($use_ajax_cdn)
			{
				$this->load_filter('output', 'ajaxcdn');
			}

			$this->load_filter('output', 'trimwhitespace');
		}
	}

	/**
	 * A callback for bitwise negation
	 *
	 * @param array $matches
	 * @return string
	 */
	static public function bitwise_negate($matches)
	{
		return (string)((~(int)$matches[1]));
	}

	/**
	 * A callback for bitwise AND
	 *
	 * @param array $matches
	 * @return string
	 */
	static public function bitwise_and($matches)
	{
		return (string)((int)$matches[1]&(int)$matches[2]);
	}

	/**
	 * A callback for bitwise XOR
	 *
	 * @param array $matches
	 * @return string
	 */
	static public function bitwise_xor($matches)
	{
		return (string)((int)$matches[1]^(int)$matches[2]);
	}

	/**
	 * A callback for bitwise OR
	 *
	 * @param array $matches
	 * @return string
	 */
	static public function bitwise_or($matches)
	{
		return (string)((int)$matches[1]|(int)$matches[2]);
	}

	/**
	 * Evaluate expression containing only bitwise operators.
	 * Replaces defined constants with corresponding values.
	 * Does not use eval().
	 *
	 * @param string $expression Expression.
	 * @return string
	 */
	static public function bitwise_eval($expression)
	{
		// replace defined constants
		$buf = get_defined_constants(true);

		// use only the 'Core' PHP constants, e.g., E_ALL, E_STRICT, ...
		$consts = isset($buf['Core']) ? $buf['Core'] : (isset($buf['mhash']) ? $buf['mhash'] : $buf['internal']);
		$expression = str_replace(' ', '', strtr($expression, $consts));

		// bitwise operators in order of precedence (highest to lowest)
		// note: boolean ! (NOT) and parentheses aren't handled
		$expression = preg_replace_callback('/~(-?[0-9]+)/', array('Piwik_Smarty', 'bitwise_negate'), $expression);
		$expression = preg_replace_callback('/(-?[0-9]+)&(-?[0-9]+)/', array('Piwik_Smarty', 'bitwise_and'), $expression);
		$expression = preg_replace_callback('/(-?[0-9]+)\^(-?[0-9]+)/', array('Piwik_Smarty', 'bitwise_xor'), $expression);
		$expression = preg_replace_callback('/(-?[0-9]+)\|(-?[0-9]+)/', array('Piwik_Smarty', 'bitwise_or'), $expression);

		return (string)((int)$expression & PHP_INT_MAX);
	}

	/**
	 * Prepend relative paths with absolute Piwik path
	 *
	 * @param string $value relative path (pass by reference)
	 * @param int $key (don't care)
	 * @param string $path Piwik root
	 */
	static public function addPiwikPath(&$value, $key, $path)
	{
		if($value[0] != '/' && $value[0] != DIRECTORY_SEPARATOR)
		{
			$value = $path ."/$value";
		}
	}
}

/**
 * @package Piwik
 * @subpackage Piwik_Smarty
 */
class SmartyException extends Exception {}
