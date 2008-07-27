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
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: LessThan.php 8064 2008-02-16 10:58:39Z thomas $
 */


/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_LessThan extends Zend_Validate_Abstract
{

    const NOT_LESS = 'notLessThan';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_LESS => "'%value%' is not less than '%max%'"
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'max' => '_max'
    );

    /**
     * Maximum value
     *
     * @var mixed
     */
    protected $_max;

    /**
     * Sets validator options
     *
     * @param  mixed $max
     * @return void
     */
    public function __construct($max)
    {
        $this->setMax($max);
    }

    /**
     * Returns the max option
     *
     * @return mixed
     */
    public function getMax()
    {
        return $this->_max;
    }

    /**
     * Sets the max option
     *
     * @param  mixed $max
     * @return Zend_Validate_LessThan Provides a fluent interface
     */
    public function setMax($max)
    {
        $this->_max = $max;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is less than max option
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue($value);
        if ($this->_max <= $value) {
            $this->_error();
            return false;
        }
        return true;
    }

}
