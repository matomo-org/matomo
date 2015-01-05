<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\CacheId;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Translate;

/**
 * @group Cache
 * @group CacheId
 */
class CacheIdTest extends IntegrationTestCase
{
    public function setUp()
    {
        Translate::loadAllTranslations();
    }

    public function tearDown()
    {
        Translate::reset();
    }

    public function test_languageAware_shouldAppendTheLoadedLanguage()
    {
        $result = CacheId::languageAware('myrandomkey');

        $this->assertEquals('myrandomkey-en', $result);
    }

    public function test_pluginAware_shouldAppendLoadedPluginsAndLanguage()
    {
        $result = CacheId::pluginAware('myrandomkey');

        $parts = explode('-', $result);

        $this->assertCount(3, $parts);
        $this->assertEquals('myrandomkey', $parts[0]);
        $this->assertEquals(32, strlen($parts[1]), $parts[1] . ' is not a MD5 hash');
        $this->assertEquals('en', $parts[2]);
    }
}
