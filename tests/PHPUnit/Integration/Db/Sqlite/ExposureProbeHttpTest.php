<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Db\Sqlite;

use Piwik\Config;
use Piwik\Db\Sqlite\Connection;
use Piwik\Db\Sqlite\ExposureProbe;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Probes the test database over real HTTP. The test web server has no deny rules, so it serves the files.
 *
 * @group Core
 * @group Db
 */
class ExposureProbeHttpTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::isSqlite()) {
            $this->markTestSkipped('Probes a SQLite database file');
        }
    }

    public function testFindsTheTestDatabaseServed(): void
    {
        $file = Connection::getDatabaseFile(Config::getInstance()->database);

        $result = (new ExposureProbe())->probe($file, Fixture::getRootUrl());

        $this->assertSame(ExposureProbe::EXPOSED, $result['status'], var_export($result, true));
        $this->assertSame(ExposureProbe::EXPOSED, $result['urls'][Fixture::getRootUrl() . 'tmp/sqlite-tests/' . basename($file)]);
        $this->assertSame([], glob(dirname($file) . '/matomo-probe-*'), 'the canary is deleted');
    }

    public function testARedirectIsReportedNotFollowedAndALargeBodyIsCut(): void
    {
        $script = PIWIK_INCLUDE_PATH . '/tmp/probe-http-test-' . bin2hex(random_bytes(4)) . '.php';
        file_put_contents($script, '<?php if (isset($_GET["redirect"])) { header("Location: /matomo.js", true, 302); exit; }'
            . ' for ($i = 0; $i < 1024; $i++) { echo str_repeat("x", 8192); flush(); }');
        $url = Fixture::getRootUrl() . 'tmp/' . basename($script);
        $fetch = \Closure::bind(function (string $url) {
            return self::fetch($url);
        }, null, ExposureProbe::class);

        try {
            $this->assertSame(['status' => 302, 'body' => ''], $fetch($url . '?redirect=1'));

            $response = $fetch($url);
            $this->assertSame(200, $response['status']);
            $this->assertSame(str_repeat('x', 64), $response['body'], 'at most 64 bytes of 8 MB are kept');
        } finally {
            unlink($script);
        }
    }
}
