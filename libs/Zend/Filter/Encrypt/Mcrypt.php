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
 * @version    $Id: Mcrypt.php 20132 2010-01-07 21:33:50Z ralph $
 */

/**
 * @see Zend_Filter_Encrypt_Interface
 */
// require_once 'Zend/Filter/Encrypt/Interface.php';

/**
 * Encryption adapter for mcrypt
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Encrypt_Mcrypt implements Zend_Filter_Encrypt_Interface
{
    /**
     * Definitions for encryption
     * array(
     *     'key' => encryption key string
     *     'algorithm' => algorithm to use
     *     'algorithm_directory' => directory where to find the algorithm
     *     'mode' => encryption mode to use
     *     'modedirectory' => directory where to find the mode
     * )
     */
    protected $_encryption = array(
        'key'                 => 'ZendFramework',
        'algorithm'           => 'blowfish',
        'algorithm_directory' => '',
        'mode'                => 'cbc',
        'mode_directory'      => '',
        'vector'              => null,
        'salt'                => false
    );

    protected static $_srandCalled = false;
    
    /**
     * Class constructor
     *
     * @param string|array|Zend_Config $options Cryption Options
     */
    public function __construct($options)
    {
        if (!extension_loaded('mcrypt')) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('This filter needs the mcrypt extension');
        }

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (is_string($options)) {
            $options = array('key' => $options);
        } elseif (!is_array($options)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Invalid options argument provided to filter');
        }

        $this->setEncryption($options);
    }

    /**
     * Returns the set encryption options
     *
     * @return array
     */
    public function getEncryption()
    {
        return $this->_encryption;
    }

    /**
     * Sets new encryption options
     *
     * @param  string|array $options Encryption options
     * @return Zend_Filter_File_Encryption
     */
    public function setEncryption($options)
    {
        if (is_string($options)) {
            $options = array('key' => $options);
        }

        if (!is_array($options)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Invalid options argument provided to filter');
        }

        $options = $options + $this->getEncryption();
        $algorithms = mcrypt_list_algorithms($options['algorithm_directory']);
        if (!in_array($options['algorithm'], $algorithms)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("The algorithm '{$options['algorithm']}' is not supported");
        }

        $modes = mcrypt_list_modes($options['mode_directory']);
        if (!in_array($options['mode'], $modes)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("The mode '{$options['mode']}' is not supported");
        }

        if (!mcrypt_module_self_test($options['algorithm'], $options['algorithm_directory'])) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('The given algorithm can not be used due an internal mcrypt problem');
        }

        if (!isset($options['vector'])) {
            $options['vector'] = null;
        }

        $this->_encryption = $options;
        $this->setVector($options['vector']);

        return $this;
    }

    /**
     * Returns the set vector
     *
     * @return string
     */
    public function getVector()
    {
        return $this->_encryption['vector'];
    }

    /**
     * Sets the initialization vector
     *
     * @param string $vector (Optional) Vector to set
     * @return Zend_Filter_Encrypt_Mcrypt
     */
    public function setVector($vector = null)
    {
        $cipher = $this->_openCipher();
        $size   = mcrypt_enc_get_iv_size($cipher);
        if (empty($vector)) {
            $this->_srand();
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && version_compare(PHP_VERSION, '5.3.0', '<')) {
                $method = MCRYPT_RAND;
            } else {
                if (file_exists('/dev/urandom') || (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')) {
                    $method = MCRYPT_DEV_URANDOM;
                } elseif (file_exists('/dev/random')) {
                    $method = MCRYPT_DEV_RANDOM;
                } else {
                    $method = MCRYPT_RAND;
                }
            }
            $vector = mcrypt_create_iv($size, $method);
        } else if (strlen($vector) != $size) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('The given vector has a wrong size for the set algorithm');
        }

        $this->_encryption['vector'] = $vector;
        $this->_closeCipher($cipher);

        return $this;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Encrypts the file $value with the defined settings
     *
     * @param  string $value Full path of file to change
     * @return string The filename which has been set, or false when there were errors
     */
    public function encrypt($value)
    {
        $cipher  = $this->_openCipher();
        $this->_initCipher($cipher);
        $encrypted = mcrypt_generic($cipher, $value);
        mcrypt_generic_deinit($cipher);
        $this->_closeCipher($cipher);

        return $encrypted;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Decrypts the file $value with the defined settings
     *
     * @param  string $value Full path of file to change
     * @return string The filename which has been set, or false when there were errors
     */
    public function decrypt($value)
    {
        $cipher = $this->_openCipher();
        $this->_initCipher($cipher);
        $decrypted = mdecrypt_generic($cipher, $value);
        mcrypt_generic_deinit($cipher);
        $this->_closeCipher($cipher);

        return $decrypted;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Mcrypt';
    }

    /**
     * Open a cipher
     *
     * @throws Zend_Filter_Exception When the cipher can not be opened
     * @return resource Returns the opened cipher
     */
    protected function _openCipher()
    {
        $cipher = mcrypt_module_open(
            $this->_encryption['algorithm'],
            $this->_encryption['algorithm_directory'],
            $this->_encryption['mode'],
            $this->_encryption['mode_directory']);

        if ($cipher === false) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Mcrypt can not be opened with your settings');
        }

        return $cipher;
    }

    /**
     * Close a cipher
     *
     * @param  resource $cipher Cipher to close
     * @return Zend_Filter_Encrypt_Mcrypt
     */
    protected function _closeCipher($cipher)
    {
        mcrypt_module_close($cipher);

        return $this;
    }

    /**
     * Initialises the cipher with the set key
     *
     * @param  resource $cipher
     * @throws
     * @return resource
     */
    protected function _initCipher($cipher)
    {
        $key = $this->_encryption['key'];

        $keysizes = mcrypt_enc_get_supported_key_sizes($cipher);
        if (empty($keysizes) || ($this->_encryption['salt'] == true)) {
            $this->_srand();
            $keysize = mcrypt_enc_get_key_size($cipher);
            $key     = substr(md5($key), 0, $keysize);
        } else if (!in_array(strlen($key), $keysizes)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('The given key has a wrong size for the set algorithm');
        }

        $result = mcrypt_generic_init($cipher, $key, $this->_encryption['vector']);
        if ($result < 0) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Mcrypt could not be initialize with the given setting');
        }

        return $this;
    }
    
    /**
     * _srand() interception
     * 
     * @see ZF-8742
     */
    protected function _srand()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            return;
        }
        
        if (!self::$_srandCalled) {
            srand((double) microtime() * 1000000);
            self::$_srandCalled = true;
        }
    }
}
