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
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Table.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Db_Table_Abstract
 */
// require_once 'Zend/Db/Table/Abstract.php';

/**
 * @see Zend_Db_Table_Definition
 */
// require_once 'Zend/Db/Table/Definition.php';

/**
 * Class for SQL table interface.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Table extends Zend_Db_Table_Abstract
{

    /**
     * __construct() - For concrete implementation of Zend_Db_Table
     *
     * @param string|array $config string can reference a Zend_Registry key for a db adapter
     *                             OR it can reference the name of a table
     * @param array|Zend_Db_Table_Definition $definition
     */
    public function __construct($config = array(), $definition = null)
    {
        if ($definition !== null && is_array($definition)) {
            $definition = new Zend_Db_Table_Definition($definition);
        }

        if (is_string($config)) {
            if (Zend_Registry::isRegistered($config)) {
                trigger_error(__CLASS__ . '::' . __METHOD__ . '(\'registryName\') is not valid usage of Zend_Db_Table, '
                    . 'try extending Zend_Db_Table_Abstract in your extending classes.',
                    E_USER_NOTICE
                    );
                $config = array(self::ADAPTER => $config);
            } else {
                // process this as table with or without a definition
                if ($definition instanceof Zend_Db_Table_Definition
                    && $definition->hasTableConfig($config)) {
                    // this will have DEFINITION_CONFIG_NAME & DEFINITION
                    $config = $definition->getTableConfig($config);
                } else {
                    $config = array(self::NAME => $config);
                }
            }
        }

        parent::__construct($config);
    }
}
