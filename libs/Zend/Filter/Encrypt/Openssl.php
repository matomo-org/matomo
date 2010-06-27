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
 * @version    $Id: Openssl.php 20288 2010-01-14 20:15:43Z thomas $
 */

/**
 * @see Zend_Filter_Encrypt_Interface
 */
// require_once 'Zend/Filter/Encrypt/Interface.php';

/**
 * Encryption adapter for openssl
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Encrypt_Openssl implements Zend_Filter_Encrypt_Interface
{
    /**
     * Definitions for encryption
     * array(
     *     'public'   => public keys
     *     'private'  => private keys
     *     'envelope' => resulting envelope keys
     * )
     */
    protected $_keys = array(
        'public'   => array(),
        'private'  => array(),
        'envelope' => array()
    );

    /**
     * Internal passphrase
     *
     * @var string
     */
    protected $_passphrase;

    /**
     * Class constructor
     * Available options
     *   'public'     => public key
     *   'private'    => private key
     *   'envelope'   => envelope key
     *   'passphrase' => passphrase
     *
     * @param string|array $options Options for this adapter
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('openssl')) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('This filter needs the openssl extension');
        }

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options)) {
            $options = array('public' => $options);
        }

        if (array_key_exists('passphrase', $options)) {
            $this->setPassphrase($options['passphrase']);
            unset($options['passphrase']);
        }

        $this->_setKeys($options);
    }

    /**
     * Sets the encryption keys
     *
     * @param  string|array $keys Key with type association
     * @return Zend_Filter_Encrypt_Openssl
     */
    protected function _setKeys($keys)
    {
        if (!is_array($keys)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Invalid options argument provided to filter');
        }

        foreach ($keys as $type => $key) {
            if (is_file($key) and is_readable($key)) {
                $file = fopen($key, 'r');
                $cert = fread($file, 8192);
                fclose($file);
            } else {
                $cert = $key;
                $key  = count($this->_keys[$type]);
            }

            switch ($type) {
                case 'public':
                    $test = openssl_pkey_get_public($cert);
                    if ($test === false) {
                        // require_once 'Zend/Filter/Exception.php';
                        throw new Zend_Filter_Exception("Public key '{$cert}' not valid");
                    }

                    openssl_free_key($test);
                    $this->_keys['public'][$key] = $cert;
                    break;
                case 'private':
                    $test = openssl_pkey_get_private($cert, $this->_passphrase);
                    if ($test === false) {
                        // require_once 'Zend/Filter/Exception.php';
                        throw new Zend_Filter_Exception("Private key '{$cert}' not valid");
                    }

                    openssl_free_key($test);
                    $this->_keys['private'][$key] = $cert;
                    break;
                case 'envelope':
                    $this->_keys['envelope'][$key] = $cert;
                    break;
                default:
                    break;
            }
        }

        return $this;
    }

    /**
     * Returns all public keys
     *
     * @return array
     */
    public function getPublicKey()
    {
        return $this->_keys['public'];
    }

    /**
     * Sets public keys
     *
     * @param  string|array $key Public keys
     * @return Zend_Filter_Encrypt_Openssl
     */
    public function setPublicKey($key)
    {
        if (is_array($key)) {
            foreach($key as $type => $option) {
                if ($type !== 'public') {
                    $key['public'] = $option;
                    unset($key[$type]);
                }
            }
        } else {
            $key = array('public' => $key);
        }

        return $this->_setKeys($key);
    }

    /**
     * Returns all private keys
     *
     * @return array
     */
    public function getPrivateKey()
    {
        return $this->_keys['private'];
    }

    /**
     * Sets private keys
     *
     * @param  string $key Private key
     * @param  string $passphrase
     * @return Zend_Filter_Encrypt_Openssl
     */
    public function setPrivateKey($key, $passphrase = null)
    {
        if (is_array($key)) {
            foreach($key as $type => $option) {
                if ($type !== 'private') {
                    $key['private'] = $option;
                    unset($key[$type]);
                }
            }
        } else {
            $key = array('private' => $key);
        }

        if ($passphrase !== null) {
            $this->setPassphrase($passphrase);
        }

        return $this->_setKeys($key);
    }

    /**
     * Returns all envelope keys
     *
     * @return array
     */
    public function getEnvelopeKey()
    {
        return $this->_keys['envelope'];
    }

    /**
     * Sets envelope keys
     *
     * @param  string|array $options Envelope keys
     * @return Zend_Filter_Encrypt_Openssl
     */
    public function setEnvelopeKey($key)
    {
        if (is_array($key)) {
            foreach($key as $type => $option) {
                if ($type !== 'envelope') {
                    $key['envelope'] = $option;
                    unset($key[$type]);
                }
            }
        } else {
            $key = array('envelope' => $key);
        }

        return $this->_setKeys($key);
    }

    /**
     * Returns the passphrase
     *
     * @return string
     */
    public function getPassphrase()
    {
        return $this->_passphrase;
    }

    /**
     * Sets a new passphrase
     *
     * @param string $passphrase
     * @return Zend_Filter_Encrypt_Openssl
     */
    public function setPassphrase($passphrase)
    {
        $this->_passphrase = $passphrase;
        return $this;
    }

    /**
     * Encrypts the file $value with the defined settings
     * Note that you also need the "encrypted" keys to be able to decrypt
     *
     * @param  string $value Content to encrypt
     * @return string The encrypted content
     * @throws Zend_Filter_Exception
     */
    public function encrypt($value)
    {
        $encrypted     = array();
        $encryptedkeys = array();

        if (count($this->_keys['public']) == 0) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Openssl can not encrypt without public keys');
        }

        foreach($this->_keys['public'] as $key => $cert) {
            $keys[$key] = openssl_pkey_get_public($cert);
        }

        $crypt  = openssl_seal($value, $encrypted, $encryptedkeys, $keys);
        foreach ($keys as $key) {
            openssl_free_key($key);
        }

        if ($crypt === false) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Openssl was not able to encrypt you content with the given options');
        }

        $this->_keys['envelope'] = $encryptedkeys;
        return $encrypted;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Decrypts the file $value with the defined settings
     *
     * @param  string $value Content to decrypt
     * @return string The decrypted content
     * @throws Zend_Filter_Exception
     */
    public function decrypt($value)
    {
        $decrypted = "";
        $envelope  = current($this->getEnvelopeKey());

        if (count($this->_keys['private']) !== 1) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Openssl can only decrypt with one private key');
        }

        if (empty($envelope)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Openssl can only decrypt with one envelope key');
        }

        foreach($this->_keys['private'] as $key => $cert) {
            $keys = openssl_pkey_get_private($cert, $this->getPassphrase());
        }

        $crypt  = openssl_open($value, $decrypted, $envelope, $keys);
        openssl_free_key($keys);

        if ($crypt === false) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Openssl was not able to decrypt you content with the given options');
        }

        return $decrypted;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Openssl';
    }
}
