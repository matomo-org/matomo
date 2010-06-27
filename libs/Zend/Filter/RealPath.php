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
 * @version    $Id: RealPath.php 20096 2010-01-06 02:05:09Z bkarwin $
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
class Zend_Filter_RealPath implements Zend_Filter_Interface
{
    /**
     * @var boolean $_pathExists
     */
    protected $_exists = true;

    /**
     * Class constructor
     *
     * @param boolean|Zend_Config $options Options to set
     */
    public function __construct($options = true)
    {
        $this->setExists($options);
    }

    /**
     * Returns true if the filtered path must exist
     *
     * @return boolean
     */
    public function getExists()
    {
        return $this->_exists;
    }

    /**
     * Sets if the path has to exist
     * TRUE when the path must exist
     * FALSE when not existing paths can be given
     *
     * @param boolean|Zend_Config $exists Path must exist
     * @return Zend_Filter_RealPath
     */
    public function setExists($exists)
    {
        if ($exists instanceof Zend_Config) {
            $exists = $exists->toArray();
        }

        if (is_array($exists)) {
            if (isset($exists['exists'])) {
                $exists = (boolean) $exists['exists'];
            }
        }

        $this->_exists = (boolean) $exists;
        return $this;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns realpath($value)
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $path = (string) $value;
        if ($this->_exists) {
            return realpath($path);
        }

        $realpath = @realpath($path);
        if ($realpath) {
            return $realpath;
        }

        $drive = '';
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $path = preg_replace('/[\\\\\/]/', DIRECTORY_SEPARATOR, $path);
            if (preg_match('/([a-zA-Z]\:)(.*)/', $path, $matches)) {
                list($fullMatch, $drive, $path) = $matches;
            } else {
                $cwd   = getcwd();
                $drive = substr($cwd, 0, 2);
                if (substr($path, 0, 1) != DIRECTORY_SEPARATOR) {
                    $path = substr($cwd, 3) . DIRECTORY_SEPARATOR . $path;
                }
            }
        } elseif (substr($path, 0, 1) != DIRECTORY_SEPARATOR) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }

        $stack = array();
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($parts as $dir) {
            if (strlen($dir) && $dir !== '.') {
                if ($dir == '..') {
                    array_pop($stack);
                } else {
                    array_push($stack, $dir);
                }
            }
        }

        return $drive . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $stack);
    }
}
