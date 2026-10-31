<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\tests\Integration;

use Piwik\Plugins\PrivacyManager\MeasurableSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Opting a site in lifts restrictions a compliance policy enforces, and enforcing a policy is
 * reserved to a superuser. A site admin who could switch this on would be able to undo a
 * decision they cannot make.
 *
 * @group PrivacyManager
 * @group Plugins
 */
class ConsentAwareAnalyticsSettingTest extends IntegrationTestCase
{
    /** @var int */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Consent aware');
    }

    public function testASuperUserMayOptTheSiteIn(): void
    {
        FakeAccess::$superUser = true;

        $settings = new MeasurableSettings($this->idSite);

        $this->assertTrue($settings->consentAwareAnalytics->isWritableByCurrentUser());
    }

    public function testASiteAdminMayNotOptTheSiteIn(): void
    {
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesAdmin = [$this->idSite];

        $settings = new MeasurableSettings($this->idSite);

        $this->assertFalse($settings->consentAwareAnalytics->isWritableByCurrentUser());
    }

    public function provideContainerConfig()
    {
        FakeAccess::$superUser = true;

        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
