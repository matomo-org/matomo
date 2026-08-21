<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Transitions;

use Piwik\Piwik;

class Controller extends \Piwik\Plugin\Controller
{
    /**
     * The referrer type labels Transitions.getTransitionsForAction puts on its groups, mapped to
     * their translation keys. The indirection keeps the API code readable, since it can name a
     * referrer type rather than repeat a translation key for it.
     */
    private static $metricTranslations = array(
        'fromSearchEngines'  => 'Transitions_FromSearchEngines',
        'fromSocialNetworks' => 'Transitions_FromSocialNetworks',
        'fromAIAssistants'   => 'Transitions_FromAIAssistants',
        'fromWebsites'       => 'Transitions_FromWebsites',
        'fromCampaigns'      => 'Transitions_FromCampaigns',
        'directEntries'      => 'Transitions_DirectEntries',
    );

    public static function getTranslation($key)
    {
        return Piwik::translate(self::$metricTranslations[$key]);
    }
}
