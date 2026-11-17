<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection\Columns;

use Piwik\Plugins\CoreHome\Columns\Consent;
use Piwik\Plugins\DevicesDetection\DevicesDetection;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

class BrowserVersion extends Base
{
    protected $columnName = 'config_browser_version';
    protected $columnType = 'VARCHAR(20) NULL';
    protected $segmentName = 'browserVersion';
    protected $nameSingular = 'DevicesDetection_BrowserVersion';
    protected $namePlural = 'DevicesDetection_BrowserVersions';
    protected $acceptValues = '1.0, 8.0, etc.';
    protected $type = self::TYPE_TEXT;

    /**
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return $this->detectBrowserVersion($request, Consent::visitHasConsent($request, $visitor));
    }

    /**
     * Replaces the major-only version stored for a visit measured without consent with the full
     * version detected from the request that brings the visitor's consent to it.
     *
     * @param Action|null $action
     * @return string|false
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        $idSite = $request->getIdSiteIfExists();

        if (
            !DevicesDetection::shouldOnlyStoreMajorVersions($idSite)
            || !$this->isFirstConsentedRequest($request, $visitor, $action)
        ) {
            return false;
        }

        return $this->detectBrowserVersion($request, true);
    }

    private function detectBrowserVersion(Request $request, bool $hasConsent): string
    {
        $parser    = $this->getUAParser($request->getUserAgent(), $request->getClientHints());

        $aBrowserInfo = $parser->getClient();

        if (!empty($aBrowserInfo['version'])) {
            $version = substr($aBrowserInfo['version'], 0, 20);
            if (DevicesDetection::shouldOnlyStoreMajorVersions($request->getIdSiteIfExists(), $hasConsent)) {
                return explode('.', $version, 2)[0];
            }
            return $version;
        }

        return '';
    }
}
