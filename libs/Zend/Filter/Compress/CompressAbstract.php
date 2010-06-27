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
 * @version    $Id: CompressAbstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Filter_Compress_CompressInterface
 */
// require_once 'Zend/Filter/Compress/CompressInterface.php';

/**
 * Abstract compression adapter
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Filter_Compress_CompressAbstract implements Zend_Filter_Compress_CompressInterface
{
    /**
     * Class constructor
     *
     * @param array|Zend_Config $options (Optional) Options to set
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Returns one or all set options
     *
     * @param string $option (Optional) Option to return
     * @return mixed
     */
    public function getOptions($option = null)
    {
        if ($option === null) {
            return $this->_options;
        }

        if (!array_key_exists($option, $this->_options)) {
            return null;
        }

        return $this->_options[$option];
    }

    /**
     * Sets all or one option
     *
     * @param  array $options
     * @return Zend_Filter_Compress_Bz2
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $option) {
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->$method($option);
            }
        }

        return $this;
    }
}
