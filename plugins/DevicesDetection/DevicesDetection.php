<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection;

require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/functions.php';

class DevicesDetection extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
        ];
    }

    public function getClientSideTranslationKeys(&$translations)
    {
        $translations[] = 'DevicesDetection_UserAgent';
        $translations[] = 'General_Refresh';
        $translations[] = 'DevicesDetection_BotDetected';
        $translations[] = 'DevicesDetection_ColumnOperatingSystem';
        $translations[] = 'Mobile_ShowAll';
        $translations[] = 'CorePluginsAdmin_Version';
        $translations[] = 'DevicesDetection_OperatingSystemFamily';
        $translations[] = 'DevicesDetection_ColumnBrowser';
        $translations[] = 'DevicesDetection_BrowserFamily';
        $translations[] = 'DevicesDetection_Device';
        $translations[] = 'DevicesDetection_dataTableLabelTypes';
        $translations[] = 'DevicesDetection_dataTableLabelBrands';
        $translations[] = 'DevicesDetection_dataTableLabelModels';
        $translations[] = 'General_Close';
        $translations[] = 'DevicesDetection_DeviceDetection';
        $translations[] = 'DevicesDetection_ClientHints';
        $translations[] = 'DevicesDetection_ConsiderClientHints';
        $translations[] = 'DevicesDetection_ClientHintsNotSupported';
    }

    public function getStylesheetFiles(&$files)
    {
        $files[] = 'plugins/DevicesDetection/vue/src/DetectionPage/DetectionPage.less';
    }
}
