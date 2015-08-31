<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreUpdater;

use Piwik\Common;
use Piwik\Config;
use Piwik\Http;
use Piwik\Plugins\SitesManager\API;
use Piwik\Url;
use Piwik\Version;
use Piwik\UpdateCheck\ReleaseChannel as BaseReleaseChannel;

abstract class ReleaseChannel extends BaseReleaseChannel
{
    public function getUrlToCheckForLatestAvailableVersion()
    {
        $parameters = array(
            'piwik_version'   => Version::VERSION,
            'php_version'     => PHP_VERSION,
            'release_channel' => $this->getId(),
            'url'             => Url::getCurrentUrlWithoutQueryString(),
            'trigger'         => Common::getRequestVar('module', '', 'string'),
            'timezone'        => API::getInstance()->getDefaultTimezone(),
        );

        $url = Config::getInstance()->General['api_service_url']
            . '/1.0/getLatestVersion/'
            . '?' . http_build_query($parameters, '', '&');

        return $url;
    }

    public function getDownloadUrlWithoutScheme($version)
    {
        return sprintf('://builds.piwik.org/piwik-%s.zip', $version);
    }
}