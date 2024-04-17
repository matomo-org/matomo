<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

require_once(PIWIK_INCLUDE_PATH . '/core/Twig.php');

/**
 * @group Twig
 */
class TwigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getTruncateTests
     */
    public function testPiwikFilterTruncate($in, $size, $out)
    {
        $truncated = \Piwik\piwik_filter_truncate($in, $size);
        $this->assertEquals($out, $truncated);
    }

    public function getTruncateTests()
    {
        return [
            ['abc', 4, 'abc'],
            ['abc&quot;', 4, 'abc&quot;'],
            ['abc&nbsp;', 4, 'abc&nbsp;'],
            ['abcdef', 3, 'abc...'],
            ['ab&amp;ef', 3, 'ab&amp;...'],
            ['some&#9660;thing', 5, 'some&#9660;...'],
            ['ab&ef ;', 3, 'ab&...'],
            ['&lt;&gt;&#9660;&nbsp;', 4, '&lt;&gt;&#9660;&nbsp;']
        ];
    }
}
