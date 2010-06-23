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
class Zend_Validate_Barcode_Issn extends Zend_Validate_Barcode_AdapterAbstract
{
    /**
     * Allowed barcode lengths
     * @var integer
     */
    protected $_length = array(8, 13);

    /**
     * Allowed barcode characters
     * @var string
     */
    protected $_characters = '0123456789X';

    /**
     * Checksum function
     * @var string
     */
    protected $_checksum = '_gtin';

    /**
     * Allows X on length of 8 chars
     *
     * @param  string $value The barcode to check for allowed characters
     * @return boolean
     */
    public function checkChars($value)
    {
        if (strlen($value) != 8) {
            if (strpos($value, 'X') !== false) {
                return false;
            }
        }

        return parent::checkChars($value);
    }

    /**
     * Validates the checksum
     *
     * @param  string $value The barcode to check the checksum for
     * @return boolean
     */
    public function checksum($value)
    {
        if (strlen($value) == 8) {
            $this->_checksum = '_issn';
        } else {
            $this->_checksum = '_gtin';
        }

        return parent::checksum($value);
    }

    /**
     * Validates the checksum ()
     * ISSN implementation (reversed mod11)
     *
     * @param  string $value The barcode to validate
     * @return boolean
     */
    protected function _issn($value)
    {
        $checksum = substr($value, -1, 1);
        $values   = str_split(substr($value, 0, -1));
        $check    = 0;
        $multi    = 8;
        foreach($values as $token) {
            if ($token == 'X') {
                $token = 10;
            }

            $check += ($token * $multi);
            --$multi;
        }

        $check %= 11;
        $check  = 11 - $check;
        if ($check == $checksum) {
            return true;
        } else if (($check == 10) && ($checksum == 'X')) {
            return true;
        }

        return false;
    }
}
