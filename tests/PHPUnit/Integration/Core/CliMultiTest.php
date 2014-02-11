<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use \Piwik\Version;
use \Piwik\Common;
use \Piwik\Url;

/**
 * Class Core_CliMultiTest
 *
 * @group Core
 */
class Core_CliMultiTest extends IntegrationTestCase
{
    /**
     * @var \Piwik\CliMulti
     */
    private $cliMulti;

    /**
     * @var string
     */
    private $authToken = '';

    /**
     * @var string[]
     */
    private $urls = array();

    /**
     * @var string[]
     */
    private $responses = array();

    public function setUp()
    {
        parent::setUp();

        $this->cliMulti  = new \Piwik\CliMulti();
        $this->authToken = Test_Piwik_BaseFixture::getTokenAuth();

        $this->urls = array(
            'getAnswerToLife' => $this->completeUrl('?module=API&method=ExampleAPI.getAnswerToLife&format=JSON'),
            'getPiwikVersion' => $this->completeUrl('?module=API&method=API.getPiwikVersion&format=JSON'),
        );

        $this->responses = array(
            'getAnswerToLife' => '{"value":42}',
            'getPiwikVersion' => '{"value":"' . Version::VERSION . '"}'
        );
    }

    public function test_request_shouldNotFailAndReturnNoResponse_IfNoUrlsAreGiven()
    {
        $response = $this->cliMulti->request(array());

        $this->assertEquals(array(), $response);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage array
     */
    public function test_request_shouldFail_IfUrlsIsNotAnArray()
    {
        $this->cliMulti->request('');
    }

    public function test_request_shouldReturnResultAsArray_IfOnlyOneUrlIsGiven()
    {
        $urls = $this->buildUrls('getAnswerToLife');

        $this->assertRequestReturnsValidResponses($urls, array('getAnswerToLife'));
    }

    public function test_request_shouldRunAsync()
    {
        $this->assertTrue($this->cliMulti->supportsAsync);
    }

    public function test_request_shouldRequestAllUrls_IfMultipleUrlsAreGiven()
    {
        $urls = $this->buildUrls('getPiwikVersion', 'getAnswerToLife');

        $this->assertRequestReturnsValidResponses($urls, array('getPiwikVersion', 'getAnswerToLife'));
    }

    public function test_request_shouldReturnSameAmountOfResponses_IfSameUrlAppearsMultipleTimes()
    {
        $urls = $this->buildUrls('getAnswerToLife', 'getAnswerToLife');

        $this->assertRequestReturnsValidResponses($urls, array('getAnswerToLife', 'getAnswerToLife'));
    }

    public function test_request_shouldCleanupAllTempFiles_OnceAllRequestsAreFinished()
    {
        $numFilesBefore = $this->getNumberOfFilesInTmpFolder();

        $this->cliMulti->request($this->buildUrls('getAnswerToLife', 'getAnswerToLife'));

        $numFilesAfter = $this->getNumberOfFilesInTmpFolder();

        $this->assertSame($numFilesAfter, $numFilesBefore);
        $this->assertGreaterThan(1, $numFilesAfter);
    }

    /**
     * This is a known issue, we do not get a content in case Piwik ends with an exit, but we have to make sure
     * we detect the request has finished though
     */
    public function test_request_shouldDetectFinishOfRequest_IfNoParamsAreGiven()
    {
        $response = $this->cliMulti->request(array($this->completeUrl('')));

        $this->assertStringStartsWith('Error: no website was found', $response[0]);

        $response = $this->cliMulti->request(array('/'));

        $this->assertStringStartsWith('<!DOCTYPE html>', $response[0]);
    }

    public function test_request_shouldBeAbleToRenderARegularPageInPiwik()
    {
        Test_Piwik_BaseFixture::createWebsite('2014-01-01 00:00:00');

        $urls = array($this->completeUrl('/?module=Widgetize&idSite=1&period=day&date=today'));

        $response = $this->cliMulti->request($urls);

        $this->assertTrue(false !== strpos($response[0], '<meta name="generator" content="Piwik - Open Source Web Analytics"/>'));
        $this->assertTrue(false !== strpos($response[0], 'Widgetize the full dashboard'));
    }

    public function test_shouldFallback_IfAsyncIsNotSupported()
    {
        $this->cliMulti->supportsAsync = false;

        $urls = $this->buildUrls('getPiwikVersion', 'getAnswerToLife', 'getPiwikVersion');

        $this->assertRequestReturnsValidResponses($urls, array('getPiwikVersion', 'getAnswerToLife', 'getPiwikVersion'));
    }

    private function assertRequestReturnsValidResponses($urls, $expectedResponseIds)
    {
        $actualResponse = $this->cliMulti->request($urls);

        $this->assertInternalType('array', $actualResponse);
        $this->assertCount(count($expectedResponseIds), $actualResponse);

        $expected = array();
        foreach ($expectedResponseIds as $expectedResponseId) {
            $expected[] = $this->responses[$expectedResponseId];
        }

        $this->assertEquals($expected, $actualResponse);
    }

    private function buildUrls()
    {
        $urls = array();

        foreach (func_get_args() as $urlId) {
            $urls[] = $this->urls[$urlId];
        }

        return $urls;
    }

    private function completeUrl($query)
    {
        $host = Test_Piwik_BaseFixture::getRootUrl();

        if (false === strpos($query, '?')) {
            $query .= '?';
        } else {
            $query .= '&';
        }

        return $host . 'tests/PHPUnit/proxy/index.php' . $query . 'testmode=1&token_auth=' . $this->authToken;
    }

    private function getNumberOfFilesInTmpFolder()
    {
        $dir = PIWIK_INCLUDE_PATH . '/tmp';

        $numFilesInTmp        = count(\_glob($dir . "/*", null));
        $numFilesInSubfolders = count(\_glob($dir . "/*/*", null));

        return $numFilesInTmp + $numFilesInSubfolders;
    }

}