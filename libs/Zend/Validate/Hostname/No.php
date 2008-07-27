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
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: No.php 8064 2008-02-16 10:58:39Z thomas $
 */


/**
 * @see Zend_Validate_Hostname_Interface
 */
require_once 'Zend/Validate/Hostname/Interface.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Hostname_No implements Zend_Validate_Hostname_Interface
{

    /**
     * Returns UTF-8 characters allowed in DNS hostnames for the specified Top-Level-Domain
     *
     * @see http://www.norid.no/domeneregistrering/idn/idn_nyetegn.en.html Norway (.NO)
     * @return string
     */
    static function getCharacters()
    {
        return  '\x00E1\x00E0\x00E4\x010D\x00E7\x0111\x00E9\x00E8\x00EA\x\x014B' .
                '\x0144\x00F1\x00F3\x00F2\x00F4\x00F6\x0161\x0167\x00FC\x017E\x00E6' .
                '\x00F8\x00E5';
    }

}
