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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Crc32.php 22697 2010-07-26 21:14:47Z alexander $
 */

/**
 * @see Zend_Validate_File_Hash
 */
// require_once 'Zend/Validate/File/Hash.php';

/**
 * Validator for the crc32 hash of given files
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_Crc32 extends Zend_Validate_File_Hash
{
    /**
     * @const string Error constants
     */
    const DOES_NOT_MATCH = 'fileCrc32DoesNotMatch';
    const NOT_DETECTED   = 'fileCrc32NotDetected';
    const NOT_FOUND      = 'fileCrc32NotFound';

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::DOES_NOT_MATCH => "File '%value%' does not match the given crc32 hashes",
        self::NOT_DETECTED   => "A crc32 hash could not be evaluated for the given file",
        self::NOT_FOUND      => "File '%value%' could not be found",
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
     * @param  string|array|Zend_Config $options
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

        $this->setCrc32($options);
    }

    /**
     * Returns all set crc32 hashes
     *
     * @return array
     */
    public function getCrc32()
    {
        return $this->getHash();
    }

    /**
     * Sets the crc32 hash for one or multiple files
     *
     * @param  string|array $options
     * @return Zend_Validate_File_Hash Provides a fluent interface
     */
    public function setHash($options)
    {
        if (!is_array($options)) {
            $options = array($options);
        }

        $options['algorithm'] = 'crc32';
        parent::setHash($options);
        return $this;
    }

    /**
     * Sets the crc32 hash for one or multiple files
     *
     * @param  string|array $options
     * @return Zend_Validate_File_Hash Provides a fluent interface
     */
    public function setCrc32($options)
    {
        $this->setHash($options);
        return $this;
    }

    /**
     * Adds the crc32 hash for one or multiple files
     *
     * @param  string|array $options
     * @return Zend_Validate_File_Hash Provides a fluent interface
     */
    public function addHash($options)
    {
        if (!is_array($options)) {
            $options = array($options);
        }

        $options['algorithm'] = 'crc32';
        parent::addHash($options);
        return $this;
    }

    /**
     * Adds the crc32 hash for one or multiple files
     *
     * @param  string|array $options
     * @return Zend_Validate_File_Hash Provides a fluent interface
     */
    public function addCrc32($options)
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
        $filehash = hash_file('crc32', $value);
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