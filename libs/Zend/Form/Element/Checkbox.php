<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Element_Xhtml */
// require_once 'Zend/Form/Element/Xhtml.php';

/**
 * Checkbox form element
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Checkbox.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Zend_Form_Element_Checkbox extends Zend_Form_Element_Xhtml
{
    /**
     * Is the checkbox checked?
     * @var bool
     */
    public $checked = false;

    /**
     * Use formCheckbox view helper by default
     * @var string
     */
    public $helper = 'formCheckbox';

    /**
     * Options that will be passed to the view helper
     * @var array
     */
    public $options = array(
        'checkedValue'   => '1',
        'uncheckedValue' => '0',
    );

    /**
     * Value when checked
     * @var string
     */
    protected $_checkedValue = '1';

    /**
     * Value when not checked
     * @var string
     */
    protected $_uncheckedValue = '0';

    /**
     * Current value
     * @var string 0 or 1
     */
    protected $_value = '0';

    /**
     * Set options
     *
     * Intercept checked and unchecked values and set them early; test stored
     * value against checked and unchecked values after configuration.
     *
     * @param  array $options
     * @return Zend_Form_Element_Checkbox
     */
    public function setOptions(array $options)
    {
        if (array_key_exists('checkedValue', $options)) {
            $this->setCheckedValue($options['checkedValue']);
            unset($options['checkedValue']);
        }
        if (array_key_exists('uncheckedValue', $options)) {
            $this->setUncheckedValue($options['uncheckedValue']);
            unset($options['uncheckedValue']);
        }
        parent::setOptions($options);

        $curValue = $this->getValue();
        $test     = array($this->getCheckedValue(), $this->getUncheckedValue());
        if (!in_array($curValue, $test)) {
            $this->setValue($curValue);
        }

        return $this;
    }

    /**
     * Set value
     *
     * If value matches checked value, sets to that value, and sets the checked
     * flag to true.
     *
     * Any other value causes the unchecked value to be set as the current
     * value, and the checked flag to be set as false.
     *
     *
     * @param  mixed $value
     * @return Zend_Form_Element_Checkbox
     */
    public function setValue($value)
    {
        if ($value == $this->getCheckedValue()) {
            parent::setValue($value);
            $this->checked = true;
        } else {
            parent::setValue($this->getUncheckedValue());
            $this->checked = false;
        }
        return $this;
    }

    /**
     * Set checked value
     *
     * @param  string $value
     * @return Zend_Form_Element_Checkbox
     */
    public function setCheckedValue($value)
    {
        $this->_checkedValue = (string) $value;
        $this->options['checkedValue'] = $value;
        return $this;
    }

    /**
     * Get value when checked
     *
     * @return string
     */
    public function getCheckedValue()
    {
        return $this->_checkedValue;
    }

    /**
     * Set unchecked value
     *
     * @param  string $value
     * @return Zend_Form_Element_Checkbox
     */
    public function setUncheckedValue($value)
    {
        $this->_uncheckedValue = (string) $value;
        $this->options['uncheckedValue'] = $value;
        return $this;
    }

    /**
     * Get value when not checked
     *
     * @return string
     */
    public function getUncheckedValue()
    {
        return $this->_uncheckedValue;
    }

    /**
     * Set checked flag
     *
     * @param  bool $flag
     * @return Zend_Form_Element_Checkbox
     */
    public function setChecked($flag)
    {
        $this->checked = (bool) $flag;
        if ($this->checked) {
            $this->setValue($this->getCheckedValue());
        } else {
            $this->setValue($this->getUncheckedValue());
        }
        return $this;
    }

    /**
     * Get checked flag
     *
     * @return bool
     */
    public function isChecked()
    {
        return $this->checked;
    }
}
