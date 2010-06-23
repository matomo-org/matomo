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
 * @version    $Id:$
 */

/**
 * @see Zend_Validate_Barcode_AdapterAbstract
 */
// require_once 'Zend/Validate/Barcode/AdapterAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Barcode_Royalmail extends Zend_Validate_Barcode_AdapterAbstract
{
    /**
     * Allowed barcode lengths
     * @var integer
     */
    protected $_length = -1;

    /**
     * Allowed barcode characters
     * @var string
     */
    protected $_characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    protected $_rows = array(
        '0' => 1, '1' => 1, '2' => 1, '3' => 1, '4' => 1, '5' => 1,
        '6' => 2, '7' => 2, '8' => 2, '9' => 2, 'A' => 2, 'B' => 2,
        'C' => 3, 'D' => 3, 'E' => 3, 'F' => 3, 'G' => 3, 'H' => 3,
        'I' => 4, 'J' => 4, 'K' => 4, 'L' => 4, 'M' => 4, 'N' => 4,
        'O' => 5, 'P' => 5, 'Q' => 5, 'R' => 5, 'S' => 5, 'T' => 5,
        'U' => 0, 'V' => 0, 'W' => 0, 'X' => 0, 'Y' => 0, 'Z' => 0,
     );

    protected $_columns = array(
        '0' => 1, '1' => 2, '2' => 3, '3' => 4, '4' => 5, '5' => 0,
        '6' => 1, '7' => 2, '8' => 3, '9' => 4, 'A' => 5, 'B' => 0,
        'C' => 1, 'D' => 2, 'E' => 3, 'F' => 4, 'G' => 5, 'H' => 0,
        'I' => 1, 'J' => 2, 'K' => 3, 'L' => 4, 'M' => 5, 'N' => 0,
        'O' => 1, 'P' => 2, 'Q' => 3, 'R' => 4, 'S' => 5, 'T' => 0,
        'U' => 1, 'V' => 2, 'W' => 3, 'X' => 4, 'Y' => 5, 'Z' => 0,
    );

    /**
     * Checksum function
     * @var string
     */
    protected $_checksum = '_royalmail';

    /**
     * Validates the checksum ()
     *
     * @param  string $value The barcode to validate
     * @return boolean
     */
    protected function _royalmail($value)
    {
        $checksum = substr($value, -1, 1);
        $values   = str_split(substr($value, 0, -1));
        $rowvalue = 0;
        $colvalue = 0;
        foreach($values as $row) {
            $rowvalue += $this->_rows[$row];
            $colvalue += $this->_columns[$row];
        }

        $rowvalue %= 6;
        $colvalue %= 6;

        $rowchkvalue = array_keys($this->_rows, $rowvalue);
        $colchkvalue = array_keys($this->_columns, $colvalue);
        $chkvalue    = current(array_intersect($rowchkvalue, $colchkvalue));
        if ($chkvalue == $checksum) {
            return true;
        }

        return false;
    }

    /**
     * Allows start and stop tag within checked chars
     *
     * @param  string $value The barcode to check for allowed characters
     * @return boolean
     */
    public function checkChars($value)
    {
        if ($value[0] == '(') {
            $value = substr($value, 1);

            if ($value[strlen($value) - 1] == ')') {
                $value = substr($value, 0, -1);
            } else {
                return false;
            }
        }

        return parent::checkChars($value);
    }
}
