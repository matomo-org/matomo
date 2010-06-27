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
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Element_Xhtml */
// require_once 'Zend/Form/Element/Xhtml.php';

/**
 * CSRF form protection
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Hash.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Zend_Form_Element_Hash extends Zend_Form_Element_Xhtml
{
    /**
     * Use formHidden view helper by default
     * @var string
     */
    public $helper = 'formHidden';

    /**
     * Actual hash used.
     *
     * @var mixed
     */
    protected $_hash;

    /**
     * Salt for CSRF token
     * @var string
     */
    protected $_salt = 'salt';

    /**
     * @var Zend_Session_Namespace
     */
    protected $_session;

    /**
     * TTL for CSRF token
     * @var int
     */
    protected $_timeout = 300;

    /**
     * Constructor
     *
     * Creates session namespace for CSRF token, and adds validator for CSRF
     * token.
     *
     * @param  string|array|Zend_Config $spec
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($spec, $options = null)
    {
        parent::__construct($spec, $options);

        $this->setAllowEmpty(false)
             ->setRequired(true)
             ->initCsrfValidator();
    }

    /**
     * Set session object
     *
     * @param  Zend_Session_Namespace $session
     * @return Zend_Form_Element_Hash
     */
    public function setSession($session)
    {
        $this->_session = $session;
        return $this;
    }

    /**
     * Get session object
     *
     * Instantiate session object if none currently exists
     *
     * @return Zend_Session_Namespace
     */
    public function getSession()
    {
        if (null === $this->_session) {
            // require_once 'Zend/Session/Namespace.php';
            $this->_session = new Zend_Session_Namespace($this->getSessionName());
        }
        return $this->_session;
    }

    /**
     * Initialize CSRF validator
     *
     * Creates Session namespace, and initializes CSRF token in session.
     * Additionally, adds validator for validating CSRF token.
     *
     * @return Zend_Form_Element_Hash
     */
    public function initCsrfValidator()
    {
        $session = $this->getSession();
        if (isset($session->hash)) {
            $rightHash = $session->hash;
        } else {
            $rightHash = null;
        }

        $this->addValidator('Identical', true, array($rightHash));
        return $this;
    }

    /**
     * Salt for CSRF token
     *
     * @param  string $salt
     * @return Zend_Form_Element_Hash
     */
    public function setSalt($salt)
    {
        $this->_salt = (string) $salt;
        return $this;
    }

    /**
     * Retrieve salt for CSRF token
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->_salt;
    }

    /**
     * Retrieve CSRF token
     *
     * If no CSRF token currently exists, generates one.
     *
     * @return string
     */
    public function getHash()
    {
        if (null === $this->_hash) {
            $this->_generateHash();
        }
        return $this->_hash;
    }

    /**
     * Get session namespace for CSRF token
     *
     * Generates a session namespace based on salt, element name, and class.
     *
     * @return string
     */
    public function getSessionName()
    {
        return __CLASS__ . '_' . $this->getSalt() . '_' . $this->getName();
    }

    /**
     * Set timeout for CSRF session token
     *
     * @param  int $ttl
     * @return Zend_Form_Element_Hash
     */
    public function setTimeout($ttl)
    {
        $this->_timeout = (int) $ttl;
        return $this;
    }

    /**
     * Get CSRF session token timeout
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * Override getLabel() to always be empty
     *
     * @return null
     */
    public function getLabel()
    {
        return null;
    }

    /**
     * Initialize CSRF token in session
     *
     * @return void
     */
    public function initCsrfToken()
    {
        $session = $this->getSession();
        $session->setExpirationHops(1, null, true);
        $session->setExpirationSeconds($this->getTimeout());
        $session->hash = $this->getHash();
    }

    /**
     * Render CSRF token in form
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        $this->initCsrfToken();
        return parent::render($view);
    }

    /**
     * Generate CSRF token
     *
     * Generates CSRF token and stores both in {@link $_hash} and element
     * value.
     *
     * @return void
     */
    protected function _generateHash()
    {
        $this->_hash = md5(
            mt_rand(1,1000000)
            .  $this->getSalt()
            .  $this->getName()
            .  mt_rand(1,1000000)
        );
        $this->setValue($this->_hash);
    }
}
