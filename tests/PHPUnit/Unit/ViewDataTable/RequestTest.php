<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\ViewDataTable;

use Piwik\ViewDataTable\Request;
use Piwik\ViewDataTable\RequestConfig;

/**
 * @group Core
 * @group ViewDataTable
 */
class RequestTest extends \PHPUnit\Framework\TestCase
{
    private $backupGet;

    public function setUp(): void
    {
        $this->backupGet = $_GET;
    }

    public function tearDown(): void
    {
        $_GET = $this->backupGet;
    }

    public function testGetRequestArrayShouldUseStringValueFromRequest()
    {
        $_GET['filter_limit'] = '10';

        $this->assertSame('10', $this->getRequestArray()['filter_limit']);
    }

    public function testGetRequestArrayShouldUseDefaultWhenRequestValueIsArray()
    {
        $_GET['filter_limit'] = ['1'];

        $this->assertSame(5, $this->getRequestArray()['filter_limit']);
    }

    private function getRequestArray(): array
    {
        $config = new RequestConfig();
        $config->filter_limit = 5;

        return (new Request($config))->getRequestArray();
    }
}
