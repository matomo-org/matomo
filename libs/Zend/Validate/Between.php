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
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Between.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Validate_Abstract
 */
// require_once 'Zend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Between extends Zend_Validate_Abstract
{
    /**
     * Validation failure message key for when the value is not between the min and max, inclusively
     */
    const NOT_BETWEEN        = 'notBetween';

    /**
     * Validation failure message key for when the value is not strictly between the min and max
     */
    const NOT_BETWEEN_STRICT = 'notBetweenStrict';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_BETWEEN        => "'%value%' is not between '%min%' and '%max%', inclusively",
        self::NOT_BETWEEN_STRICT => "'%value%' is not strictly between '%min%' and '%max%'"
    );

    /**
     * Additional variables available for validation failure messages
     *
     * @var array
     */
    protected $_messageVariables = array(
        'min' => '_min',
        'max' => '_max'
    );

    /**
     * Minimum value
     *
     * @var mixed
     */
    protected $_min;

    /**
     * Maximum value
     *
     * @var mixed
     */
    protected $_max;

    /**
     * Whether to do inclusive comparisons, allowing equivalence to min and/or max
     *
     * If false, then strict comparisons are done, and the value may equal neither
     * the min nor max options
     *
     * @var boolean
     */
    protected $_inclusive;

    /**
     * Sets validator options
     * Accepts the following option keys:
     *   'min' => scalar, minimum border
     *   'max' => scalar, maximum border
     *   'inclusive' => boolean, inclusive border values
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp['min'] = array_shift($options);
            if (!empty($options)) {
                $temp['max'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['inclusive'] = array_shift($options);
            }

            $options = $temp;
        }

        if (!array_key_exists('min', $options) || !array_key_exists('max', $options)) {
            // require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Missing option. 'min' and 'max' has to be given");
        }

        if (!array_key_exists('inclusive', $options)) {
            $options['inclusive'] = true;
        }

        $this->setMin($options['min'])
             ->setMax($options['max'])
             ->setInclusive($options['inclusive']);
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
     * @return Zend_Validate_Between Provides a fluent interface
     */
    public function setMin($min)
    {
        $this->_min = $min;
        return $this;
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
     * @return Zend_Validate_Between Provides a fluent interface
     */
    public function setMax($max)
    {
        $this->_max = $max;
        return $this;
    }

    /**
     * Returns the inclusive option
     *
     * @return boolean
     */
    public function getInclusive()
    {
        return $this->_inclusive;
    }

    /**
     * Sets the inclusive option
     *
     * @param  boolean $inclusive
     * @return Zend_Validate_Between Provides a fluent interface
     */
    public function setInclusive($inclusive)
    {
        $this->_inclusive = $inclusive;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is between min and max options, inclusively
     * if inclusive option is true.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        if ($this->_inclusive) {
            if ($this->_min > $value || $value > $this->_max) {
                $this->_error(self::NOT_BETWEEN);
                return false;
            }
        } else {
            if ($this->_min >= $value || $value >= $this->_max) {
                $this->_error(self::NOT_BETWEEN_STRICT);
                return false;
            }
        }
        return true;
    }

}
