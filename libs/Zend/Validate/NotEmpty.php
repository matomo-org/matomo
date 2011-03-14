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
 * @version    $Id: NotEmpty.php 23775 2011-03-01 17:25:24Z ralph $
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
class Zend_Validate_NotEmpty extends Zend_Validate_Abstract
{
    const BOOLEAN       = 1;
    const INTEGER       = 2;
    const FLOAT         = 4;
    const STRING        = 8;
    const ZERO          = 16;
    const EMPTY_ARRAY   = 32;
    const NULL          = 64;
    const PHP           = 127;
    const SPACE         = 128;
    const OBJECT        = 256;
    const OBJECT_STRING = 512;
    const OBJECT_COUNT  = 1024;
    const ALL           = 2047;

    const INVALID  = 'notEmptyInvalid';
    const IS_EMPTY = 'isEmpty';

    protected $_constants = array(
        self::BOOLEAN       => 'boolean',
        self::INTEGER       => 'integer',
        self::FLOAT         => 'float',
        self::STRING        => 'string',
        self::ZERO          => 'zero',
        self::EMPTY_ARRAY   => 'array',
        self::NULL          => 'null',
        self::PHP           => 'php',
        self::SPACE         => 'space',
        self::OBJECT        => 'object',
        self::OBJECT_STRING => 'objectstring',
        self::OBJECT_COUNT  => 'objectcount',
        self::ALL           => 'all',
    );

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::IS_EMPTY => "Value is required and can't be empty",
        self::INVALID  => "Invalid type given. String, integer, float, boolean or array expected",
    );

    /**
     * Internal type to detect
     *
     * @var integer
     */
    protected $_type = 493;

    /**
     * Constructor
     *
     * @param string|array|Zend_Config $options OPTIONAL
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp    = array();
            if (!empty($options)) {
                $temp['type'] = array_shift($options);
            }

            $options = $temp;
        }

        if (is_array($options) && array_key_exists('type', $options)) {
            $this->setType($options['type']);
        }
    }

    /**
     * Returns the set types
     *
     * @return array
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set the types
     *
     * @param  integer|array $type
     * @throws Zend_Validate_Exception
     * @return Zend_Validate_NotEmpty
     */
    public function setType($type = null)
    {
        if (is_array($type)) {
            $detected = 0;
            foreach($type as $value) {
                if (is_int($value)) {
                    $detected += $value;
                } else if (in_array($value, $this->_constants)) {
                    $detected += array_search($value, $this->_constants);
                }
            }

            $type = $detected;
        } else if (is_string($type) && in_array($type, $this->_constants)) {
            $type = array_search($type, $this->_constants);
        }

        if (!is_int($type) || ($type < 0) || ($type > self::ALL)) {
            // require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Unknown type');
        }

        $this->_type = $type;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is not an empty value.
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if ($value !== null && !is_string($value) && !is_int($value) && !is_float($value) &&
            !is_bool($value) && !is_array($value) && !is_object($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $type    = $this->getType();
        $this->_setValue($value);
        $object  = false;

        // OBJECT_COUNT (countable object)
        if ($type >= self::OBJECT_COUNT) {
            $type -= self::OBJECT_COUNT;
            $object = true;

            if (is_object($value) && ($value instanceof Countable) && (count($value) == 0)) {
                $this->_error(self::IS_EMPTY);
                return false;
            }
        }

        // OBJECT_STRING (object's toString)
        if ($type >= self::OBJECT_STRING) {
            $type -= self::OBJECT_STRING;
            $object = true;

            if ((is_object($value) && (!method_exists($value, '__toString'))) ||
                (is_object($value) && (method_exists($value, '__toString')) && (((string) $value) == ""))) {
                $this->_error(self::IS_EMPTY);
                return false;
            }
        }

        // OBJECT (object)
        if ($type >= self::OBJECT) {
            $type -= self::OBJECT;
            // fall trough, objects are always not empty
        } else if ($object === false) {
            // object not allowed but object given -> return false
            if (is_object($value)) {
                $this->_error(self::IS_EMPTY);
                return false;
            }
        }

        // SPACE ('   ')
        if ($type >= self::SPACE) {
            $type -= self::SPACE;
            if (is_string($value) && (preg_match('/^\s+$/s', $value))) {
                $this->_error(self::IS_EMPTY);
                return false;
            }
        }

        // NULL (null)
        if ($type >= self::NULL) {
            $type -= self::NULL;
            if ($value === null) {
                $this->_error(self::IS_EMPTY);
                return false;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type >= self::EMPTY_ARRAY) {
            $type -= self::EMPTY_ARRAY;
            if (is_array($value) && ($value == array())) {
                $this->_error(self::IS_EMPTY);
                return false;
            }
        }

        // ZERO ('0')
        if ($type >= self::ZERO) {
            $type -= self::ZERO;
            if (is_string($value) && ($value == '0')) {
                $this->_error(self::IS_EMPTY);
                return false;
            }
        }

        // STRING ('')
        if ($type >= self::STRING) {
            $type -= self::STRING;
            if (is_string($value) && ($value == '')) {
                $this->_error(self::IS_EMPTY);
                return false;
            }
        }

        // FLOAT (0.0)
        if ($type >= self::FLOAT) {
            $type -= self::FLOAT;
            if (is_float($value) && ($value == 0.0)) {
                $this->_error(self::IS_EMPTY);
                return false;
            }
        }

        // INTEGER (0)
        if ($type >= self::INTEGER) {
            $type -= self::INTEGER;
            if (is_int($value) && ($value == 0)) {
                $this->_error(self::IS_EMPTY);
                return false;
            }
        }

        // BOOLEAN (false)
        if ($type >= self::BOOLEAN) {
            $type -= self::BOOLEAN;
            if (is_bool($value) && ($value == false)) {
                $this->_error(self::IS_EMPTY);
                return false;
            }
        }

        return true;
    }
}
