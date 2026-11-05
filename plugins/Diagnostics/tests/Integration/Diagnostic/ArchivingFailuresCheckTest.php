<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Diagnostics\tests\Integration\Diagnostic;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveFailures;
use Piwik\Date;
use Piwik\Plugins\Diagnostics\Diagnostic\ArchivingFailuresCheck;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Translation\Translator;

/**
 * @group Diagnostics
 */
class ArchivingFailuresCheckTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();
        Fixture::createWebsite('2015-01-01 00:00:00');
        Date::$now = strtotime('2020-02-03 04:05:06');
        Config::getInstance()->General['archive_failure_max_attempts'] = 2;
    }

    public function tearDown(): void
    {
        Date::$now = null;
        Fixture::resetTranslations();
        parent::tearDown();
    }

    public function testNoResultWithoutFailures()
    {
        $this->assertSame([], $this->getCheck()->execute());
    }

    public function testOkWhenFailuresAreWaitingForRetry()
    {
        $this->recordFailure('2020-01-01', 1);

        $result = $this->getSingleResult();

        $this->assertSame(DiagnosticResult::STATUS_OK, $result->getStatus());
        $this->assertStringContainsString('1 archives could not be processed and are waiting to be retried, and 0 archives were given up', $result->getItems()[0]->getComment());
        $this->assertStringContainsString('<code>./console core:archive-failures</code>', $result->getItems()[0]->getComment());
    }

    public function testWarningWhenFailuresWereGivenUp()
    {
        $this->recordFailure('2020-01-01', 2);
        $this->recordFailure('2020-01-02', 1);

        $result = $this->getSingleResult();

        $this->assertSame(DiagnosticResult::STATUS_WARNING, $result->getStatus());
        $this->assertStringContainsString('1 archives could not be processed and are waiting to be retried, and 1 archives were given up', $result->getItems()[0]->getComment());
        $this->assertStringNotContainsString('Simulated error', $result->getItems()[0]->getComment());
    }

    private function recordFailure(string $date, int $attempts): void
    {
        $archiveFailures = new ArchiveFailures();
        for ($i = 0; $i < $attempts; $i++) {
            $archiveFailures->recordFailure(['idsite' => 1, 'name' => 'done', 'period' => 1, 'date1' => $date, 'date2' => $date, 'report' => null], 'Simulated error');
        }
    }

    private function getSingleResult(): DiagnosticResult
    {
        $results = $this->getCheck()->execute();
        $this->assertCount(1, $results);

        return $results[0];
    }

    private function getCheck(): ArchivingFailuresCheck
    {
        return new ArchivingFailuresCheck(StaticContainer::get(Translator::class));
    }
}
