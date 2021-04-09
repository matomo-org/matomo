<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Cache;
use Piwik\Segment;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorApi;
use Piwik\Plugins\SegmentEditor\Model as SegmentEditorModel;

/**
 * @group Segment
 * @group SegmentsCacheTest
 */
class SegmentsCacheTest extends IntegrationTestCase
{
    public function test_segment_hash_cached()
    {
        $cache = Cache::getEagerCache();
        $model = new SegmentEditorModel();

        $idSegment = SegmentEditorApi::getInstance()->add('test segment 1', 'browserCode==ff');
        $segment = $model->getSegment($idSegment);
        $key1 = Segment::CACHE_KEY . md5($segment['definition']);

        $this->assertFalse($cache->contains($key1));

        Segment::getSegmentHash('dummy');

        $this->assertTrue($cache->contains($key1));
    }

    public function test_if_segment_not_found_default_hash_returned()
    {
        $definition = 'browserCode==ch';
        $defaultHash = md5(urldecode($definition));

        $this->assertEquals($defaultHash, Segment::getSegmentHash($definition));
    }
}
