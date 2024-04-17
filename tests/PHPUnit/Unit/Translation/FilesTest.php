<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

/**
 * @group Translation
 * @group langfiles
 */
class FilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getTranslationFiles
     */
    public function testForValidJsonFiles($file)
    {
        $json = json_decode(file_get_contents($file), true);

        $this->assertIsArray($json, "translation file $file seems to be corrupted or empty");
    }

    public function getTranslationFiles()
    {
        $filesBase    = glob(PIWIK_DOCUMENT_ROOT . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . '*.json');
        $filesPlugins = glob(PIWIK_DOCUMENT_ROOT . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . '*.json');

        $allFiles = array_merge($filesBase, $filesPlugins);
        return array_map(function ($val) {
            return [$val];
        }, $allFiles);
    }
}
