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
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Entry.php 16971 2009-07-22 18:05:45Z mikaelkael $
 */

/**
 * @see Zend_Feed_Reader_Extension_EntryAbstract
 */
require_once 'Zend/Feed/Reader/Extension/EntryAbstract.php';

/**
 * @see Zend_Feed_Reader_Extension_CreativeCommons_Feed
 */
require_once 'Zend/Feed/Reader/Extension/CreativeCommons/Feed.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_Extension_CreativeCommons_Entry extends Zend_Feed_Reader_Extension_EntryAbstract
{
    /**
     * Get the entry license
     *
     * @return string|null
     */
    public function getLicense($index = 0)
    {
        $licenses = $this->getLicenses();

        if (isset($licenses[$index])) {
            return $licenses[$index];
        }

        return null;
    }

    /**
     * Get the entry licenses
     *
     * @return array
     */
    public function getLicenses()
    {
        $name = 'licenses';
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }

        $licenses = array();
        $list = $this->_xpath->evaluate($this->getXpathPrefix() . '//cc:license');

        if ($list->length) {
            foreach ($list as $license) {
                    $licenses[] = $license->nodeValue;
            }

            $licenses = array_unique($licenses);
        } else {
            $cc = new Zend_Feed_Reader_Extension_CreativeCommons_Feed(
                $this->_domDocument, $this->_data['type'], $this->_xpath
            );
            $licenses = $cc->getLicenses();
        }

        $this->_data[$name] = $licenses;

        return $this->_data[$name];
    }

    /**
     * Register Creative Commons namespaces
     *
     */
    protected function _registerNamespaces()
    {
        $this->_xpath->registerNamespace('cc', 'http://backend.userland.com/creativeCommonsRssModule');
    }
}
