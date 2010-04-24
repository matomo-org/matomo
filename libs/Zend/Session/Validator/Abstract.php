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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 * @since      Preview Release 0.2
 */

/**
 * @see Zend_Session_Validator_Interface
 */
// require_once 'Zend/Session/Validator/Interface.php';

/**
 * Zend_Session_Validator_Abstract
 *
 * @category   Zend
 * @package    Zend_Session
 * @subpackage Validator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Session_Validator_Abstract implements Zend_Session_Validator_Interface
{

    /**
     * SetValidData() - This method should be used to store the environment variables that
     * will be needed in order to validate the session later in the validate() method.
     * These values are stored in the session in the __ZF namespace, in an array named VALID
     *
     * @param  mixed $data
     * @return void
     */
    protected function setValidData($data)
    {
        $validatorName = get_class($this);

        $_SESSION['__ZF']['VALID'][$validatorName] = $data;
    }


    /**
     * GetValidData() - This method should be used to retrieve the environment variables that
     * will be needed to 'validate' a session.
     *
     * @return mixed
     */
    protected function getValidData()
    {
        $validatorName = get_class($this);

        return $_SESSION['__ZF']['VALID'][$validatorName];
    }

}
