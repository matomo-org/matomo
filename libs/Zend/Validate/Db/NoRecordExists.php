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
 * @version    $Id: NoRecordExists.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Validate_Db_Abstract
 */
// require_once 'Zend/Validate/Db/Abstract.php';

/**
 * Confirms a record does not exist in a table.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @uses       Zend_Validate_Db_Abstract
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Db_NoRecordExists extends Zend_Validate_Db_Abstract
{
    public function isValid($value)
    {
        $valid = true;
        $this->_setValue($value);

        $result = $this->_query($value);
        if ($result) {
            $valid = false;
            $this->_error(self::ERROR_RECORD_FOUND);
        }

        return $valid;
    }
}
