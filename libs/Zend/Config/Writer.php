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
 * @package    Zend_Config
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Writer.php 23953 2011-05-03 05:47:39Z ralph $
 */

/**
 * @category   Zend
 * @package    Zend_Config
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Config_Writer
{
    /**
     * Option keys to skip when calling setOptions()
     *
     * @var array
     */
    protected $_skipOptions = array(
        'options'
    );

    /**
     * Config object to write
     *
     * @var Zend_Config
     */
    protected $_config = null;

    /**
     * Create a new adapter
     *
     * $options can only be passed as array or be omitted
     *
     * @param null|array $options
     */
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set options via a Zend_Config instance
     *
     * @param  Zend_Config $config
     * @return Zend_Config_Writer
     */
    public function setConfig(Zend_Config $config)
    {
        $this->_config = $config;

        return $this;
    }

    /**
     * Set options via an array
     *
     * @param  array $options
     * @return Zend_Config_Writer
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array(strtolower($key), $this->_skipOptions)) {
                continue;
            }

            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Write a Zend_Config object to it's target
     *
     * @return void
     */
    abstract public function write();
}
