<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\tests\System;

use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Fixtures\EmptySite;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group CorePluginsAdmin
 * @group ApiTest
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var EmptySite
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToTest = [];
        $apiToTest[] = [['CorePluginsAdmin.getSystemSettings'], []];

        return $apiToTest;
    }

    public function testGetSystemSettingsWithComplianceAvailable(): void
    {
        $this->runApiTests('CorePluginsAdmin.getSystemSettings', [
            'testSuffix' => '_compliancePolicyFeatureFlagEnabled',
        ]);
    }

    public function testGetSiteSettingsWhenPolicyEnforced(): void
    {
        CnilPolicy::setActiveStatus(null, true);

        $this->runApiTests('CorePluginsAdmin.getSystemSettings', [
            'testSuffix' => '_compliancePolicyEnforced',
        ]);

        CnilPolicy::setActiveStatus(null, false);
    }

    public static function getOutputPrefix()
    {
        return 'CorePluginsAdmin';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

ApiTest::$fixture = new EmptySite();
