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
 * @version    $Id: Priority.php 20358 2010-01-17 19:03:49Z thomas $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Validates whether a given value is valid as a sitemap <priority> value
 *
 * @link       http://www.sitemaps.org/protocol.php Sitemaps XML format
 *
 * @category   Zend
 * @package    Zend_Validate
 * @subpackage Sitemap
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Sitemap_Priority extends Zend_Validate_Abstract
{
    /**
     * Validation key for not valid
     *
     */
    const NOT_VALID = 'invalidSitemapPriority';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_VALID => "'%value%' is no valid sitemap priority",
    );

    /**
     * Validates if a string is valid as a sitemap priority
     *
     * @link http://www.sitemaps.org/protocol.php#prioritydef <priority>
     *
     * @param  string  $value  value to validate
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        if (!is_numeric($value)) {
            return false;
        }

        $value = (float)$value;
        return $value >= 0 && $value <= 1;
    }
}
