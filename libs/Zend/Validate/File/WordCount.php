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
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: WordCount.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Validate_File_Count
 */
// require_once 'Zend/Validate/File/Count.php';

/**
 * Validator for counting all words in a file
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_WordCount extends Zend_Validate_File_Count
{
    /**#@+
     * @const string Error constants
     */
    const TOO_MUCH  = 'fileWordCountTooMuch';
    const TOO_LESS  = 'fileWordCountTooLess';
    const NOT_FOUND = 'fileWordCountNotFound';
    /**#@-*/

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::TOO_MUCH => "Too much words, maximum '%max%' are allowed but '%count%' were counted",
        self::TOO_LESS => "Too less words, minimum '%min%' are expected but '%count%' were counted",
        self::NOT_FOUND => "File '%value%' is not readable or does not exist",
    );

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the counted words are at least min and
     * not bigger than max (when max is not null).
     *
     * @param  string $value Filename to check for word count
     * @param  array  $file  File data from Zend_File_Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        // Is file readable ?
        // require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_FOUND);
        }

        $content = file_get_contents($value);
        $this->_count = str_word_count($content);
        if (($this->_max !== null) && ($this->_count > $this->_max)) {
            return $this->_throw($file, self::TOO_MUCH);
        }

        if (($this->_min !== null) && ($this->_count < $this->_min)) {
            return $this->_throw($file, self::TOO_LESS);
        }

        return true;
    }

    /**
     * Throws an error of the given type
     *
     * @param  string $file
     * @param  string $errorType
     * @return false
     */
    protected function _throw($file, $errorType)
    {
        if ($file !== null) {
            $this->_value = $file['name'];
        }

        $this->_error($errorType);
        return false;
    }
}
