<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\GetLocalSiteTimeDate;

class GetLocalSiteTimeDate extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
	    'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'CronArchive.getArchivingAPIMethodForPlugin' => 'getArchivingAPIMethodForPlugin',
        ];
    }

    // support archiving just this plugin via core:archive
    public function getArchivingAPIMethodForPlugin(&$method, $plugin)
    {
        if ($plugin == 'GetLocalSiteTimeDate') {
            $method = 'GetLocalSiteTimeDate.getExampleArchivedMetric';
        }
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/GetLocalSiteTimeDate/javascripts/localDateTime.js";
    }

}
