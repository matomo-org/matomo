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
use Piwik\Settings\Storage\Backend\PluginSettingsTable;

/**
 * Defines a new challenge which a super user needs to complete in order to become a "Matomo expert".
 * Plugins can add new challenges by listening to the {@hook Tour.filterChallenges} event.
 *
 * @since 3.10.0
 * @api
 */
abstract class Challenge
{
    const APPENDIX_SKIPPED = '_skipped';
    const APPENDIX_COMPLETED = '_completed';

    private static $settings = [];

    public function __construct()
    {
    }

    /**
     * The human readable name that will be shown in the onboarding widget. Should be max 3 or 4 words and represent an
     * action, like "Add a report"
     * @return string
     */
    abstract public function getName();

    /**
     * A short unique ID that represents this challenge, for example "add_report".
     * @return string
     */
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
    public function isCompleted(string $login)
    {
        return $this->hasAttribute($login, self::APPENDIX_COMPLETED);
    }

    /**
     * By default challenges are enabled, if is not appropriate to display a challenge at this time because some condition
     * has not been met then the challenge can be set as disabled by overriding this method. The constructor code will
     * still be run every time the challenges are loaded. To disable a challenge based on plugin availablilty it is better
     * to add a check to the Piwik\Plugins\Tour\Engagement::getChallenges() method
     *
     * @return false
     */
    public function isDisabled()
    {
        return false;
    }

    /**
     * A detailed description that describes the value of the action the user needs to complete, or some tips on how
     * to complete this challenge. Will be shown when hovering a challenge name.
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * A URL that has more information about how to complete the given event or a URL within the Matomo app to directly
     * complete a challenge. For example "add_user" challenge could directly link to the user management.
     * @return string
     */
    public function getUrl()
    {
        return '';
    }

    private function getPluginSettingsInstance(string $login)
    {
        return new PluginSettingsTable('Tour', $login);
    }

    private function getSettings(string $login)
    {
        if (!isset(self::$settings[$login])) {
            $pluginSettings = $this->getPluginSettingsInstance($login);
            self::$settings[$login] = $pluginSettings->load();
        }

        return self::$settings[$login];
    }

    public static function clearCache()
    {
        self::$settings = [];
    }

    /**
     * Detect if the challenge was skipped.
     * @ignore
     * @return bool
     */
    public function isSkipped(string $login)
    {
        return $this->hasAttribute($login, self::APPENDIX_SKIPPED);
    }

    /**
     * Skip this challenge.
     * @ignore
     * @return bool
     */
    public function skipChallenge(string $login)
    {
        $this->storeAttribute($login, self::APPENDIX_SKIPPED);
    }

    /**
     * Set this challenge was completed successfully by the current user. Only works for a super user.
     * @return bool
     */
    public function setCompleted(string $login)
    {
        $this->storeAttribute($login, self::APPENDIX_COMPLETED);
    }

    private function hasAttribute(string $login, $appendix)
    {
        $settings = $this->getSettings($login);

        if (!empty($settings[$this->getId() . $appendix])) {
            return true;
        }

        return false;
    }

    private function storeAttribute(string $login, $appendix)
    {
        if (!Piwik::hasUserSuperUserAccess()) {
            return;
        }
        $pluginSettings = $this->getPluginSettingsInstance($login);
        $settings = $pluginSettings->load();

        if (empty($settings[$this->getId() . $appendix])) {
            $settings[$this->getId() . $appendix] = '1';
            $pluginSettings->save($settings);
            self::clearCache();
        }
    }
}
