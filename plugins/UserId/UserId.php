<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId;

use Piwik\Plugins\UserId\Settings\UserIdDisabled;

/**
 * Plugin adds a new Users report showing all unique user IDs and some aggregated data
 */
class UserId extends \Piwik\Plugin
{
    /**
     * Register event observers
     *
     * @return array
     */
    public function registerEvents()
    {
        return array(
            // Add plugin's custom JS files
            'AssetManager.getJavaScriptFiles' => 'getJavaScriptFiles',
            // Add translations for the client side JS
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            // Keep the configured value available to the tracker alongside the enforced one
            'Tracker.Cache.getSiteAttributes' => 'setTrackerCacheSiteAttributes',
        );
    }

    /**
     * The tracker needs the value an admin set themselves as well as the one a compliance policy
     * resolves to, so that consent can lift the policy's requirement without overriding theirs.
     */
    public function setTrackerCacheSiteAttributes(&$cacheContent, int $idSite): void
    {
        $cacheContent[UserIdDisabled::CONFIGURED_VALUE_CACHE_KEY] = UserIdDisabled::getMeasurableValue($idSite);
    }

    /**
     * Add a custom JS to the page. It adds possibility to open visitor details popover for each
     * user ID in a report table
     *
     * @param $jsFiles
     */
    public function getJavaScriptFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/UserId/javascripts/rowaction.js";
    }

    /**
     * Add translations for the client side JS
     *
     * @param $translationKeys
     */
    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = "Live_ViewVisitorProfile";
        $translationKeys[] = "UserId_UserIdDisabledSettingRequirementNote";
        $translationKeys[] = "UserId_UserIdDisabledSettingTitle";
    }
}
