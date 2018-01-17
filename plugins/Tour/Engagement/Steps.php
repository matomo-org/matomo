<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\Plugins\Tour\Dao\DataFinder;
use Piwik\Settings\Storage\Backend\PluginSettingsTable;

class Steps
{
    /**
     * @var DataFinder
     */
    private $finder;

    private static $settings = null;

    public function __construct(DataFinder $dataFinder)
    {
        $this->finder = $dataFinder;
    }

    public function getSteps()
    {
        $steps = array(
            $this->getStep1(),
            $this->getStep2(),
            $this->getStep3(),
            $this->getStep4(),
            $this->getStep5(),
        );

        foreach ($steps as &$step) {
            $step['skipped'] = $this->isSkipped($step['key']);
        }

        return $steps;
    }

    private function getSettings()
    {
        if (!isset(self::$settings)) {
            $pluginSettings = new PluginSettingsTable('Tour', $login = '');
            self::$settings = $pluginSettings->load();
        }

        return self::$settings;
    }

    public function clearCache()
    {
        self::$settings = null;
    }

    protected function isSkipped($key)
    {
        $settings = $this->getSettings();

        if (!empty($settings[$key . '_skipped'])) {
            return true;
        }

        return false;
    }

    public static function skipStep($key)
    {
        $pluginSettings = new PluginSettingsTable('Tour', $login = '');
        $settings = $pluginSettings->load();
        $settings[$key . '_skipped'] = '1';
        $pluginSettings->save($settings);
    }

    protected function getStep1()
    {
        $done = $this->finder->hasTrackedData();

        return array(
            'name' => Piwik::translate('Tour_EmbedTrackingCode'), 'key' => 'track_data', 'done' => $done,
            'link' => array('module' => 'CoreAdminHome', 'action' => 'trackingCodeGenerator', 'widget' => false)
        );
    }

    protected function getStep2()
    {
        $done = $this->finder->hasCreatedGoal();

        return array(
            'name' => Piwik::translate('Tour_DefineGoal'), 'key' => 'define_goal', 'done' => $done,
            'link' => array('module' => 'Goals', 'action' => 'manage', 'widget' => false)
        );
    }

    protected function getStep3()
    {
        $logo = new CustomLogo();
        $done = $logo->isEnabled();

        return array(
            'name' => Piwik::translate('Tour_UploadLogo'), 'key' => 'setup_branding', 'done' => $done,
            'link' => array('module' => 'CoreAdminHome', 'action' => 'generalSettings', 'widget' => false),
            'linkHash' => 'useCustomLogo'
        );
    }

    protected function getStep4()
    {
        $done = $this->finder->hasAddedUser();

        return array(
            'name' => Piwik::translate('Tour_AddUser'), 'key' => 'add_user', 'done' => $done,
            'link' => array('module' => 'UsersManager', 'action' => 'index', 'widget' => false)
        );
    }

    protected function getStep5()
    {
        $done = $this->finder->hasAddedWebsite();

        return array(
            'name' => Piwik::translate('Tour_AddWebsite'), 'key' => 'add_website', 'done' => $done,
            'link' => array('module' => 'SitesManager', 'action' => 'index', 'widget' => false)
        );
    }

}