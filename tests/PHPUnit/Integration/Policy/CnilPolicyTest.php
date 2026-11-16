<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Policy;

use Piwik\Piwik;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class CnilPolicyTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // the copy under test is assembled from translations, so asserting on it needs them loaded
        Fixture::loadAllTranslations();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        parent::tearDown();
    }

    public function testGranularDescriptionBuildsTheStatusLegendAsListMarkup(): void
    {
        $description = CnilPolicy::getGranularDescription();

        // the list markup is built in code so translators cannot break it
        $this->assertStringContainsString("<ul class='browser-default'>", $description);
        $this->assertSame(4, substr_count($description, '<li>'));
        $this->assertStringContainsString(Piwik::translate('General_ComplianceCNILStatusLegendManual'), $description);
    }
}
