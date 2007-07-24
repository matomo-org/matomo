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
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */ 

/**
 * Zend_Server_Reflection_Exception
 */
require_once 'Zend/Server/Reflection/Exception.php';

/**
 * Zend_Server_Reflection_ReturnValue
 */
require_once 'Zend/Server/Reflection/ReturnValue.php';

/**
 * Zend_Server_Reflection_Parameter
 */
require_once 'Zend/Server/Reflection/Parameter.php';

/**
 * Method/Function prototypes
 *
 * Contains accessors for the return value and all method arguments.
 * 
 * @category   Zend
 * @package    Zend_Server
 * @subpackage Reflection
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version $Id: Prototype.php 2815 2007-01-16 01:42:33Z bkarwin $
 */
class Zend_Server_Reflection_Prototype
{
    /**
     * Constructor
     * 
     * @param Zend_Server_Reflection_ReturnValue $return 
     * @param array $params 
     * @return void
     */
    public function __construct(Zend_Server_Reflection_ReturnValue $return, $params = null)
    {
        $this->_return = $return;

        if (!is_array($params) && (null !== $params)) {
            throw new Zend_Server_Reflection_Exception('Invalid parameters');
        }

        if (is_array($params)) {
            foreach ($params as $param) {
                if (!$param instanceof Zend_Server_Reflection_Parameter) {
                    throw new Zend_Server_Reflection_Exception('One or more params are invalid');
                }
            }
        }

        $this->_params = $params;
    }

    /**
     * Retrieve return type
     * 
     * @return string
     */
    public function getReturnType()
    {
        return $this->_return->getType();
    }

    /**
     * Retrieve the return value object
     * 
     * @access public
     * @return Zend_Server_Reflection_ReturnValue
     */
    public function getReturnValue()
    {
        return $this->_return;
    }

    /**
     * Retrieve method parameters
     * 
     * @return array Array of {@link Zend_Server_Reflection_Parameter}s
     */
    public function getParameters()
    {
        return $this->_params;
    }
}
