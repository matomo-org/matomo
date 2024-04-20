<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Unit;

use Piwik\Common;
use Piwik\Plugins\PrivacyManager\ReferrerAnonymizer;

class AnonymizeReferrerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReferrerAnonymizer
     */
    private $anonymizer;

    public function setUp(): void
    {
        parent::setUp();
        $this->anonymizer = new ReferrerAnonymizer();
    }

    /**
     * @dataProvider getReferrerUrls
     */
    public function test_anonymiseReferrerUrl($expected, $url, $option)
    {
        $value = $this->anonymizer->anonymiseReferrerUrl($url, $option);
        $this->assertSame($expected, $value);
    }

    /**
     * @dataProvider getReferrerNames
     */
    public function test_anonymiseReferrerName($expected, $name, $type, $option)
    {
        $value = $this->anonymizer->anonymiseReferrerName($name, $type, $option);
        $this->assertSame($expected, $value);
    }

    public function getReferrerNames()
    {
        return array(
            ['foo', 'foo', Common::REFERRER_TYPE_WEBSITE, ReferrerAnonymizer::EXCLUDE_NONE,],
            ['baz', 'baz', Common::REFERRER_TYPE_SEARCH_ENGINE, ReferrerAnonymizer::EXCLUDE_ALL,], // search and social is always kept
            ['bar', 'bar', Common::REFERRER_TYPE_CAMPAIGN, ReferrerAnonymizer::EXCLUDE_ALL,], //campaign is always kept
            ['', 'foo.com', Common::REFERRER_TYPE_WEBSITE, ReferrerAnonymizer::EXCLUDE_ALL,],
            ['foo.com', 'foo.com', Common::REFERRER_TYPE_WEBSITE, ReferrerAnonymizer::EXCLUDE_PATH,], // host should be kept
        );
    }

    /**
     * @dataProvider getReferrerKeywords
     */
    public function test_anonymiseReferrerKeyword($expected, $keyword, $type, $option)
    {
        $value = $this->anonymizer->anonymiseReferrerKeyword($keyword, $type, $option);
        $this->assertSame($expected, $value);
    }

    public function getReferrerKeywords()
    {
        return array(
            ['foo', 'foo', Common::REFERRER_TYPE_WEBSITE, ReferrerAnonymizer::EXCLUDE_NONE,],
            ['bar', 'bar', Common::REFERRER_TYPE_CAMPAIGN, ReferrerAnonymizer::EXCLUDE_ALL,], //campaign is always kept
            ['', 'baz', Common::REFERRER_TYPE_SEARCH_ENGINE, ReferrerAnonymizer::EXCLUDE_ALL,],
            ['', 'foo.com', Common::REFERRER_TYPE_WEBSITE, ReferrerAnonymizer::EXCLUDE_ALL,],
            ['', 'foo.com', Common::REFERRER_TYPE_WEBSITE, ReferrerAnonymizer::EXCLUDE_PATH,],
            ['', 'foo.com', Common::REFERRER_TYPE_WEBSITE, ReferrerAnonymizer::EXCLUDE_QUERY,],
            ['', 'foo.com', Common::REFERRER_TYPE_SEARCH_ENGINE, ReferrerAnonymizer::EXCLUDE_QUERY,],
            ['', 'foo.com', Common::REFERRER_TYPE_SEARCH_ENGINE, ReferrerAnonymizer::EXCLUDE_PATH,],
            ['foo.com', 'foo.com', Common::REFERRER_TYPE_SEARCH_ENGINE, ReferrerAnonymizer::EXCLUDE_NONE,],
            ['foo.com', 'foo.com', Common::REFERRER_TYPE_WEBSITE, ReferrerAnonymizer::EXCLUDE_NONE,],
        );
    }

    public function getReferrerUrls()
    {
        return array(
            [false, false, ReferrerAnonymizer::EXCLUDE_ALL,],
            [false, false, ReferrerAnonymizer::EXCLUDE_NONE,],
            ['https://foo.com/bar/baz?hello=world', 'https://foo.com/bar/baz?hello=world', ReferrerAnonymizer::EXCLUDE_NONE,],
            ['', 'https://foo.com/bar/baz?hello=world', ReferrerAnonymizer::EXCLUDE_ALL,],
            ['https://foo.com/bar/baz/', 'https://foo.com/bar/baz/?hello=world', ReferrerAnonymizer::EXCLUDE_QUERY,],
            ['https://foo.com/bar/baz', 'https://foo.com/bar/baz?hello=world', ReferrerAnonymizer::EXCLUDE_QUERY,],
            ['https://foo.com/bar/baz', 'https://foo.com/bar/baz', ReferrerAnonymizer::EXCLUDE_QUERY,],
            ['https://foo.com/', 'https://foo.com/bar/baz/?hello=world', ReferrerAnonymizer::EXCLUDE_PATH,],
            ['https://foo.com/', 'https://foo.com/bar/baz?hello=world', ReferrerAnonymizer::EXCLUDE_PATH,],
            ['https://foo.com/', 'https://foo.com/bar/baz', ReferrerAnonymizer::EXCLUDE_PATH,],
            ['https://foo.com/', 'https://foo.com/bar/baz', ReferrerAnonymizer::EXCLUDE_PATH,],
            ['https://foo.com', 'https://foo.com', ReferrerAnonymizer::EXCLUDE_PATH,], // no path
            ['//foo.com/', '//foo.com/path?x=1', ReferrerAnonymizer::EXCLUDE_PATH,],
            ['foo.com/path?x=2', 'foo.com/path?x=2', ReferrerAnonymizer::EXCLUDE_PATH,],// not really a URL so isn't anonymised
        );
    }
}
