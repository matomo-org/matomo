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
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Interface.php 23775 2011-03-01 17:25:24Z ralph $
 * @since      Preview Release 0.2
 */

/**
 * Zend_Session_Validator_Interface
 *
 * @category   Zend
 * @package    Zend_Session
 * @subpackage Validator
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Session_Validator_Interface
{

    /**
     * Setup() - this method will store the environment variables
     * necessary to be able to validate against in future requests.
     *
     * @return void
     */
    public function setup();

    /**
     * Validate() - this method will be called at the beginning of
     * every session to determine if the current environment matches
     * that which was store in the setup() procedure.
     *
     * @return boolean
     */
    public function validate();

}
