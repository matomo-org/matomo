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

    public function test_cashed_hashes_for_similar_segments()
    {
        $cache = Cache::getEagerCache();
        $model = new SegmentEditorModel();

        // pageUrl==http://abc.com/d+f
        // pageUrl==http://abc.com/d f
        // pageUrl==http://abc.com/d%20f
        $definitions = [
            'pageUrl==http%253A%252F%252Fabc.com%252Fd%252Bf',
            'pageUrl==http%253A%252F%252Fabc.com%252Fd%2520f',
            'pageUrl==http%253A%252F%252Fabc.com%252Fd%252520f',
        ];

        $idSegment1 = SegmentEditorApi::getInstance()->add('test segment 1', $definitions[0]);
        $idSegment2 = SegmentEditorApi::getInstance()->add('test segment 2', $definitions[1]);
        $idSegment3 = SegmentEditorApi::getInstance()->add('test segment 3', $definitions[2]);
        $segment1 = $model->getSegment($idSegment1);
        $segment2 = $model->getSegment($idSegment2);
        $segment3 = $model->getSegment($idSegment3);
        $tests = [];

        $tests[$segment1['hash']] = [
            Segment::CACHE_KEY . md5($segment1['definition']),
            Segment::CACHE_KEY . md5(urldecode($segment1['definition'])),
            Segment::CACHE_KEY . md5(urlencode($segment1['definition'])),
        ];
        $tests[$segment2['hash']] = [
            Segment::CACHE_KEY . md5($segment2['definition']),
            Segment::CACHE_KEY . md5(urldecode($segment2['definition'])),
            Segment::CACHE_KEY . md5(urlencode($segment2['definition'])),
        ];
        $tests[$segment3['hash']] = [
            Segment::CACHE_KEY . md5($segment3['definition']),
            Segment::CACHE_KEY . md5(urldecode($segment3['definition'])),
            Segment::CACHE_KEY . md5(urlencode($segment3['definition'])),
        ];

        Segment::getSegmentHash('dummy');

        foreach ($tests as $hash => $keys) {
            foreach ($keys as $key) {
                $this->assertEquals($hash, $cache->fetch($key));
            }
        }
    }

    public function test_segment_cache_with_operator_characters()
    {
        $cache = Cache::getEagerCache();
        $model = new SegmentEditorModel();

        // pageUrl==http://abc.com/=/@/!/,/;
        $idSegment = SegmentEditorApi::getInstance()->add('test segment 1', 'pageUrl==http%253A%252F%252Fabc.com%252F%253D%252F%2540%252F!%252F%252C%252F%253B');
        $segment = $model->getSegment($idSegment);
        $key1 = Segment::CACHE_KEY . md5($segment['definition']);

        Segment::getSegmentHash('dummy');

        $this->assertTrue($cache->contains($key1));
    }
}
