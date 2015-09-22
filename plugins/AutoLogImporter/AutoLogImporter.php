<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\AutoLogImporter;

class AutoLogImporter extends \Piwik\Plugin
{
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/AutoLogImporter/stylesheets/status.less";
    }

    public function install()
    {
        $dao = new Dao();
        $dao->install();
    }

    public function uninstall()
    {
        $dao = new Dao();
        $dao->uninstall();
    }
}
