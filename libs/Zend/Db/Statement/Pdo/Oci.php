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
 * @package    Zend_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Oci.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Db_Statement_Pdo
 */
require_once 'Zend/Db/Statement/Pdo.php';

/**
 * Proxy class to wrap a PDOStatement object for IBM Databases.
 * Matches the interface of PDOStatement.  All methods simply proxy to the
 * matching method in PDOStatement.  PDOExceptions thrown by PDOStatement
 * are re-thrown as Zend_Db_Statement_Exception.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Statement_Pdo_Oci extends Zend_Db_Statement_Pdo
{

    /**
    * Returns an array containing all of the result set rows.
    *
    * Behaves like parent, but if limit()
    * is used, the final result removes the extra column
    * 'zend_db_rownum'
    *
    * @param int $style OPTIONAL Fetch mode.
    * @param int $col   OPTIONAL Column number, if fetch mode is by column.
    * @return array Collection of rows, each in a format by the fetch mode.
    * @throws Zend_Db_Statement_Exception
    */
    public function fetchAll($style = null, $col = null)
    {
        $data = parent::fetchAll($style, $col);
        $results = array();
        $remove = $this->_adapter->foldCase('zend_db_rownum');

        foreach ($data as $row) {
            if (is_array($row) && array_key_exists($remove, $row)) {
                unset($row[$remove]);
            }
            $results[] = $row;
        }
        return $results;
    }
}