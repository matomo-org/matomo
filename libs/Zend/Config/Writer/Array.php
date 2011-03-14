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
 * @package    Zend_Config
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Array.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Config_Writer
 */
// require_once 'Zend/Config/Writer/FileAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Config
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Config_Writer_Array extends Zend_Config_Writer_FileAbstract
{
    /**
     * Render a Zend_Config into a PHP Array config string.
     *
     * @since 1.10
     * @return string
     */
    public function render()
    {
        $data        = $this->_config->toArray();
        $sectionName = $this->_config->getSectionName();

        if (is_string($sectionName)) {
            $data = array($sectionName => $data);
        }

        $arrayString = "<?php\n"
                     . "return " . var_export($data, true) . ";\n";

        return $arrayString;
    }
}
