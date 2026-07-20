<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution\tests\System;

use Exception;
use Piwik\API\Request;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Plugins\Resolution\tests\Fixtures\TwoSitesWithResolutions;
use Piwik\Policy\PolicyManager;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Plugins
 * @group Resolution
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var TwoSitesWithResolutions
     */
    public static $fixture = null; // initialized below class definition

    protected function tearDown(): void
    {
        $this->setComplianceFeatureFlag(false);
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, self::$fixture->idSite);
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, self::$fixture->idSite2);
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, null);

        parent::tearDown();
    }

    public function testGetResolutionReturnsOnlyAllowedSitesForSpecificSiteList(): void
    {
        $this->setComplianceFeatureFlag(true);
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, self::$fixture->idSite);

        $this->assertSame(
            ['1024x768'],
            $this->getResolutionLabelsForSiteRequest(self::$fixture->idSite . ',' . self::$fixture->idSite2)
        );
    }

    public function testGetResolutionReturnsOnlyAllowedSitesForAll(): void
    {
        $this->setComplianceFeatureFlag(true);
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, self::$fixture->idSite);

        $this->assertSame(['1024x768'], $this->getResolutionLabelsForSiteRequest('all'));
    }

    public function testGetResolutionReturnsErrorWhenSingleRequestedSiteIsDisallowed(): void
    {
        $this->setComplianceFeatureFlag(true);
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, true, self::$fixture->idSite);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Screen resolution report is disabled by compliance policy.');

        $this->getResolutionLabelsForSiteRequest((string) self::$fixture->idSite);
    }

    /**
     * @return list<string>
     */
    private function getResolutionLabelsForSiteRequest(string $idSite): array
    {
        /** @var DataTable|DataTable\Map $report */
        $report = Request::processRequest('Resolution.getResolution', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => self::$fixture->dateTime,
            'flat' => '1',
        ]);

        return array_values($report->getColumn('label'));
    }

    private function setComplianceFeatureFlag(bool $enableFlag): void
    {
        $featureFlag = new PrivacyCompliance();
        $featureFlagConfig = $featureFlag->getName() . '_feature';

        Config::getInstance()->FeatureFlags = [
            $featureFlagConfig => $enableFlag ? 'enabled' : 'disabled',
        ];
    }
}

ApiTest::$fixture = new TwoSitesWithResolutions();
