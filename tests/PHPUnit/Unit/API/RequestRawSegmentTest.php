<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\API;

use Piwik\API\Request;

/**
 * @group Core
 */
class RequestRawSegmentTest extends \PHPUnit\Framework\TestCase
{
    private $backupServer;
    private $backupGet;
    private $backupPost;
    private $backupRequest;

    public function setUp(): void
    {
        parent::setUp();
        $this->backupServer = $_SERVER;
        $this->backupGet = $_GET;
        $this->backupPost = $_POST;
        $this->backupRequest = $_REQUEST;
    }

    public function tearDown(): void
    {
        $_SERVER = $this->backupServer;
        $_GET = $this->backupGet;
        $_POST = $this->backupPost;
        $_REQUEST = $this->backupRequest;
        parent::tearDown();
    }

    public function testGetRawSegmentFromRequestReadsUrlEncodedQueryString(): void
    {
        $_SERVER['QUERY_STRING'] = 'module=API&segment=browserCode%3D%3DFF';
        $_GET = ['module' => 'API', 'segment' => 'browserCode==FF'];
        $_POST = [];
        $_REQUEST = $_GET;

        self::assertSame('browserCode%3D%3DFF', Request::getRawSegmentFromRequest());
    }

    public function testGetRawSegmentFromRequestReadsSegmentSentInPostBody(): void
    {
        $_SERVER['QUERY_STRING'] = 'module=API&method=ImageGraph.get';
        $_GET = ['module' => 'API', 'method' => 'ImageGraph.get'];
        $_POST = ['segment' => 'browserCode==FF;countryCode==de'];
        $_REQUEST = array_merge($_GET, $_POST);

        self::assertSame('browserCode%3D%3DFF%3BcountryCode%3D%3Dde', Request::getRawSegmentFromRequest());
    }

    public function testGetRawSegmentFromRequestKeepsAmpersandInPostSegment(): void
    {
        $_SERVER['QUERY_STRING'] = 'module=API&method=ImageGraph.get';
        $_GET = ['module' => 'API', 'method' => 'ImageGraph.get'];
        $_POST = ['segment' => 'pageTitle==Q&A'];
        $_REQUEST = array_merge($_GET, $_POST);

        self::assertSame('pageTitle%3D%3DQ%26A', Request::getRawSegmentFromRequest());
    }

    public function testGetRawSegmentFromRequestReturnsFalseWithoutSegment(): void
    {
        $_SERVER['QUERY_STRING'] = 'module=API';
        $_GET = ['module' => 'API'];
        $_POST = [];
        $_REQUEST = $_GET;

        self::assertFalse(Request::getRawSegmentFromRequest());
    }
}
