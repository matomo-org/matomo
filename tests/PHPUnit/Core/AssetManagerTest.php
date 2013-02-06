<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class AssetManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     * @group AssetManager
     */
    public function testPrioritySort()
    {
        $buckets = array(
            'themes/base.css',
            'themes/',
            'libs/base.css',
            'libs/',
            'plugins/',
        );

        $data = array(
            'plugins/xyz',
            'plugins/abc',
            'themes/base.css',
            'libs/xyz',
            'libs/base.css',
            'libs/abc',
            'plugins/xyz',
            'themes/test',
            'libs/xyz',
        );

        $expected = array(
            'themes/base.css',
            'themes/test',
            'libs/base.css',
            'libs/xyz',
            'libs/abc',
            'plugins/xyz',
            'plugins/abc',
        );

        $this->assertEquals($expected, Piwik_AssetManager::prioritySort($buckets, $data));
    }
}
