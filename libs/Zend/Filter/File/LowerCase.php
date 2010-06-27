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
 * @version    $Id: LowerCase.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Filter_StringToLower
 */
// require_once 'Zend/Filter/StringToLower.php';

/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_File_LowerCase extends Zend_Filter_StringToLower
{
    /**
     * Adds options to the filter at initiation
     *
     * @param string $options
     */
    public function __construct($options = null)
    {
        if (!empty($options)) {
            $this->setEncoding($options);
        }
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Does a lowercase on the content of the given file
     *
     * @param  string $value Full path of file to change
     * @return string The given $value
     * @throws Zend_Filter_Exception
     */
    public function filter($value)
    {
        if (!file_exists($value)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("File '$value' not found");
        }

        if (!is_writable($value)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("File '$value' is not writable");
        }

        $content = file_get_contents($value);
        if (!$content) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("Problem while reading file '$value'");
        }

        $content = parent::filter($content);
        $result  = file_put_contents($value, $content);

        if (!$result) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("Problem while writing file '$value'");
        }

        return $value;
    }
}
