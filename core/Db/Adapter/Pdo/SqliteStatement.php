<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Adapter\Pdo;

use PDOException;
use PDOStatement;
use Piwik\Db\Sqlite\Connection;
use Zend_Db_Statement_Exception;
use Zend_Db_Statement_Pdo;

/**
 * Statement of the SQLite adapter, which binds parameters with the types of their PHP values.
 *
 * @see Connection::bindValues()
 * @internal
 */
class SqliteStatement extends Zend_Db_Statement_Pdo
{
    public function _execute(?array $params = null) // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        if (!$this->_stmt instanceof PDOStatement) {
            throw new Zend_Db_Statement_Exception('SQLite: the statement is not prepared.');
        }

        try {
            if ($params !== null) {
                Connection::bindValues($this->_stmt, $params);
            }

            return $this->_stmt->execute();
        } catch (PDOException $e) {
            throw new Zend_Db_Statement_Exception($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
