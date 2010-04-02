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
 * @version    $Id: Lastmod.php 21365 2010-03-07 09:38:41Z thomas $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Validates whether a given value is valid as a sitemap <lastmod> value
 *
 * @link       http://www.sitemaps.org/protocol.php Sitemaps XML format
 *
 * @category   Zend
 * @package    Zend_Validate
 * @subpackage Sitemap
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Sitemap_Lastmod extends Zend_Validate_Abstract
{
    /**
     * Regular expression to use when validating
     *
     */
    const LASTMOD_REGEX = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])(T([0-1][0-9]|2[0-3])(:[0-5][0-9])(:[0-5][0-9])?(\\+|-)([0-1][0-9]|2[0-3]):[0-5][0-9])?$/';

    /**
     * Validation key for not valid
     *
     */
    const NOT_VALID = 'sitemapLastmodNotValid';
    const INVALID   = 'sitemapLastmodInvalid';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_VALID => "'%value%' is no valid sitemap lastmod",
        self::INVALID   => "Invalid type given, the value should be a string",
    );

    /**
     * Validates if a string is valid as a sitemap lastmod
     *
     * @link http://www.sitemaps.org/protocol.php#lastmoddef <lastmod>
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
        $result = @preg_match(self::LASTMOD_REGEX, $value);
        if ($result != 1) {
            $this->_error(self::NOT_VALID);
            return false;
        }

        return true;
    }
}
