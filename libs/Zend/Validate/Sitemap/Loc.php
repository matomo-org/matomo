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
 * @subpackage Sitemap
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Loc.php 22697 2010-07-26 21:14:47Z alexander $
 */

/**
 * @see Zend_Validate_Abstract
 */
// require_once 'Zend/Validate/Abstract.php';

/**
 * @see Zend_Uri
 */
// require_once 'Zend/Uri.php';

/**
 * Validates whether a given value is valid as a sitemap <loc> value
 *
 * @link       http://www.sitemaps.org/protocol.php Sitemaps XML format
 *
 * @category   Zend
 * @package    Zend_Validate
 * @subpackage Sitemap
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Sitemap_Loc extends Zend_Validate_Abstract
{
    /**
     * Validation key for not valid
     *
     */
    const NOT_VALID = 'sitemapLocNotValid';
    const INVALID   = 'sitemapLocInvalid';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_VALID => "'%value%' is no valid sitemap location",
        self::INVALID   => "Invalid type given, the value should be a string",
    );

    /**
     * Validates if a string is valid as a sitemap location
     *
     * @link http://www.sitemaps.org/protocol.php#locdef <loc>
     *
     * @param  string  $value  value to validate
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);
        $result = Zend_Uri::check($value);
        if ($result !== true) {
            $this->_error(self::NOT_VALID);
            return false;
        }

        return true;
    }
}
