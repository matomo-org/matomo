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
        Translate::loadEnglishTranslation();
    }

    public function tearDown()
    {
        Translate::unloadEnglishTranslation();
    }

    public function test_languageAware_shouldAppendTheLoadedLanguage()
    {
        $result = CacheId::languageAware('myrandomkey');

        $this->assertEquals('myrandomkey-en', $result);
    }

    public function test_pluginAware_shouldAppendLoadedPluginsAndLanguage()
    {
        $result = CacheId::pluginAware('myrandomkey');

        // if this test fails most likely there is a new plugin loaded and you simple have to update the cache id.
        $this->assertEquals('myrandomkey-8f88a1dea9163e86178e69a1293ec084-en', $result);
    }
}
