<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Config;
use Piwik\Development;
use Piwik\Plugin\Manager;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Informational about the Matomo configuration
 */
class ConfigInformational implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        $results = [];

        if (SettingsPiwik::isMatomoInstalled()) {
            $results[] = DiagnosticResult::informationalResult('Browser Segment Archiving Enabled', Rules::isBrowserArchivingAvailableForSegments());
            $results[] = DiagnosticResult::informationalResult('Development Mode Enabled', Development::isEnabled());
            $results[] = DiagnosticResult::informationalResult('Internet Enabled', SettingsPiwik::isInternetEnabled());
            $results[] = DiagnosticResult::informationalResult('Multi Server Environment', SettingsPiwik::isMultiServerEnvironment());
            $results[] = DiagnosticResult::informationalResult('Auto Update Enabled', SettingsPiwik::isAutoUpdateEnabled());
            $results[] = DiagnosticResult::informationalResult('Custom User Path', PIWIK_USER_PATH != PIWIK_DOCUMENT_ROOT);
            $results[] = DiagnosticResult::informationalResult('Custom Include Path', PIWIK_INCLUDE_PATH != PIWIK_DOCUMENT_ROOT);
            $results[] = DiagnosticResult::informationalResult('Release Channel', Config::getInstance()->General['release_channel']);

            $pluginsActivated = array();
            $pluginsDeactivated = array();
            $pluginsInvalid = array();
            $plugins = Manager::getInstance()->loadAllPluginsAndGetTheirInfo();
            foreach ($plugins as $pluginName => $plugin) {
                $string = $pluginName;
                if (
                    !empty($plugin['info']['version'])
                    && !empty($plugin['uninstallable'])
                    && (!defined('PIWIK_TEST_MODE') || !PIWIK_TEST_MODE)
                ) {
                    // we only want to show versions for plugins not shipped with core
                    // in tests we don't show version numbers to not always needing to update the screenshot
                    $string .= ' ' . $plugin['info']['version'];
                }
                if (!empty($plugin['activated'])) {
                    $pluginsActivated[] = $string;
                } else {
                    $pluginsDeactivated[] = $string;
                }
                if (!empty($plugin['invalid'])) {
                    $pluginsInvalid[] = $string;
                }
            }

            $results[] = DiagnosticResult::informationalResult('Plugins Activated', implode(', ', $pluginsActivated));
            $results[] = DiagnosticResult::informationalResult('Plugins Deactivated', implode(', ', $pluginsDeactivated));
            $results[] = DiagnosticResult::informationalResult('Plugins Invalid', implode(', ', $pluginsInvalid));

            if (!empty($GLOBALS['MATOMO_PLUGIN_DIRS'])) {
                $results[] = DiagnosticResult::informationalResult('Custom Plugins Directories', json_encode($GLOBALS['MATOMO_PLUGIN_DIRS']));
            }
        }

        return $results;
    }
}
