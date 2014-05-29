<?php
/**
 * Piwik - Open source web analytics
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

    public function addClassToBody($str)
    {
        if (API::getInstance()->isEnabled()) {
            $str .= ' leftMenuPlugin';
        }
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/LeftMenu/stylesheets/theme.less";
    }

}

