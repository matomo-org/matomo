<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestCase;

use Piwik\Config;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;

/**
 * Tests extending IntegrationTestCase are much slower to run: the setUp will
 * create all Piwik tables in a freshly empty test database.
 *
 * This allows each test method to start from a clean DB and setup initial state to
 * then test it.
 *
 * @since 2.8.0
 */
abstract class IntegrationTestCase extends SystemTestCase
{
    /**
     * Implementation details:
     *
     * To increase speed of tests, database setup is done once in setUpBeforeClass.
     * Afterwards, the content of the tables is stored in a static class variable,
     * self::$tableData. Before each individual test, the database tables are
     * truncated and the data in self::$tableData is restored.
     *
     * If your test modifies table columns, you will need to recreate the database
     * completely. This can be accomplished by:
     *
     *     public function setUp()
     *     {
     *         self::$fixture->performSetUp();
     *     }
     *
     *     public function tearDown()
     *     {
     *         parent::tearDown();
     *         self::$fixture->performTearDown();
     *     }
     */
    public static function setUpBeforeClass()
    {
        static::configureFixture(static::$fixture);
        parent::setUpBeforeClass();
        static::beforeTableDataCached();
    }

    protected static function configureFixture($fixture)
    {
        $fixture->createSuperUser     = false;
        $fixture->configureComponents = false;

        $fixture->extraTestEnvVars['loadRealTranslations'] = false;
    }

    /**
     * @deprecated
     */
    protected static function beforeTableDataCached()
    {
        // empty
    }
}
