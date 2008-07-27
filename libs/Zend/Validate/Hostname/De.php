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
 * @version    $Id: De.php 8064 2008-02-16 10:58:39Z thomas $
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
class Zend_Validate_Hostname_De implements Zend_Validate_Hostname_Interface
{

    /**
     * Returns UTF-8 characters allowed in DNS hostnames for the specified Top-Level-Domain
     *
     * @see http://www.denic.de/en/domains/idns/liste.html Germany (.DE) alllowed characters
     * @return string
     */
    static function getCharacters()
    {
        return  '\x{00E1}\x{00E0}\x{0103}\x{00E2}\x{00E5}\x{00E4}\x{00E3}\x{0105}\x{0101}\x{00E6}\x{0107}' .
                '\x{0109}\x{010D}\x{010B}\x{00E7}\x{010F}\x{0111}\x{00E9}\x{00E8}\x{0115}\x{00EA}\x{011B}' .
                '\x{00EB}\x{0117}\x{0119}\x{0113}\x{011F}\x{011D}\x{0121}\x{0123}\x{0125}\x{0127}\x{00ED}' .
                '\x{00EC}\x{012D}\x{00EE}\x{00EF}\x{0129}\x{012F}\x{012B}\x{0131}\x{0135}\x{0137}\x{013A}' .
                '\x{013E}\x{013C}\x{0142}\x{0144}\x{0148}\x{00F1}\x{0146}\x{014B}\x{00F3}\x{00F2}\x{014F}' .
                '\x{00F4}\x{00F6}\x{0151}\x{00F5}\x{00F8}\x{014D}\x{0153}\x{0138}\x{0155}\x{0159}\x{0157}' .
                '\x{015B}\x{015D}\x{0161}\x{015F}\x{0165}\x{0163}\x{0167}\x{00FA}\x{00F9}\x{016D}\x{00FB}' .
                '\x{016F}\x{00FC}\x{0171}\x{0169}\x{0173}\x{016B}\x{0175}\x{00FD}\x{0177}\x{00FF}\x{017A}' .
                '\x{017E}\x{017C}\x{00F0}\x{00FE}';
    }

}