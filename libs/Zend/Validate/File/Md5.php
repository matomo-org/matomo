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
 * @version   $Id: Md5.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Validate_File_Hash
 */
// require_once 'Zend/Validate/File/Hash.php';

/**
 * Validator for the md5 hash of given files
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_Md5 extends Zend_Validate_File_Hash
{
    /**
     * @const string Error constants
     */
    const DOES_NOT_MATCH = 'fileMd5DoesNotMatch';
    const NOT_DETECTED   = 'fileMd5NotDetected';
    const NOT_FOUND      = 'fileMd5NotFound';

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::DOES_NOT_MATCH => "File '%value%' does not match the given md5 hashes",
        self::NOT_DETECTED   => "A md5 hash could not be evaluated for the given file",
        self::NOT_FOUND      => "File '%value%' is not readable or does not exist",
    );

    /**
     * Hash of the file
     *
     * @var string
     */
    protected $_hash;

    /**
     * Sets validator options
     *
     * $hash is the hash we accept for the file $file
     *
     * @param  string|array $options
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (is_scalar($options)) {
            $options = array('hash1' => $options);
        } elseif (!is_array($options)) {
            // require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Invalid options to validator provided');
        }

        $this->setMd5($options);
    }

    /**
     * Returns all set md5 hashes
     *
     * @return array
     */
    public function getMd5()
    {
        return $this->getHash();
    }

    /**
     * Sets the md5 hash for one or multiple files
     *
     * @param  string|array $options
     * @param  string       $algorithm (Deprecated) Algorithm to use, fixed to md5
     * @return Zend_Validate_File_Hash Provides a fluent interface
     */
    public function setHash($options)
    {
        if (!is_array($options)) {
            $options = (array) $options;
        }

        $options['algorithm'] = 'md5';
        parent::setHash($options);
        return $this;
    }

    /**
     * Sets the md5 hash for one or multiple files
     *
     * @param  string|array $options
     * @return Zend_Validate_File_Hash Provides a fluent interface
     */
    public function setMd5($options)
    {
        $this->setHash($options);
        return $this;
    }

    /**
     * Adds the md5 hash for one or multiple files
     *
     * @param  string|array $options
     * @param  string       $algorithm (Deprecated) Algorithm to use, fixed to md5
     * @return Zend_Validate_File_Hash Provides a fluent interface
     */
    public function addHash($options)
    {
        if (!is_array($options)) {
            $options = (array) $options;
        }

        $options['algorithm'] = 'md5';
        parent::addHash($options);
        return $this;
    }

    /**
     * Adds the md5 hash for one or multiple files
     *
     * @param  string|array $options
     * @return Zend_Validate_File_Hash Provides a fluent interface
     */
    public function addMd5($options)
    {
        $this->addHash($options);
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the given file confirms the set hash
     *
     * @param  string $value Filename to check for hash
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

        $hashes = array_unique(array_keys($this->_hash));
        $filehash = hash_file('md5', $value);
        if ($filehash === false) {
            return $this->_throw($file, self::NOT_DETECTED);
        }

        foreach($hashes as $hash) {
            if ($filehash === $hash) {
                return true;
            }
        }

        return $this->_throw($file, self::DOES_NOT_MATCH);
    }
}
