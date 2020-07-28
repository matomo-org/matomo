<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Unit;

use Matomo\Network\IP;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
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

    public function getReferrerUrls()
    {
        return array(
            ['', 'https://foo.com/bar/baz?hello=world', ReferrerAnonymizer::EXCLUDE_ALL,],
            ['', 'https://foo.com/bar/baz?hello=world', ReferrerAnonymizer::EXCLUDE_URL,],
            ['https://foo.com/bar/baz/', 'https://foo.com/bar/baz/?hello=world', ReferrerAnonymizer::EXCLUDE_QUERY,],
            ['https://foo.com/bar/baz', 'https://foo.com/bar/baz?hello=world', ReferrerAnonymizer::EXCLUDE_QUERY,],
            ['https://foo.com/bar/baz', 'https://foo.com/bar/baz', ReferrerAnonymizer::EXCLUDE_QUERY,],
            ['https://foo.com/', 'https://foo.com/bar/baz/?hello=world', ReferrerAnonymizer::EXCLUDE_PATH,],
            ['https://foo.com/', 'https://foo.com/bar/baz?hello=world', ReferrerAnonymizer::EXCLUDE_PATH,],
            ['https://foo.com/', 'https://foo.com/bar/baz', ReferrerAnonymizer::EXCLUDE_PATH,],
            ['https://foo.com/', 'https://foo.com/bar/baz', ReferrerAnonymizer::EXCLUDE_PATH,],
            ['https://foo.com', 'https://foo.com', ReferrerAnonymizer::EXCLUDE_PATH,], // no path
            ['//foo.com/', '//foo.com/path?x=1', ReferrerAnonymizer::EXCLUDE_PATH,],
            ['foo.com/path?x=1', 'foo.com/path?x=1', ReferrerAnonymizer::EXCLUDE_PATH,],// not really a URL so isn't anonymised
        );
    }
}