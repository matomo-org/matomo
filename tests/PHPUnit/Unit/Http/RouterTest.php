<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Http;

use Piwik\Http\Router;

/**
 * @group Core
 */
class RouterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testFilterUrl($url, $expected)
    {
        $filter = new Router();

        $this->assertSame($expected, $filter->filterUrl($url));
    }

    public function urlProvider()
    {
        return array(
            // Unfiltered URLs
            array('http://localhost/', null),
            array('http://localhost/index.php', null),
            array('http://localhost/index.php?module=CoreHome&action=index', null),
            // Filtered URLs
            array(
                'http://localhost/index.php/.html',
                'http://localhost/index.php'
            ),
            array(
                'http://localhost/index.php/.html?module=CoreHome&action=index',
                'http://localhost/index.php?module=CoreHome&action=index'
            ),
            array(
                'http://localhost/index.php/test/test.html?module=CoreHome&action=index',
                'http://localhost/index.php?module=CoreHome&action=index'
            ),
        );
    }
}
