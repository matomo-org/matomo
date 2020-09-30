<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\GeoIp2;

use Piwik\CliMulti;
use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\Installation\FormDefaultSettings;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Scheduler\Scheduler;

/**
 *
 */
class GeoIp2 extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'         => 'getJsFiles',
            'Translate.getClientSideTranslationKeys'  => 'getClientSideTranslationKeys',
            'Installation.defaultSettingsForm.init'   => 'installationFormInit',
            'Installation.defaultSettingsForm.submit' => 'installationFormSubmit',
        );
    }

    public function isTrackerPlugin()
    {
        return true;
    }

    public function deactivate()
    {
        // switch to default provider if GeoIP2 provider was in use
        if (LocationProvider::getCurrentProvider() instanceof \Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2) {
            LocationProvider::setCurrentProvider(LocationProvider\DefaultProvider::ID);
        }
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/GeoIp2/angularjs/geoip2-updater/geoip2-updater.controller.js";
        $jsFiles[] = "plugins/GeoIp2/angularjs/geoip2-updater/geoip2-updater.directive.js";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = "GeoIp2_FatalErrorDuringDownload";
        $translationKeys[] = "GeoIp2_SetupAutomaticUpdatesOfGeoIP";
        $translationKeys[] = "General_Done";
        $translationKeys[] = "General_Save";
        $translationKeys[] = "General_Continue";
    }

    /**
     * Customize the Installation "default settings" form.
     *
     * @param FormDefaultSettings $form
     */
    public function installationFormInit(FormDefaultSettings $form)
    {
        $form->addElement('checkbox', 'setup_geoip2', null,
            [
                'content' => '<div class="form-help">' . Piwik::translate('GeoIp2_AutomaticSetupDescription', ['<a rel="noreferrer noopener" target="_blank" href="https://db-ip.com/db/lite.php?refid=mtm">','</a>']) . '</div> &nbsp;&nbsp;' . Piwik::translate('GeoIp2_AutomaticSetup')
            ]
        );

        // default values
        $form->addDataSource(new \HTML_QuickForm2_DataSource_Array([
            'setup_geoip2' => true,
        ]));
    }

    /**
     * Process the submit on the Installation "default settings" form.
     *
     * @param FormDefaultSettings $form
     */
    public function installationFormSubmit(FormDefaultSettings $form)
    {
        $setupGeoIp2 = (bool) $form->getSubmitValue('setup_geoip2');

        if ($setupGeoIp2) {
            Option::set(GeoIP2AutoUpdater::AUTO_SETUP_OPTION_NAME, true);
            GeoIP2AutoUpdater::setUpdaterOptions([
                'loc' => \Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2::getDbIpLiteUrl(),
                'period' => GeoIP2AutoUpdater::SCHEDULE_PERIOD_MONTHLY
            ]);

            $cliMulti = new CliMulti();

            // directly trigger the update task if possible
            // otherwise ensure it will be run soonish as scheduled task
            if ($cliMulti->supportsAsync()) {
                $phpCli = new CliMulti\CliPhp();
                $command = sprintf('%s %s/console core:run-scheduled-tasks --force "Piwik\Plugins\GeoIp2\GeoIP2AutoUpdater.update" > /dev/null 2>&1 &',
                    $phpCli->findPhpBinary(), PIWIK_INCLUDE_PATH);
                shell_exec($command);
            } else {
                /** @var Scheduler $scheduler */
                $scheduler = StaticContainer::getContainer()->get('Piwik\Scheduler\Scheduler');
                $scheduler->rescheduleTask(new GeoIP2AutoUpdater());
            }
        }
    }
}
