<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\AssetManager;

class AssetManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     */
    public function testPrioritySort()
    {
        $buckets = array(
            'libs/base.css',
            'libs/',
            'plugins/',
        );

        $data = array(
            'plugins/xyz',
            'plugins/abc',
            'libs/xyz',
            'libs/base.css',
            'libs/abc',
            'plugins/xyz',
            'libs/xyz',
        );

        $expected = array(
            'libs/base.css',
            'libs/xyz',
            'libs/abc',
            'plugins/xyz',
            'plugins/abc',
        );

        $this->assertEquals($expected, AssetManager::prioritySort($buckets, $data));
    }
}
