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
 * @version    $Id: InArray.php 20358 2010-01-17 19:03:49Z thomas $
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
class Zend_Validate_InArray extends Zend_Validate_Abstract
{
    const NOT_IN_ARRAY = 'notInArray';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_IN_ARRAY => "'%value%' was not found in the haystack",
    );

    /**
     * Haystack of possible values
     *
     * @var array
     */
    protected $_haystack;

    /**
     * Whether a strict in_array() invocation is used
     *
     * @var boolean
     */
    protected $_strict = false;

    /**
     * Whether a recursive search should be done
     *
     * @var boolean
     */
    protected $_recursive = false;

    /**
     * Sets validator options
     *
     * @param  array|Zend_Config $haystack
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            // require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Array expected as parameter');
        } else {
            $count = func_num_args();
            $temp  = array();
            if ($count > 1) {
                $temp['haystack'] = func_get_arg(0);
                $temp['strict']   = func_get_arg(1);
                $options = $temp;
            } else {
                $temp = func_get_arg(0);
                if (!array_key_exists('haystack', $options)) {
                    $options = array();
                    $options['haystack'] = $temp;
                } else {
                    $options = $temp;
                }
            }
        }

        $this->setHaystack($options['haystack']);
        if (array_key_exists('strict', $options)) {
            $this->setStrict($options['strict']);
        }

        if (array_key_exists('recursive', $options)) {
            $this->setRecursive($options['recursive']);
        }
    }

    /**
     * Returns the haystack option
     *
     * @return mixed
     */
    public function getHaystack()
    {
        return $this->_haystack;
    }

    /**
     * Sets the haystack option
     *
     * @param  mixed $haystack
     * @return Zend_Validate_InArray Provides a fluent interface
     */
    public function setHaystack(array $haystack)
    {
        $this->_haystack = $haystack;
        return $this;
    }

    /**
     * Returns the strict option
     *
     * @return boolean
     */
    public function getStrict()
    {
        return $this->_strict;
    }

    /**
     * Sets the strict option
     *
     * @param  boolean $strict
     * @return Zend_Validate_InArray Provides a fluent interface
     */
    public function setStrict($strict)
    {
        $this->_strict = (boolean) $strict;
        return $this;
    }

    /**
     * Returns the recursive option
     *
     * @return boolean
     */
    public function getRecursive()
    {
        return $this->_recursive;
    }

    /**
     * Sets the recursive option
     *
     * @param  boolean $recursive
     * @return Zend_Validate_InArray Provides a fluent interface
     */
    public function setRecursive($recursive)
    {
        $this->_recursive = (boolean) $recursive;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is contained in the haystack option. If the strict
     * option is true, then the type of $value is also checked.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue($value);
        if ($this->getRecursive()) {
            $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->_haystack));
            foreach($iterator as $element) {
                if ($this->_strict) {
                    if ($element === $value) {
                        return true;
                    }
                } else if ($element == $value) {
                    return true;
                }
            }
        } else {
            if (in_array($value, $this->_haystack, $this->_strict)) {
                return true;
            }
        }

        $this->_error(self::NOT_IN_ARRAY);
        return false;
    }
}
