<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;


use Piwik\Context;
use Piwik\Tracker;

class ContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getTestDataForOverwriteIdSiteForCache
     */
    public function test_overwriteIdSiteForCache_shouldModifySuperGlobalsCorrectly(
        $originalGet,
        $originalPost,
        $inTrackerMode,
        $idSite,
        $expectedChangedGet,
        $expectedChangedPost
    ) {
        $originalTrackerMode = Tracker::$initTrackerMode;
        try {
            Tracker::$initTrackerMode = $inTrackerMode;

            $_GET = $originalGet;
            $_POST = $originalPost;

            Context::changeIdSite($idSite, function () use ($expectedChangedGet, $expectedChangedPost) {
                $this->assertEquals($expectedChangedGet, $_GET);
                $this->assertEquals($expectedChangedPost, $expectedChangedPost);
            });

            // make sure GET/POST revert correctly
            $this->assertEquals($originalGet, $_GET);
            $this->assertEquals($originalPost, $_POST);
        } finally {
            Tracker::$initTrackerMode = $originalTrackerMode;
        }
    }

    public function getTestDataForOverwriteIdSiteForCache()
    {
        return [
            // all get, no post
            [
                ['idSite' => '1', 'idSites' => '2,3', 'idsite' => '4'],
                [],
                false,
                '5',
                ['idSite' => '5', 'idsite' => '4'],
                ['idSite' => '5'],
            ],

            // all post, no get
            [
                [],
                ['idSite' => '1', 'idSites' => '2,3', 'idsite' => '4'],
                false,
                '5',
                ['idSite' => '5'],
                ['idSite' => '5', 'idsite' => '4'],
            ],

            // post + get, no idSites
            [
                ['idSite' => '1', 'idsite' => '3'],
                ['idSite' => '2', 'idsite' => '4'],
                false,
                '5',
                ['idSite' => '5', 'idsite' => '3'],
                ['idSite' => '5', 'idsite' => '4'],
            ],

            // post + get, tracker mode
            [
                ['idSite' => '1', 'idsite' => '4'],
                ['idSite' => '1', 'idsite' => '6'],
                true,
                '5',
                ['idSite' => '5', 'idsite' => '5'],
                ['idSite' => '5', 'idsite' => '5'],
            ],

            // no variables set before
            [
                [],
                [],
                false,
                '5',
                ['idSite' => '5'],
                ['idSite' => '5'],
            ],
            [
                [],
                [],
                true,
                '5',
                ['idSite' => '5', 'idsite' => '5'],
                ['idSite' => '5', 'idsite' => '5'],
            ],
        ];
    }
}
