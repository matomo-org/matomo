<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater;

use Piwik\Common;
use Piwik\Db;
use Piwik\Http;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\SitesManager\API;
use Piwik\UpdateCheck\ReleaseChannel as BaseReleaseChannel;
use Piwik\Url;
use Piwik\Version;

abstract class ReleaseChannel extends BaseReleaseChannel
{
    public function getUrlToCheckForLatestAvailableVersion()
    {
        $parameters = array(
            'piwik_version'   => Version::VERSION,
            'php_version'     => PHP_VERSION,
            'mysql_version'   => Db::get()->getServerVersion(),
            'release_channel' => $this->getId(),
            'url'             => Url::getCurrentUrlWithoutQueryString(),
            'trigger'         => Common::getRequestVar('module', '', 'string'),
            'timezone'        => API::getInstance()->getDefaultTimezone(),
        );

        $url = Client::getApiServiceUrl()
            . '/1.0/getLatestVersion/'
            . '?' . Http::buildQuery($parameters);

        return $url;
    }

    public function getDownloadUrlWithoutScheme($version)
    {
        if (!empty($version)) {
            return sprintf('://builds.matomo.org/matomo-%s.zip', $version);
        }

        return '://builds.matomo.org/matomo.zip';
    }
}
