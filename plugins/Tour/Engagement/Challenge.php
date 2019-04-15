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


abstract class Challenge
{
    const APPENDIX_SKIPPED = '_skipped';
    const APPENDIX_COMPLETED = '_completed';

    private static $settings = null;

    abstract public function getName();

    abstract public function getId();

    /**
     * By default, we attribute a challenge as soon as it was completed manually by calling `$challenge->setCompleted()`.
     *
     * If we can detect whether a particular user has already completed a challenge in the past then we mark it automatically
     * as completed. We can detect this automatically eg by querying the DB and check if a particular login has for example
     * created a segment etc. We do this only if the query is supposed to be fast. Otherwise we would fallback to the manual
     * way.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->hasAttribute(self::APPENDIX_COMPLETED);
    }

    public function getDescription()
    {
        return '';
    }

    public function getUrl()
    {
        return '';
    }

    private function getPluginSettingsInstance()
    {
        return new PluginSettingsTable('Tour', Piwik::getCurrentUserLogin());
    }

    private function getSettings()
    {
        if (!isset(self::$settings)) {
            $pluginSettings = $this->getPluginSettingsInstance();
            self::$settings = $pluginSettings->load();
        }

        return self::$settings;
    }

    public static function clearCache()
    {
        self::$settings = null;
    }

    public function isSkipped()
    {
        return $this->hasAttribute(self::APPENDIX_SKIPPED);
    }

    public function skipChallenge()
    {
        $this->storeAttribute(self::APPENDIX_SKIPPED);
    }

    public function setCompleted()
    {
        $this->storeAttribute(self::APPENDIX_COMPLETED);
    }

    private function hasAttribute($appendix)
    {
        $settings = $this->getSettings();

        if (!empty($settings[$this->getId() . $appendix])) {
            return true;
        }

        return false;
    }

    private function storeAttribute($appendix)
    {
        $pluginSettings = $this->getPluginSettingsInstance();
        $settings = $pluginSettings->load();

        if (empty($settings[$this->getId() . $appendix])) {
            $settings[$this->getId() . $appendix] = '1';
            $pluginSettings->save($settings);
            self::clearCache();
        }
    }
}