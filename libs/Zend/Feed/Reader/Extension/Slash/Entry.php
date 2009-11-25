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
 * @version    $Id: Entry.php 18951 2009-11-12 16:26:19Z alexander $
 */

/**
 * @see Zend_Feed_Reader
 */
require_once 'Zend/Feed/Reader.php';

/**
 * @see Zend_Feed_Reader_Extension_EntryAbstract
 */
require_once 'Zend/Feed/Reader/Extension/EntryAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_Extension_Slash_Entry
    extends Zend_Feed_Reader_Extension_EntryAbstract
{
    /**
     * Get the entry section
     *
     * @return string|null
     */
    public function getSection()
    {
        return $this->_getData('section');
    }

    /**
     * Get the entry department
     *
     * @return string|null
     */
    public function getDepartment()
    {
        return $this->_getData('department');
    }

    /**
     * Get the entry hit_parade
     *
     * @return array
     */
    public function getHitParade()
    {
        $name = 'hit_parade';

        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }

        $stringParade = $this->_getData($name);
        $hitParade    = array();

        if (!empty($stringParade)) {
            $stringParade = explode(',', $stringParade);

            foreach ($stringParade as $hit)
                $hitParade[] = $hit + 0; //cast to integer
        }

        $this->_data[$name] = $hitParade;
        return $hitParade;
    }

    /**
     * Get the entry comments
     *
     * @return int
     */
    public function getCommentCount()
    {
        $name = 'comments';

        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }

        $comments = $this->_getData($name, 'string');

        if (!$comments) {
            $this->_data[$name] = null;
            return $this->_data[$name];
        }

        return $comments;
    }

    /**
     * Get the entry data specified by name
     * @param string $name
     * @param string $type
     *
     * @return mixed|null
     */
    protected function _getData($name, $type = 'string')
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }

        $data = $this->_xpath->evaluate($type . '(' . $this->getXpathPrefix() . '/slash10:' . $name . ')');

        if (!$data) {
            $data = null;
        }

        $this->_data[$name] = $data;

        return $data;
    }

    /**
     * Register Slash namespaces
     *
     * @return void
     */
    protected function _registerNamespaces()
    {
        $this->_xpath->registerNamespace('slash10', 'http://purl.org/rss/1.0/modules/slash/');
    }
}
