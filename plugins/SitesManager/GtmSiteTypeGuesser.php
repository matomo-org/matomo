<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SitesManager;

use Piwik\Plugins\SitesManager\SitesManager;

class GtmSiteTypeGuesser
{
    public function guessSiteTypeFromResponse($response)
    {
        if ($response === false) {
            return SitesManager::SITE_TYPE_UNKNOWN;
        }

        $needle = '/wp-content';
        if (strpos($response['data'], $needle) !== false) {
            return SitesManager::SITE_TYPE_WORDPRESS;
        }

        $needle = '<!-- This is Squarespace. -->';
        if (strpos($response['data'], $needle) !== false) {
            return SitesManager::SITE_TYPE_SQUARESPACE;
        }

        $needle = 'X-Wix-Published-Version';
        if (strpos($response['data'], $needle) !== false) {
            return SitesManager::SITE_TYPE_WIX;
        }

        // https://github.com/joomla/joomla-cms/blob/staging/libraries/src/Application/WebApplication.php#L516
        // Joomla was the outcome of a fork of Mambo on 17 August 2005 - https://en.wikipedia.org/wiki/Joomla
        if (isset($response['headers']['expires']) && $response['headers']['expires'] === 'Wed, 17 Aug 2005 00:00:00 GMT') {
            return SitesManager::SITE_TYPE_JOOMLA;
        }

        $needle = 'Shopify.theme';
        if (strpos($response['data'], $needle) !== false) {
            return SitesManager::SITE_TYPE_SHOPIFY;
        }

        $needle = 'content="Microsoft SharePoint';
        if (strpos($response['data'], $needle) !== false) {
            return SitesManager::SITE_TYPE_SHAREPOINT;
        }

        return SitesManager::SITE_TYPE_UNKNOWN;
    }

    public function guessGtmFromResponse($response)
    {
        if ($response === false) {
            return false;
        }

        $needle = 'gtm.start';
        if (strpos($response['data'], $needle) !== false) {
            return true;
        }

        return false;
    }
}
