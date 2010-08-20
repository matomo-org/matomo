<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id:$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Parent class for forms to be included in Smarty
 * 
 * For an example, @see Piwik_Login_FormLogin
 * 
 * @package Piwik
 * @see HTML_QuickForm2, libs/HTML/QuickForm2.php
 * @link http://pear.php.net/package/HTML_QuickForm2/
 */
abstract class Piwik_QuickForm2 extends HTML_QuickForm2
{
	protected $a_formElements = array();
	
	function __construct( $id, $method = 'post', $attributes = null, $trackSubmit = false)
	{
		if(!isset($attributes['action']))
		{
			$attributes['action'] = Piwik_Url::getCurrentQueryString();
		}
		if(!isset($attributes['name']))
		{
			$attributes['name'] = $id;
		}
		parent::__construct($id,  $method, $attributes, $trackSubmit);

		$this->init();
	}

	/**
	 * Class specific initialization
	 */
	abstract function init();

	/**
	 * The elements in this form
	 *
	 * @return array Element names
	 */
	public function getElementList()
	{
		return $this->a_formElements;
	}

	/**
	 * Wrapper around HTML_QuickForm2_Container's addElement()
	 *
	 * @param    string|HTML_QuickForm2_Node  Either type name (treated
	 *               case-insensitively) or an element instance
	 * @param    mixed   Element name
	 * @param    mixed   Element attributes
	 * @param    array   Element-specific data
	 * @return   HTML_QuickForm2_Node     Added element
	 * @throws   HTML_QuickForm2_InvalidArgumentException
	 * @throws   HTML_QuickForm2_NotFoundException
	 */
    public function addElement($elementOrType, $name = null, $attributes = null,
                               array $data = array())
	{
		if($name != 'submit')
		{
			$this->a_formElements[] = $name;
		}

		return parent::addElement($elementOrType, $name, $attributes, $data);
	}
	
	function setChecked( $nameElement )
	{
		foreach( $this->_elements as $key => $value)
		{
			if($value->_attributes['name'] == $nameElement)
			{
				$this->_elements[$key]->_attributes['checked'] = 'checked';
			}
		}
	}
	function setSelected( $nameElement, $value )
	{
		foreach( $this->_elements as $key => $value)
		{
			if($value->_attributes['name'] == $nameElement)
			{
				$this->_elements[$key]->_attributes['selected'] = 'selected';
			}
		}
	}

	/**
	 * Ported from HTML_QuickForm to minimize changes to Controllers
	 *
	 * @param string $elementName
	 * @return mixed
	 */
	function getSubmitValue($elementName)
	{
		$value = $this->getValue();
		return isset($value[$elementName]) ? $value[$elementName] : null;
	}
}
