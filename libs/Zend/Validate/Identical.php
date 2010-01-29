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
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Identical.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** @see Zend_Validate_Abstract */
require_once 'Zend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Identical extends Zend_Validate_Abstract
{
    /**
     * Error codes
     * @const string
     */
    const NOT_SAME      = 'notSame';
    const MISSING_TOKEN = 'missingToken';

    /**
     * Error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_SAME      => "The token '%token%' does not match the given token '%value%'",
        self::MISSING_TOKEN => 'No token was provided to match against',
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'token' => '_tokenString'
    );

    /**
     * Original token against which to validate
     * @var string
     */
    protected $_tokenString;
    protected $_token;

    /**
     * Sets validator options
     *
     * @param  mixed $token
     * @return void
     */
    public function __construct($token = null)
    {
        if ($token instanceof Zend_Config) {
            $token = $token->toArray();
        }

        if (is_array($token) && (count($token) == 1) && array_key_exists('token', $token)) {
            $token = $token['token'];
        }

        if (null !== $token) {
            $this->setToken($token);
        }
    }

    /**
     * Set token against which to compare
     *
     * @param  mixed $token
     * @return Zend_Validate_Identical
     */
    public function setToken($token)
    {
        $this->_tokenString = (string) $token;
        $this->_token       = $token;
        return $this;
    }

    /**
     * Retrieve token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue((string) $value);
        $token        = $this->getToken();

        if ($token === null) {
            $this->_error(self::MISSING_TOKEN);
            return false;
        }

        if ($value !== $token)  {
            $this->_error(self::NOT_SAME);
            return false;
        }

        return true;
    }
}
