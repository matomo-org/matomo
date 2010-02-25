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
 * @package    Zend_Auth
 * @subpackage Zend_Auth_Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Ldap.php 21009 2010-02-09 14:06:42Z sgehrig $
 */

/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Auth
 * @subpackage Zend_Auth_Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Auth_Adapter_Ldap implements Zend_Auth_Adapter_Interface
{

    /**
     * The Zend_Ldap context.
     *
     * @var Zend_Ldap
     */
    protected $_ldap = null;

    /**
     * The array of arrays of Zend_Ldap options passed to the constructor.
     *
     * @var array
     */
    protected $_options = null;

    /**
     * The username of the account being authenticated.
     *
     * @var string
     */
    protected $_username = null;

    /**
     * The password of the account being authenticated.
     *
     * @var string
     */
    protected $_password = null;

    /**
     * The DN of the authenticated account. Used to retrieve the account entry on request.
     *
     * @var string
     */
    protected $_authenticatedDn = null;

    /**
     * Constructor
     *
     * @param  array  $options  An array of arrays of Zend_Ldap options
     * @param  string $username The username of the account being authenticated
     * @param  string $password The password of the account being authenticated
     * @return void
     */
    public function __construct(array $options = array(), $username = null, $password = null)
    {
        $this->setOptions($options);
        if ($username !== null) {
            $this->setUsername($username);
        }
        if ($password !== null) {
            $this->setPassword($password);
        }
    }

    /**
     * Returns the array of arrays of Zend_Ldap options of this adapter.
     *
     * @return array|null
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Sets the array of arrays of Zend_Ldap options to be used by
     * this adapter.
     *
     * @param  array $options The array of arrays of Zend_Ldap options
     * @return Zend_Auth_Adapter_Ldap Provides a fluent interface
     */
    public function setOptions($options)
    {
        $this->_options = is_array($options) ? $options : array();
        return $this;
    }

    /**
     * Returns the username of the account being authenticated, or
     * NULL if none is set.
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * Sets the username for binding
     *
     * @param  string $username The username for binding
     * @return Zend_Auth_Adapter_Ldap Provides a fluent interface
     */
    public function setUsername($username)
    {
        $this->_username = (string) $username;
        return $this;
    }

    /**
     * Returns the password of the account being authenticated, or
     * NULL if none is set.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Sets the passwort for the account
     *
     * @param  string $password The password of the account being authenticated
     * @return Zend_Auth_Adapter_Ldap Provides a fluent interface
     */
    public function setPassword($password)
    {
        $this->_password = (string) $password;
        return $this;
    }

    /**
     * setIdentity() - set the identity (username) to be used
     *
     * Proxies to {@see setUsername()}
     *
     * Closes ZF-6813
     *
     * @param  string $identity
     * @return Zend_Auth_Adapter_Ldap Provides a fluent interface
     */
    public function setIdentity($identity)
    {
        return $this->setUsername($identity);
    }

    /**
     * setCredential() - set the credential (password) value to be used
     *
     * Proxies to {@see setPassword()}
     *
     * Closes ZF-6813
     *
     * @param  string $credential
     * @return Zend_Auth_Adapter_Ldap Provides a fluent interface
     */
    public function setCredential($credential)
    {
        return $this->setPassword($credential);
    }

    /**
     * Returns the LDAP Object
     *
     * @return Zend_Ldap The Zend_Ldap object used to authenticate the credentials
     */
    public function getLdap()
    {
        if ($this->_ldap === null) {
            /**
             * @see Zend_Ldap
             */
            require_once 'Zend/Ldap.php';
            $this->_ldap = new Zend_Ldap();
        }

        return $this->_ldap;
    }

    /**
     * Set an Ldap connection
     *
     * @param Zend_Ldap $ldap An existing Ldap object
     * @return Zend_Auth_Adapter_Ldap Provides a fluent interface
     */
    public function setLdap(Zend_Ldap $ldap)
    {
        $this->_ldap = $ldap;

        $this->setOptions(array($ldap->getOptions()));

        return $this;
    }

    /**
     * Returns a domain name for the current LDAP options. This is used
     * for skipping redundant operations (e.g. authentications).
     *
     * @return string
     */
    protected function _getAuthorityName()
    {
        $options = $this->getLdap()->getOptions();
        $name = $options['accountDomainName'];
        if (!$name)
            $name = $options['accountDomainNameShort'];
        return $name ? $name : '';
    }

    /**
     * Authenticate the user
     *
     * @throws Zend_Auth_Adapter_Exception
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        /**
         * @see Zend_Ldap_Exception
         */
        require_once 'Zend/Ldap/Exception.php';

        $messages = array();
        $messages[0] = ''; // reserved
        $messages[1] = ''; // reserved

        $username = $this->_username;
        $password = $this->_password;

        if (!$username) {
            $code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $messages[0] = 'A username is required';
            return new Zend_Auth_Result($code, '', $messages);
        }
        if (!$password) {
            /* A password is required because some servers will
             * treat an empty password as an anonymous bind.
             */
            $code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $messages[0] = 'A password is required';
            return new Zend_Auth_Result($code, '', $messages);
        }

        $ldap = $this->getLdap();

        $code = Zend_Auth_Result::FAILURE;
        $messages[0] = "Authority not found: $username";
        $failedAuthorities = array();

        /* Iterate through each server and try to authenticate the supplied
         * credentials against it.
         */
        foreach ($this->_options as $name => $options) {

            if (!is_array($options)) {
                /**
                 * @see Zend_Auth_Adapter_Exception
                 */
                require_once 'Zend/Auth/Adapter/Exception.php';
                throw new Zend_Auth_Adapter_Exception('Adapter options array not in array');
            }
            $adapterOptions = $this->_prepareOptions($ldap, $options);
            $dname = '';

            try {
                if ($messages[1])
                    $messages[] = $messages[1];
                $messages[1] = '';
                $messages[] = $this->_optionsToString($options);

                $dname = $this->_getAuthorityName();
                if (isset($failedAuthorities[$dname])) {
                    /* If multiple sets of server options for the same domain
                     * are supplied, we want to skip redundant authentications
                     * where the identity or credentials where found to be
                     * invalid with another server for the same domain. The
                     * $failedAuthorities array tracks this condition (and also
                     * serves to supply the original error message).
                     * This fixes issue ZF-4093.
                     */
                    $messages[1] = $failedAuthorities[$dname];
                    $messages[] = "Skipping previously failed authority: $dname";
                    continue;
                }

                $canonicalName = $ldap->getCanonicalAccountName($username);
                $ldap->bind($canonicalName, $password);
                /*
                 * Fixes problem when authenticated user is not allowed to retrieve
                 * group-membership information or own account.
                 * This requires that the user specified with "username" and optionally
                 * "password" in the Zend_Ldap options is able to retrieve the required
                 * information.
                 */
                $requireRebind = false;
                if (isset($options['username'])) {
                    $ldap->bind();
                    $requireRebind = true;
                }
                $dn = $ldap->getCanonicalAccountName($canonicalName, Zend_Ldap::ACCTNAME_FORM_DN);

                $groupResult = $this->_checkGroupMembership($ldap, $canonicalName, $dn, $adapterOptions);
                if ($groupResult === true) {
                    $this->_authenticatedDn = $dn;
                    $messages[0] = '';
                    $messages[1] = '';
                    $messages[] = "$canonicalName authentication successful";
                    if ($requireRebind === true) {
	                    // rebinding with authenticated user
	                    $ldap->bind($dn, $password);
                    }
                    return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $canonicalName, $messages);
                } else {
                    $messages[0] = 'Account is not a member of the specified group';
                    $messages[1] = $groupResult;
                    $failedAuthorities[$dname] = $groupResult;
                }
            } catch (Zend_Ldap_Exception $zle) {

                /* LDAP based authentication is notoriously difficult to diagnose. Therefore
                 * we bend over backwards to capture and record every possible bit of
                 * information when something goes wrong.
                 */

                $err = $zle->getCode();

                if ($err == Zend_Ldap_Exception::LDAP_X_DOMAIN_MISMATCH) {
                    /* This error indicates that the domain supplied in the
                     * username did not match the domains in the server options
                     * and therefore we should just skip to the next set of
                     * server options.
                     */
                    continue;
                } else if ($err == Zend_Ldap_Exception::LDAP_NO_SUCH_OBJECT) {
                    $code = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
                    $messages[0] = "Account not found: $username";
                    $failedAuthorities[$dname] = $zle->getMessage();
                } else if ($err == Zend_Ldap_Exception::LDAP_INVALID_CREDENTIALS) {
                    $code = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
                    $messages[0] = 'Invalid credentials';
                    $failedAuthorities[$dname] = $zle->getMessage();
                } else {
                    $line = $zle->getLine();
                    $messages[] = $zle->getFile() . "($line): " . $zle->getMessage();
                    $messages[] = str_replace($password, '*****', $zle->getTraceAsString());
                    $messages[0] = 'An unexpected failure occurred';
                }
                $messages[1] = $zle->getMessage();
            }
        }

        $msg = isset($messages[1]) ? $messages[1] : $messages[0];
        $messages[] = "$username authentication failed: $msg";

        return new Zend_Auth_Result($code, $username, $messages);
    }

    /**
     * Sets the LDAP specific options on the Zend_Ldap instance
     *
     * @param  Zend_Ldap $ldap
     * @param  array $options
     * @return array of auth-adapter specific options
     */
    protected function _prepareOptions(Zend_Ldap $ldap, array $options)
    {
        $adapterOptions = array(
            'group'       => null,
            'groupDn'     => $ldap->getBaseDn(),
            'groupScope'  => Zend_Ldap::SEARCH_SCOPE_SUB,
            'groupAttr'   => 'cn',
            'groupFilter' => 'objectClass=groupOfUniqueNames',
            'memberAttr'  => 'uniqueMember',
            'memberIsDn'  => true
        );
        foreach ($adapterOptions as $key => $value) {
            if (array_key_exists($key, $options)) {
                $value = $options[$key];
                unset($options[$key]);
                switch ($key) {
                    case 'groupScope':
                        $value = (int)$value;
                        if (in_array($value, array(Zend_Ldap::SEARCH_SCOPE_BASE,
                                Zend_Ldap::SEARCH_SCOPE_ONE, Zend_Ldap::SEARCH_SCOPE_SUB), true)) {
                           $adapterOptions[$key] = $value;
                        }
                        break;
                    case 'memberIsDn':
                        $adapterOptions[$key] = ($value === true ||
                                $value === '1' || strcasecmp($value, 'true') == 0);
                        break;
                    default:
                        $adapterOptions[$key] = trim($value);
                        break;
                }
            }
        }
        $ldap->setOptions($options);
        return $adapterOptions;
    }

    /**
     * Checks the group membership of the bound user
     *
     * @param  Zend_Ldap $ldap
     * @param  string    $canonicalName
     * @param  string    $dn
     * @param  array     $adapterOptions
     * @return string|true
     */
    protected function _checkGroupMembership(Zend_Ldap $ldap, $canonicalName, $dn, array $adapterOptions)
    {
        if ($adapterOptions['group'] === null) {
            return true;
        }

        if ($adapterOptions['memberIsDn'] === false) {
            $user = $canonicalName;
        } else {
            $user = $dn;
        }

        /**
         * @see Zend_Ldap_Filter
         */
        require_once 'Zend/Ldap/Filter.php';
        $groupName = Zend_Ldap_Filter::equals($adapterOptions['groupAttr'], $adapterOptions['group']);
        $membership = Zend_Ldap_Filter::equals($adapterOptions['memberAttr'], $user);
        $group = Zend_Ldap_Filter::andFilter($groupName, $membership);
        $groupFilter = $adapterOptions['groupFilter'];
        if (!empty($groupFilter)) {
            $group = $group->addAnd($groupFilter);
        }

        $result = $ldap->count($group, $adapterOptions['groupDn'], $adapterOptions['groupScope']);

        if ($result === 1) {
            return true;
        } else {
            return 'Failed to verify group membership with ' . $group->toString();
        }
    }

    /**
     * getAccountObject() - Returns the result entry as a stdClass object
     *
     * This resembles the feature {@see Zend_Auth_Adapter_DbTable::getResultRowObject()}.
     * Closes ZF-6813
     *
     * @param  array $returnAttribs
     * @param  array $omitAttribs
     * @return stdClass|boolean
     */
    public function getAccountObject(array $returnAttribs = array(), array $omitAttribs = array())
    {
        if (!$this->_authenticatedDn) {
            return false;
        }

        $returnObject = new stdClass();

        $omitAttribs = array_map('strtolower', $omitAttribs);

        $entry = $this->getLdap()->getEntry($this->_authenticatedDn, $returnAttribs, true);
        foreach ($entry as $attr => $value) {
            if (in_array($attr, $omitAttribs)) {
                // skip attributes marked to be omitted
                continue;
            }
            if (is_array($value)) {
                $returnObject->$attr = (count($value) > 1) ? $value : $value[0];
            } else {
                $returnObject->$attr = $value;
            }
        }
        return $returnObject;
    }

    /**
     * Converts options to string
     *
     * @param  array $options
     * @return string
     */
    private function _optionsToString(array $options)
    {
        $str = '';
        foreach ($options as $key => $val) {
            if ($key === 'password')
                $val = '*****';
            if ($str)
                $str .= ',';
            $str .= $key . '=' . $val;
        }
        return $str;
    }
}
