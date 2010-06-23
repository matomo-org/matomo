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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Category.php 20954 2010-02-06 17:56:27Z padraic $
 */
 
/**
 * @see Zend_Feed_Reader_Collection_CollectionAbstract
 */
// require_once 'Zend/Feed/Reader/Collection/CollectionAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_Collection_Category
extends Zend_Feed_Reader_Collection_CollectionAbstract
{

    /**
     * Return a simple array of the most relevant slice of
     * the collection values. For example, feed categories contain
     * the category name, domain/URI, and other data. This method would
     * merely return the most useful data - i.e. the category names.
     *
     * @return array
     */
    public function getValues() {
        $categories = array();
        foreach ($this->getIterator() as $element) {
            if (isset($element['label']) && !empty($element['label'])) {
                $categories[] = $element['label'];
            } else {
                $categories[] = $element['term'];
            }
        }
        return array_unique($categories);
    }

}
