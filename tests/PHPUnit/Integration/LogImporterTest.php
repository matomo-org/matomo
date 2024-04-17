<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Filesystem;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group Integration
 * @group LogImporter
 */
class LogImporterTest extends IntegrationTestCase
{
    public static $dateTime = '2012-08-09 11:22:33';

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        Fixture::createSuperUser(false);
        Fixture::createWebsite(self::$dateTime, 0, false, 'https://matomo.org/', 1, null, null, null, null, 0);
        Fixture::createWebsite(self::$dateTime, 1, false, 'https://shop.matomo.org/', 1, null, null, null, null, 0);
        Fixture::createWebsite(self::$dateTime, 0, false, 'https://piwik.org/', 1, null, null, null, null, 1);
    }

    /**
     * @dataProvider getParserOptionsToTest
     */
    public function testParserOptions($options, $expectedVisitAndActionCountPerSite, $outputToMatch = null)
    {
        // ensure to send testmode option
        $options['--enable-testmode'] = false;

        $result = Fixture::executeLogImporter(PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/access-logs/different_hosts.log', $options, true);

        if ($outputToMatch) {
            $this->assertStringContainsString($outputToMatch, implode("\n", $result));
        }

        foreach ($expectedVisitAndActionCountPerSite as $expectedVisitAndActionCount) {
            $this->assertVisitAndActionCount($expectedVisitAndActionCount[0], $expectedVisitAndActionCount[1], $expectedVisitAndActionCount[2] ?? null);
        }
    }

    public function getParserOptionsToTest(): array
    {
        return [
            [
                [],
                [[3, 3], [1, 1, 1], [1, 1, 2], [1, 1, 3]],
            ],
            [
                ['--token-auth' => Fixture::ADMIN_USER_TOKEN,],
                [[3, 3], [1, 1, 1], [1, 1, 2], [1, 1, 3]],
            ],
            [
                ['--login' => Fixture::ADMIN_USER_LOGIN, '--password' => Fixture::ADMIN_USER_PASSWORD,],
                [[3, 3], [1, 1, 1], [1, 1, 2], [1, 1, 3]],
            ],
            [
                ['--idsite-fallback' => '1',],
                [[4, 4], [2, 2, 1], [1, 1, 2], [1, 1, 3]],
            ],
            [
                ['--add-sites-new-hosts' => false,],
                [[4, 4], [1, 1, 1], [1, 1, 2], [1, 1, 3], [1, 1, 4]],
            ],
            [
                // --idsite that allows all hosts
                ['--idsite' => '1',],
                [[4, 4], [4, 4, 1], [0, 0, 2], [0, 0, 3]],
            ],
            [
                // --idsite that only allows specific hosts
                ['--idsite' => '3',],
                [[1, 1], [0, 0, 1], [0, 0, 2], [1, 1, 3]],
            ],
            [
                ['--skip' => '3',],
                [[1, 1], [0, 0, 1], [1, 1, 2], [0, 0, 3]],
            ],
            [
                ['--dry-run' => false,],
                [[0, 0], [0, 0, 1], [0, 0, 2], [0, 0, 3]],
                '4 requests imported successfully',
            ],
            [
                ['--log-hostname' => 'matomo.org',],
                [[4, 4], [4, 4, 1], [0, 0, 2], [0, 0, 3]],
            ],
            [
                ['--hostname' => '*matomo.org',],
                [[2, 2], [1, 1, 1], [1, 1, 2], [0, 0, 3]],
            ],
            [
                ['--hostname' => ['matomo.org', 'piwik.org'],],
                [[2, 2], [1, 1, 1], [0, 0, 2], [1, 1, 3]],
            ],
            [
                ['--include-host' => 'matomo.org',],
                [[1, 1], [1, 1, 1], [0, 0, 2], [0, 0, 3]],
            ],
            [
                ['--include-host' => ['matomo.org', 'shop.matomo.org'],],
                [[2, 2], [1, 1, 1], [1, 1, 2], [0, 0, 3]],
            ],
            [
                ['--exclude-host' => 'matomo.org',],
                [[2, 2], [0, 0, 1], [1, 1, 2], [1, 1, 3]],
            ],
            [
                ['--exclude-host' => ['matomo.org', 'shop.matomo.org'],],
                [[1, 1], [0, 0, 1], [0, 0, 2], [1, 1, 3]],
            ],
            [
                ['--exclude-older-than' => '2020-08-11 09:00:00 +0000',],
                [[1, 1], [0, 0, 1], [1, 1, 2], [0, 0, 3]],
            ],
            [
                ['--exclude-newer-than' => '2020-08-11 09:00:00 +0000',],
                [[2, 2], [1, 1, 1], [0, 0, 2], [1, 1, 3]],
            ],
            [
                ['--enable-static' => false,],
                [[3, 4], [1, 1, 1], [1, 1, 2], [1, 2, 3]],
            ],
            [
                ['--enable-bots' => false,],
                [[4, 4], [2, 2, 1], [1, 1, 2], [1, 1, 3]],
            ],
            [
                ['--useragent-exclude' => 'Android',],
                [[2, 2], [1, 1, 1], [1, 1, 2], [0, 0, 3]],
            ],
            [
                ['--useragent-exclude' => ['Android', 'Linux'],],
                [[1, 1], [0, 0, 1], [1, 1, 2], [0, 0, 3]],
            ],
            [
                ['--enable-http-errors' => false,],
                [[3, 4], [1, 1, 1], [1, 2, 2], [1, 1, 3]],
            ],
            [
                ['--enable-http-redirects' => false,],
                [[4, 4], [2, 2, 1], [1, 1, 2], [1, 1, 3]],
            ],
            [
                ['--exclude-path' => '*/guides/*',],
                [[2, 2], [1, 1, 1], [1, 1, 2], [0, 0, 3]],
            ],
            [
                ['--include-path' => '*/guides/*',],
                [[1, 1], [0, 0, 1], [0, 0, 2], [1, 1, 3]],
            ],
        ];
    }

    public function testEncodingOption()
    {
        $options = [
            '--encoding' => 'windows-1252',
            '--enable-testmode' => false,
        ];

        $result = Fixture::executeLogImporter(PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/access-logs/windows-1252.log', $options, true);

        $this->assertVisitCount(1, 1);
        $this->assertActionCount(1, 1);

        $name = Db::fetchOne('SELECT `name` FROM ' . Common::prefixTable('log_action'));
        $this->assertEquals('matomo.org/äöüß§$%', $name);
    }

    public function testOutputOption()
    {
        $file = StaticContainer::get('path.tmp') . '/logs/import_log.log';

        Filesystem::deleteFileIfExists($file);

        $options = [
            '--output' => $file,
            '--enable-testmode' => false,
        ];

        Fixture::executeLogImporter(PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/access-logs/different_hosts.log', $options, true);

        $this->assertVisitCount(3);

        $output = file_get_contents($file);
        $this->assertStringContainsString('4 requests imported successfully', $output);
    }

    protected function assertVisitAndActionCount($visitCount, $actionCount, $idSite = null)
    {
        $this->assertVisitCount($visitCount, $idSite);
        $this->assertActionCount($actionCount, $idSite);
    }

    protected function assertVisitCount($expectedCount, $idSite = null)
    {
        $where = $idSite ? ' AND idsite = ' . (int)$idSite : '';

        $visitCount = Db::fetchOne('SELECT count(*) FROM ' . Common::prefixTable('log_visit') . ' WHERE 1 ' . $where);

        self::assertEquals($expectedCount, $visitCount);
    }

    protected function assertActionCount($expectedCount, $idSite = null)
    {
        $where = $idSite ? ' AND idsite = ' . (int)$idSite : '';

        $actionCount = Db::fetchOne('SELECT count(*) FROM ' . Common::prefixTable('log_link_visit_action') . ' WHERE 1 ' . $where);

        self::assertEquals($expectedCount, $actionCount);
    }
}
