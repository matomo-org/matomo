<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\Integration;

use Piwik\Plugins\Actions\Columns\Metrics\AveragePageGenerationTime;
use Piwik\Tests\Framework\Assert\ProcessedMetricAssert;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ProcessedMetricFormulasTest extends IntegrationTestCase
{
    private $assert;

    public function setUp(): void
    {
        parent::setUp();
        $this->assert = new ProcessedMetricAssert();
    }

    public function test_ProcessedMetricFormulasInReportsAreValid()
    {
        $this->assert->assertProcessedMetricsInReportMetadataAreValid(
            'Actions',
            [AveragePageGenerationTime::class]
        );
    }
}
