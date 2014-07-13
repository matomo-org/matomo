<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LeftMenu;

class LeftMenu extends \Piwik\Plugin
{
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles' => array('function' => 'getStylesheetFiles', 'after' => true),
            'Template.bodyClass' => 'addClassToBody'
        );
    }

    public function addClassToBody(&$str, $layout)
    {
        if (API::getInstance()->isEnabled() && 'dashboard' === $layout) {
            $str .= ' leftMenuPlugin';
        }
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/LeftMenu/stylesheets/theme.less";
    }

}

