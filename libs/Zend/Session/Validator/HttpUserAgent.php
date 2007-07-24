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
 * @package    Zend_Session
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: HttpUserAgent.php 2812 2007-01-16 01:40:54Z bkarwin $
 * @since      Preview Release 0.2
 */

/**
 * Zend_Session_Validator_Abstract
 */
require_once 'Zend/Session/Validator/Abstract.php';

/**
 * Zend_Session_Validator_HttpUserAgent
 * 
 * @category Zend
 * @package Zend_Session
 * @subpackage Validators
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Session_Validator_HttpUserAgent extends Zend_Session_Validator_Abstract 
{
    
    /**
     * Setup() - this method will get the current user agent and store it in the session
     * as 'valid data'
     *
     */
    public function setup()
    {
        $this->setValidData( (isset($_SERVER['HTTP_USER_AGENT'])
		    ? $_SERVER['HTTP_USER_AGENT'] : null) );
    }
    
    /**
     * Validate() - this method will determine if the current user agent matches the 
     * user agent we stored when we initialized this variable.
     *
     * @return bool
     */
    public function validate()
    {
        $current_browser = (isset($_SERVER['HTTP_USER_AGENT'])
		    ? $_SERVER['HTTP_USER_AGENT'] : null);
        
        if ($current_browser === $this->getValidData()) {
            return true;
        } else {
            return false;
        }
    }
    
}
