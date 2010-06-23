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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: GreaterThan.php 20358 2010-01-17 19:03:49Z thomas $
 */

/**
 * @see Zend_Validate_Abstract
 */
// require_once 'Zend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_GreaterThan extends Zend_Validate_Abstract
{

    const NOT_GREATER = 'notGreaterThan';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_GREATER => "'%value%' is not greater than '%min%'",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'min' => '_min'
    );

    /**
     * Minimum value
     *
     * @var mixed
     */
    protected $_min;

    /**
     * Sets validator options
     *
     * @param  mixed|Zend_Config $min
     * @return void
     */
    public function __construct($min)
    {
        if ($min instanceof Zend_Config) {
            $min = $min->toArray();
        }

        if (is_array($min)) {
            if (array_key_exists('min', $min)) {
                $min = $min['min'];
            } else {
                // require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("Missing option 'min'");
            }
        }

        $this->setMin($min);
    }

    /**
     * Returns the min option
     *
     * @return mixed
     */
    public function getMin()
    {
        return $this->_min;
    }

    /**
     * Sets the min option
     *
     * @param  mixed $min
     * @return Zend_Validate_GreaterThan Provides a fluent interface
     */
    public function setMin($min)
    {
        $this->_min = $min;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is greater than min option
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        if ($this->_min >= $value) {
            $this->_error(self::NOT_GREATER);
            return false;
        }
        return true;
    }

}
