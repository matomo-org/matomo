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
 * @version    $Id: Ini.php 23775 2011-03-01 17:25:24Z ralph $
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
class Zend_Config_Writer_Ini extends Zend_Config_Writer_FileAbstract
{
    /**
     * String that separates nesting levels of configuration data identifiers
     *
     * @var string
     */
    protected $_nestSeparator = '.';

    /**
     * If true the ini string is rendered in the global namespace without sections.
     *
     * @var bool
     */
    protected $_renderWithoutSections = false;

    /**
     * Set the nest separator
     *
     * @param  string $filename
     * @return Zend_Config_Writer_Ini
     */
    public function setNestSeparator($separator)
    {
        $this->_nestSeparator = $separator;

        return $this;
    }

    /**
     * Set if rendering should occour without sections or not.
     *
     * If set to true, the INI file is rendered without sections completely
     * into the global namespace of the INI file.
     *
     * @param  bool $withoutSections
     * @return Zend_Config_Writer_Ini
     */
    public function setRenderWithoutSections($withoutSections=true)
    {
        $this->_renderWithoutSections = (bool)$withoutSections;
        return $this;
    }

    /**
     * Render a Zend_Config into a INI config string.
     *
     * @since 1.10
     * @return string
     */
    public function render()
    {
        $iniString   = '';
        $extends     = $this->_config->getExtends();
        $sectionName = $this->_config->getSectionName();

        if($this->_renderWithoutSections == true) {
            $iniString .= $this->_addBranch($this->_config);
        } else if (is_string($sectionName)) {
            $iniString .= '[' . $sectionName . ']' . "\n"
                       .  $this->_addBranch($this->_config)
                       .  "\n";
        } else {
            $config = $this->_sortRootElements($this->_config);
            foreach ($config as $sectionName => $data) {
                if (!($data instanceof Zend_Config)) {
                    $iniString .= $sectionName
                               .  ' = '
                               .  $this->_prepareValue($data)
                               .  "\n";
                } else {
                    if (isset($extends[$sectionName])) {
                        $sectionName .= ' : ' . $extends[$sectionName];
                    }

                    $iniString .= '[' . $sectionName . ']' . "\n"
                               .  $this->_addBranch($data)
                               .  "\n";
                }
            }
        }

        return $iniString;
    }

    /**
     * Add a branch to an INI string recursively
     *
     * @param  Zend_Config $config
     * @return void
     */
    protected function _addBranch(Zend_Config $config, $parents = array())
    {
        $iniString = '';

        foreach ($config as $key => $value) {
            $group = array_merge($parents, array($key));

            if ($value instanceof Zend_Config) {
                $iniString .= $this->_addBranch($value, $group);
            } else {
                $iniString .= implode($this->_nestSeparator, $group)
                           .  ' = '
                           .  $this->_prepareValue($value)
                           .  "\n";
            }
        }

        return $iniString;
    }

    /**
     * Prepare a value for INI
     *
     * @param  mixed $value
     * @return string
     */
    protected function _prepareValue($value)
    {
        if (is_integer($value) || is_float($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return ($value ? 'true' : 'false');
        } elseif (strpos($value, '"') === false) {
            return '"' . $value .  '"';
        } else {
            /** @see Zend_Config_Exception */
            // require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception('Value can not contain double quotes "');
        }
    }

    /**
     * Root elements that are not assigned to any section needs to be
     * on the top of config.
     *
     * @see    http://framework.zend.com/issues/browse/ZF-6289
     * @param  Zend_Config
     * @return Zend_Config
     */
    protected function _sortRootElements(Zend_Config $config)
    {
        $configArray = $config->toArray();
        $sections = array();

        // remove sections from config array
        foreach ($configArray as $key => $value) {
            if (is_array($value)) {
                $sections[$key] = $value;
                unset($configArray[$key]);
            }
        }

        // readd sections to the end
        foreach ($sections as $key => $value) {
            $configArray[$key] = $value;
        }

        return new Zend_Config($configArray);
    }
}
