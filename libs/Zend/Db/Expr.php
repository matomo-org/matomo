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
 * @subpackage Expr
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Expr.php 23775 2011-03-01 17:25:24Z ralph $
 */


/**
 * Class for SQL SELECT fragments.
 *
 * This class simply holds a string, so that fragments of SQL statements can be
 * distinguished from identifiers and values that should be implicitly quoted
 * when interpolated into SQL statements.
 *
 * For example, when specifying a primary key value when inserting into a new
 * row, some RDBMS brands may require you to use an expression to generate the
 * new value of a sequence.  If this expression is treated as an identifier,
 * it will be quoted and the expression will not be evaluated.  Another example
 * is that you can use Zend_Db_Expr in the Zend_Db_Select::order() method to
 * order by an expression instead of simply a column name.
 *
 * The way this works is that in each context in which a column name can be
 * specified to methods of Zend_Db classes, if the value is an instance of
 * Zend_Db_Expr instead of a plain string, then the expression is not quoted.
 * If it is a plain string, it is assumed to be a plain column name.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Expr
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Expr
{
    /**
     * Storage for the SQL expression.
     *
     * @var string
     */
    protected $_expression;

    /**
     * Instantiate an expression, which is just a string stored as
     * an instance member variable.
     *
     * @param string $expression The string containing a SQL expression.
     */
    public function __construct($expression)
    {
        $this->_expression = (string) $expression;
    }

    /**
     * @return string The string of the SQL expression stored in this object.
     */
    public function __toString()
    {
        return $this->_expression;
    }

}
