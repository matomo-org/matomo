<?php
/**
* @package tests
*/
/**
* @package tests
*/
class iiparserBase
{
	/**
	* always base
	* @var string
	*/
	var $type = 'base';
	/**
	* set to different things by its descendants
	* @abstract
	* @var mixed
	*/
	var $value = false;
	
	/**
	* @return string returns value of {@link $type)
	*/
	function getType()
	{
		return $this->type;
	}
	
	/**
	* @param mixed set the value of this element
	*/
	function setValue($value)
	{
		$this->value = $value;
	}
	
	/**
	* @return mixed get the value of this element (element-dependent)
	*/
	function getValue()
	{
		return $this->value;
	}
}
?>