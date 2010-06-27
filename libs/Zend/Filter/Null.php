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
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Null.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Filter_Interface
 */
// require_once 'Zend/Filter/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Null implements Zend_Filter_Interface
{
    const BOOLEAN      = 1;
    const INTEGER      = 2;
    const EMPTY_ARRAY  = 4;
    const STRING       = 8;
    const ZERO         = 16;
    const ALL          = 31;

    protected $_constants = array(
        self::BOOLEAN     => 'boolean',
        self::INTEGER     => 'integer',
        self::EMPTY_ARRAY => 'array',
        self::STRING      => 'string',
        self::ZERO        => 'zero',
        self::ALL         => 'all'
    );

    /**
     * Internal type to detect
     *
     * @var integer
     */
    protected $_type = self::ALL;

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
                $temp = array_shift($options);
            }
            $options = $temp;
        } else if (is_array($options) && array_key_exists('type', $options)) {
            $options = $options['type'];
        }

        if (!empty($options)) {
            $this->setType($options);
        }
    }

    /**
     * Returns the set null types
     *
     * @return array
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set the null types
     *
     * @param  integer|array $type
     * @throws Zend_Filter_Exception
     * @return Zend_Filter_Null
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
        } else if (is_string($type)) {
            if (in_array($type, $this->_constants)) {
                $type = array_search($type, $this->_constants);
            }
        }

        if (!is_int($type) || ($type < 0) || ($type > self::ALL)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Unknown type');
        }

        $this->_type = $type;
        return $this;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns null representation of $value, if value is empty and matches
     * types that should be considered null.
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $type = $this->getType();

        // STRING ZERO ('0')
        if ($type >= self::ZERO) {
            $type -= self::ZERO;
            if (is_string($value) && ($value == '0')) {
                return null;
            }
        }

        // STRING ('')
        if ($type >= self::STRING) {
            $type -= self::STRING;
            if (is_string($value) && ($value == '')) {
                return null;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type >= self::EMPTY_ARRAY) {
            $type -= self::EMPTY_ARRAY;
            if (is_array($value) && ($value == array())) {
                return null;
            }
        }

        // INTEGER (0)
        if ($type >= self::INTEGER) {
            $type -= self::INTEGER;
            if (is_int($value) && ($value == 0)) {
                return null;
            }
        }

        // BOOLEAN (false)
        if ($type >= self::BOOLEAN) {
            $type -= self::BOOLEAN;
            if (is_bool($value) && ($value == false)) {
                return null;
            }
        }

        return $value;
    }
}
