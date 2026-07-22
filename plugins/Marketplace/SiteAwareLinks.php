<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UserPreferences;
use Piwik\Request;
use Piwik\Site;
use Piwik\Url;

class SiteAwareLinks
{
    /**
     * @return array{idSite?: int}
     */
    public function getIdSiteParameter(): array
    {
        $idSite = $this->getCurrentValidIdSiteOrDefault();
        if ($idSite === false) {
            return [];
        }

        return ['idSite' => $idSite];
    }

    public function getActionUrl(string $action, array $params = []): string
    {
        // Trusted values are merged last so a caller cannot overwrite the route or the
        // validated idSite this helper exists to guarantee (mirrors Plugin\Menu::urlForModuleAction()).
        $params = array_merge(
            $params,
            $this->getIdSiteParameter(),
            ['module' => 'Marketplace', 'action' => $action]
        );

        return '?' . Url::getQueryStringFromParameters($params);
    }

    public function getOverviewUrl(string $pluginName = ''): string
    {
        $url = $this->getActionUrl('overview');

        if ($pluginName === '') {
            return $url;
        }

        return $url . '#?' . Url::getQueryStringFromParameters(['showPlugin' => $pluginName]);
    }

    /**
     * @return false|int
     */
    public function getCurrentValidIdSiteOrDefault()
    {
        $requestedIdSite = Request::fromRequest()->getIntegerParameter('idSite', 0);
        if ($this->isValidWebsiteForCurrentUser($requestedIdSite)) {
            return $requestedIdSite;
        }

        $defaultIdSite = (new UserPreferences())->getDefaultWebsiteId();
        if (!is_numeric($defaultIdSite)) {
            return false;
        }

        return (int) $defaultIdSite;
    }

    private function isValidWebsiteForCurrentUser(int $idSite): bool
    {
        // Bounded check (no site enumeration): view access short-circuits for super users,
        // and new Site() confirms the site still exists. Covers "no access" and "deleted".
        if (empty($idSite)) {
            return false;
        }

        if (!Piwik::isUserHasViewAccess($idSite)) {
            return false;
        }

        try {
            new Site($idSite);
        } catch (UnexpectedWebsiteFoundException $e) {
            return false;
        }

        return true;
    }
}
