<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Translation\Loader\JsonFileLoader;

/**
 * @group Translation
 */
class JsonFileLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldLoadJsonFile()
    {
        $loader = new JsonFileLoader();
        $translations = $loader->load('en', array(__DIR__ . '/fixtures/dir1'));

        $expected = array(
            'General' => array(
                'test1' => 'Hello',
                'test2' => 'Hello',
            ),
        );

        $this->assertEquals($expected, $translations);
    }

    public function testShouldIgnoreMissingFiles()
    {
        $loader = new JsonFileLoader();
        $translations = $loader->load('foo', array(__DIR__ . '/fixtures/dir1'));

        $this->assertEquals(array(), $translations);
    }

    public function testShouldMergeTranslationsIfLoadingMultipleFiles()
    {
        $loader = new JsonFileLoader();
        $translations = $loader->load('en', array(__DIR__ . '/fixtures/dir1', __DIR__ . '/fixtures/dir2'));

        $expected = array(
            'General' => array(
                'test1' => 'Hello',
                'test2' => 'Hello 2', // the second file should overwrite the first one
                'test3' => 'Hello 3',
            ),
        );

        $this->assertEquals($expected, $translations);
    }
}
