<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\EventDispatcher;
use Piwik\Http;
use Piwik\Http\EgressBlockedException;
use Piwik\Http\EgressHostValidator;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Version;

/**
 * @group Core
 * @group HttpTest
 */
class HttpTest extends \PHPUnit\Framework\TestCase
{
    /** @var array|null */
    private $originalBlocklist;

    /** @var EgressHostValidator|null */
    private $originalEgressValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalBlocklist = StaticContainer::get('http.blocklist.hosts');
        $this->originalEgressValidator = StaticContainer::get(EgressHostValidator::class);
    }

    protected function tearDown(): void
    {
        // Some tests register Http.sendHttpRequest observers to mock responses. Observers
        // can't be removed, so replace the dispatcher to keep them from leaking into later tests.
        StaticContainer::getContainer()->set(
            EventDispatcher::class,
            new EventDispatcher(Manager::getInstance())
        );


        StaticContainer::getContainer()->set('http.blocklist.hosts', $this->originalBlocklist);
        StaticContainer::getContainer()->set(EgressHostValidator::class, $this->originalEgressValidator);

        parent::tearDown();
    }

    /**
     * Dataprovider for testFetchRemoteFile
     */
    public function getMethodsToTest()
    {
        return array(
            'curl' => array('curl'),
            'fopen' => array('fopen'),
            'socket' => array('socket'),
        );
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testFetchRemoteFile($method)
    {
        $this->assertNotNull(Http::getTransportMethod());
        $result = Http::sendHttpRequestBy($method, Fixture::getRootUrl() . 'matomo.js', 30);
        $this->assertTrue(strpos($result, 'Matomo') !== false);
    }

    public function testFetchApiLatestVersion()
    {
        $destinationPath = PIWIK_DOCUMENT_ROOT . '/tmp/latest/LATEST';
        Http::fetchRemoteFile(Fixture::getRootUrl(), $destinationPath, 3);
        $this->assertFileExists($destinationPath);
        $this->assertGreaterThan(0, filesize($destinationPath));
    }

    public function testFetchLatestZip()
    {
        $destinationPath = PIWIK_DOCUMENT_ROOT . '/tmp/latest/latest.zip';
        Http::fetchRemoteFile(Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/fixture.zip', $destinationPath, 3, 30);
        $this->assertFileExists($destinationPath);
        $this->assertGreaterThan(0, filesize($destinationPath));
    }

    public function testBuildQuery()
    {
        $this->assertEquals('', Http::buildQuery(array()));
        $this->assertEquals('test=foo', Http::buildQuery(array('test' => 'foo')));
        $this->assertEquals('test=foo&bar=baz', Http::buildQuery(array('test' => 'foo', 'bar' => 'baz')));
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testCustomByteRange($method)
    {
        if ($method == 'fopen') {
            self::expectNotToPerformAssertions();
            return; // not supported w/ this method
        }

        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . '/matomo.js',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = array(10, 20),
            $getExtendedInfo = true
        );

        $this->assertEquals(206, $result['status']);
        $this->assertTrue(isset($result['headers']['Content-Range']));
        $this->assertEquals('bytes 10-20/', substr($result['headers']['Content-Range'], 0, 12));
        $this->assertTrue(in_array($result['headers']['Content-Type'], array('application/x-javascript', 'application/javascript')));
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHEADOperation($method)
    {
        if ($method == 'fopen') {
            self::expectNotToPerformAssertions();
            return; // not supported w/ this method
        }

        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/fixture.zip',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = true,
            $httpMethod = 'HEAD'
        );

        $this->assertEquals('', $result['data']);
        $this->assertEquals(200, $result['status']);

        $this->assertTrue(isset($result['headers']['Content-Length']), "Content-Length header not set!");
        $this->assertTrue(is_numeric($result['headers']['Content-Length']), "Content-Length header not numeric!");
        $this->assertTrue(in_array($result['headers']['Content-Type'], array('application/zip', 'application/x-zip-compressed')));
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpAuthentication($method)
    {
        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/HttpAuthentication.php',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = true,
            $httpMethod = 'GET',
            $httpUsername = 'test',
            $httpPassword = 'test'
        );

        $this->assertEquals('Authentication successful', $result['data']);
        $this->assertEquals(200, $result['status']);
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpAuthenticationInvalid($method)
    {
        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/HttpAuthentication.php',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = true,
            $httpMethod = 'GET',
            $httpUsername = '',
            $httpPassword = ''
        );

        $this->assertEquals(401, $result['status']);
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpPostViaString($method)
    {
        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/Post.php',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = false,
            $httpMethod = 'POST',
            $httpUsername = '',
            $httpPassword = '',
            'abc12=43&abfec=abcdef'
        );

        $this->assertEquals('{"abc12":"43","abfec":"abcdef","method":"post"}', $result);
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpPostViaArray($method)
    {
        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/Post.php',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = false,
            $httpMethod = 'POST',
            $httpUsername = '',
            $httpPassword = '',
            array('adf2' => '44', 'afc23' => 'ab12')
        );

        $this->assertEquals('{"adf2":"44","afc23":"ab12","method":"post"}', $result);
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpCustomHeaders($method)
    {
        $result = Http::sendHttpRequestBy(
            $method,
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/AdditionalHeaders.php',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = false,
            $getExtendedInfo = false,
            $httpMethod = 'POST',
            $httpUsername = '',
            $httpPassword = '',
            array(),
            array('CustomHeader: customdata')
        );

        $this->assertEquals('customdata', $result);
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpsWorksWithValidCertificate($method)
    {
        $result = Http::sendHttpRequestBy($method, 'https://builds.matomo.org/LATEST', 10);

        $this->assertStringMatchesFormat('%d.%d.%d', $result);
    }

    /**
     * error message can be:
     *      curl_exec: server certificate verification failed. CAfile: /home/travis/build/piwik/piwik/core/DataFiles/cacert.pem CRLfile: none. Hostname requested was: self-signed.badssl.com
     * or
     *      curl_exec: SSL certificate problem: self signed certificate. Hostname requested was: self-signed.badssl.com
     */
    public function testCurlHttpsFailsWithInvalidCertificate()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/curl_exec: .*certificate.* /');

        // use a domain from https://badssl.com/
        Http::sendHttpRequestBy('curl', 'https://self-signed.badssl.com/', 10);
    }

    public function testFopenHttpsFailsWithInvalidCertificate()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/failed to open stream/i');

        // use a domain from https://badssl.com/
        Http::sendHttpRequestBy('fopen', 'https://self-signed.badssl.com/', 10);
    }

    public function testSocketHttpsWorksWithValidCertificate()
    {
        $result = Http::sendHttpRequestBy('socket', 'https://piwik.org/', 10);
        $this->assertNotEmpty($result);
    }

    /**
     * @dataProvider getMethodsToTest
     */
    public function testHttpDownloadChunkResponseSizeLimitedToChunk($method)
    {
        $result = Http::sendHttpRequestBy(
            $method,
            'https://builds.matomo.org/matomo.zip',
            300,
            null,
            null,
            null,
            0,
            '',
            false,
            array(0, 50)
        );
        /**
         * The last arg above asked the server to limit the response sent back to bytes 0->50.
         * The RFC for HTTP Range Requests says that these headers can be ignored, so the test
         * depends on a server that will respect it - we are requesting our build download, which does.
         */
        $this->assertEquals(51, strlen($result));
    }

    /**
     * @dataProvider getRedirectUrls
     */
    public function testRedirects($url, $method, $isValid, $message)
    {
        if ($isValid === false) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessageMatches($message);
        }

        $response = Http::sendHttpRequestBy($method, $url, 1000);

        if ($isValid !== false) {
            $this->assertEquals($message, $response);
        }
    }

    public function getRedirectUrls()
    {
        return [
            // check 5 redirects are working
            [Fixture::getRootUrl() . 'tests/resources/redirector.php?redirects=5', 'curl', true, Fixture::getRootUrl() . 'tests/resources/redirector.php?redirects=0'],
            [Fixture::getRootUrl() . 'tests/resources/redirector.php?redirects=5', 'socket', true, Fixture::getRootUrl() . 'tests/resources/redirector.php?redirects=0'],
            [Fixture::getRootUrl() . 'tests/resources/redirector.php?redirects=4', 'fopen', true, Fixture::getRootUrl() . 'tests/resources/redirector.php?redirects=0'],

            // more than 5 redirects should fail
            [Fixture::getRootUrl() . 'tests/resources/redirector.php?redirects=6', 'curl', false, '/curl_exec: Maximum \(5\) redirects followed./'],
            [Fixture::getRootUrl() . 'tests/resources/redirector.php?redirects=6', 'socket', false, '/Too many redirects/'],
            [Fixture::getRootUrl() . 'tests/resources/redirector.php?redirects=6', 'fopen', true, ''],

            // Redirect to disallowed protocol shouldn't be possible
            [Fixture::getRootUrl() . 'tests/resources/redirector.php?target=' . urlencode('ftps://my.local'), 'curl', false, '/curl_exec: Protocol "ftps" not supported or disabled in libcurl/'],
            [Fixture::getRootUrl() . 'tests/resources/redirector.php?target=' . urlencode('ftps://my.local'), 'socket', false, '/Protocol ftps not in list of allowed protocols/'],
            //[Fixture::getRootUrl().'tests/resources/redirector.php?target='.urlencode('ftps://my.local'), 'fopen', false, ''],
        ];
    }

    public function testHttpPostsEvent()
    {
        $params = null;
        $params2 = null;
        Piwik::addAction('Http.sendHttpRequest', function () use (&$params) {
            $params = func_get_args();
        });
        Piwik::addAction('Http.sendHttpRequest.end', function () use (&$params2) {
            $params2 = func_get_args();
        });
        $destinationPath = PIWIK_USER_PATH . '/tmp/latest/LATEST';
        $url = Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/Post.php';
        Http::sendHttpRequestBy(
            Http::getTransportMethod(),
            $url,
            30,
            $userAgent = null,
            $destinationPath,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = array(10, 20),
            $getExtendedInfo = false,
            $httpMethod = 'POST',
            $httpUsername = '',
            $httpPassword = '',
            array('adf2' => '44', 'afc23' => 'ab12')
        );

        $this->assertEquals(array($url, array(
            'httpMethod' => 'POST',
            'body' => array('adf2' => '44','afc23' => 'ab12'),
            'userAgent' => 'Matomo/' . Version::VERSION,
            'timeout' => 30,
            'headers' => array(
                'Range: bytes=10-20',
                'Via: ' . Version::VERSION . '  (Matomo/' . Version::VERSION . ')',
            ),
            'verifySsl' => true,
            'destinationPath' => $destinationPath,
            'validateEgressIp' => false,
        ), null, null, array()), $params);

        $this->assertNotEmpty($params2[4]);// headers
        unset($params2[4]);
        $this->assertEquals(array($url, array(
            'httpMethod' => 'POST',
            'body' => array('adf2' => '44','afc23' => 'ab12'),
            'userAgent' => 'Matomo/' . Version::VERSION,
            'timeout' => 30,
            'headers' => array(
                'Range: bytes=10-20',
                'Via: ' . Version::VERSION . '  (Matomo/' . Version::VERSION . ')',
            ),
            'verifySsl' => true,
            'destinationPath' => $destinationPath,
            'validateEgressIp' => false,
        ), '{"adf2":"44","afc23":"ab12","method":"post"}', 200), $params2);
    }

    public function testHttpReturnsResultOfPostedEvent()
    {
        Piwik::addAction('Http.sendHttpRequest', function ($url, $args, &$response, &$status, &$headers) {
            $response = '{test: true}';
            $status = 204;
            $headers = array('content-length' => 948);
        });

        $result = Http::sendHttpRequestBy(
            Http::getTransportMethod(),
            Fixture::getRootUrl() . 'tests/PHPUnit/Integration/Http/Post.php',
            30,
            $userAgent = null,
            $destinationPath = null,
            $file = null,
            $followDepth = 0,
            $acceptLanguage = false,
            $acceptInvalidSslCertificate = false,
            $byteRange = array(10, 20),
            $getExtendedInfo = true,
            $httpMethod = 'POST',
            $httpUsername = '',
            $httpPassword = '',
            array('adf2' => '44', 'afc23' => 'ab12')
        );

        $this->assertEquals(array(
            'data' => '{test: true}',
            'status' => 204,
            'headers' => array('content-length' => 948),
        ), $result);
    }

    /**
     * @dataProvider getProtocolUrls
     */
    public function testInvalidProtocols($url, $message)
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage($message);

        Http::sendHttpRequest($url, 5);
    }

    public function getProtocolUrls()
    {
        return [
            ['phar://malformed.url', 'Protocol phar not in list of allowed protocols: http,https'],
            ['ftp://usful.ftp/file.md', 'Protocol ftp not in list of allowed protocols: http,https'],
            ['rtp://custom.url', 'Protocol rtp not in list of allowed protocols: http,https'],
            ['/local/file', 'Missing scheme in given url'],
        ];
    }

    public function testSendHttpRequestByWithEgressValidationFollowsRedirects()
    {
        $this->allowEgressValidationForTestHost();

        $result = Http::sendHttpRequestBy(
            'curl',
            Fixture::getRootUrl() . 'tests/resources/redirector.php?redirects=2',
            30,
            null,
            null,
            null,
            0,
            false,
            false,
            false,
            true,
            'GET',
            null,
            null,
            null,
            array(),
            null,
            true,
            true // $validateEgressIp
        );

        $this->assertEquals(200, $result['status']);
        self::assertStringContainsString('redirects=0', $result['data']);
    }

    public function testSendHttpRequestByWithEgressValidationRejectsRedirectToPrivateHost()
    {
        $this->allowEgressValidationForTestHost();

        self::expectException(EgressBlockedException::class);
        self::expectExceptionMessage('Refusing to fetch: host resolves to a private or reserved address.');

        Http::sendHttpRequestBy(
            'curl',
            Fixture::getRootUrl() . 'tests/resources/redirector.php?target=' . urlencode('http://10.0.0.1/'),
            30,
            null,
            null,
            null,
            0,
            false,
            false,
            false,
            true,
            'GET',
            null,
            null,
            null,
            array(),
            null,
            true,
            true // $validateEgressIp
        );
    }

    public function testSendHttpRequestByWithEgressValidationIgnoresForgedLocationInResponseBody()
    {
        $this->allowEgressValidationForTestHost();

        // The body forges a header block that overwrites $headers['Location'], so this proves the
        // redirect target comes from curl and not from the parsed headers.
        $result = Http::sendHttpRequestBy(
            'curl',
            Fixture::getRootUrl() . 'tests/resources/redirector.php?redirects=1&forgeLocation='
                . urlencode('http://169.254.169.254/latest/meta-data/'),
            30,
            null,
            null,
            null,
            0,
            false,
            false,
            false,
            true, // $getExtendedInfo
            'GET',
            null,
            null,
            null,
            array(),
            null,
            true,
            true // $validateEgressIp
        );

        $this->assertEquals(200, $result['status']);
        self::assertStringContainsString('redirects=0', $result['data']);
    }

    public function testSendHttpRequestByWithEgressValidationDropsSecretsCrossOriginButKeepsRelaxedCertCheck()
    {
        $this->allowEgressValidationForTestHost();

        // A different port on the same host is a different origin, and stays within the allowlisted
        // loopback ranges so the hop is validated rather than rejected.
        $host = (string) parse_url(Fixture::getRootUrl(), PHP_URL_HOST);
        $target = 'http://' . $host . ':9/';

        $hops = array();
        Piwik::addAction('Http.sendHttpRequest', function ($url, $params, &$response) use (&$hops) {
            $hops[] = $params;
            if (count($hops) === 2) {
                $response = 'stopped'; // handle the second hop so it never reaches the network
            }
        });

        Http::sendHttpRequestBy(
            'curl',
            Fixture::getRootUrl() . 'tests/resources/redirector.php?target=' . urlencode($target),
            30,
            null,
            null,
            null,
            0,
            false,
            true, // $acceptInvalidSslCertificate
            false,
            true, // $getExtendedInfo
            'POST',
            'user', // $httpUsername
            'secret', // $httpPassword
            array('a' => 'b'), // $requestBody
            array('X-Caller: keep-me'), // $additionalHeaders
            null,
            true,
            true // $validateEgressIp
        );

        self::assertCount(2, $hops);

        // credentials, caller headers, the body and the method must not survive the origin change
        self::assertSame(array('POST', 'GET'), array_column($hops, 'httpMethod'));
        self::assertSame(array('a' => 'b'), $hops[0]['body']);
        self::assertNull($hops[1]['body']);
        self::assertContains('X-Caller: keep-me', $hops[0]['headers']);
        self::assertNotContains('X-Caller: keep-me', $hops[1]['headers']);
        self::assertNotEmpty(preg_grep('/^Authorization:/', $hops[0]['headers']));
        self::assertEmpty(preg_grep('/^Authorization:/', $hops[1]['headers']));

        // the relaxed certificate check does survive, see the rationale in Http::sendHttpRequestBy()
        self::assertSame(array(false, false), array_column($hops, 'verifySsl'));
    }

    public function testSendHttpRequestByWithEgressValidationRejectsBlockedHostAfterCanonicalisation()
    {
        $this->allowEgressValidationForTestHost();

        // lowercased because the exception reports the canonicalised (lowercased) host
        $host = strtolower((string) parse_url(Fixture::getRootUrl(), PHP_URL_HOST));
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $this->markTestSkipped('Test requires a DNS test host, not an IP literal.');
        }

        // Blocklist the plain host. The raw request host carries a trailing dot, so it slips the
        // pre-connection check, but canonicalisation strips the dot and the re-check must catch it.
        StaticContainer::getContainer()->set('http.blocklist.hosts', [$host]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage('Hostname ' . $host . ' is in list of disallowed hosts');

        $url = str_ireplace('://' . $host, '://' . $host . '.', Fixture::getRootUrl())
            . 'tests/resources/redirector.php';

        Http::sendHttpRequestBy(
            'curl',
            $url,
            30,
            null,
            null,
            null,
            0,
            false,
            false,
            false,
            true,
            'GET',
            null,
            null,
            null,
            array(),
            null,
            true,
            true // $validateEgressIp
        );
    }

    public function testSendHttpRequestWithEgressValidationRejectsRedirectWhenDownloadingToFile()
    {
        $this->allowEgressValidationForTestHost();

        $destinationPath = PIWIK_DOCUMENT_ROOT . '/tmp/latest/egress-redirect-test';

        try {
            Http::sendHttpRequest(
                Fixture::getRootUrl() . 'tests/resources/redirector.php?redirects=1',
                30,
                null,
                $destinationPath,
                0,
                false,
                false,
                false,
                'GET',
                null,
                null,
                true,
                true // $validateEgressIp
            );
            $this->fail('Expected the redirect to be refused while downloading to a file');
        } catch (EgressBlockedException $e) {
            self::assertSame('SSRF-safe HTTP requests cannot follow redirects when downloading to a file.', $e->getMessage());
            // the truncated download must not be left behind for the caller to pick up
            clearstatcache(true, $destinationPath);
            self::assertFileNotExists($destinationPath);
        } finally {
            @unlink($destinationPath);
        }
    }

    /**
     * Lets the SSRF-safe path accept the local test host, which resolves to loopback
     * and would otherwise be rejected as non-public. Keeps the real curl transport,
     * pinning and redirect re-validation in place.
     */
    private function allowEgressValidationForTestHost(): void
    {
        $host = (string) parse_url(Fixture::getRootUrl(), PHP_URL_HOST);

        $allowedRanges = ['127.0.0.1', '::1'];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $allowedRanges[] = $host;
        } else {
            $allowedRanges = array_merge($allowedRanges, gethostbynamel($host) ?: []);
        }

        StaticContainer::getContainer()->set(
            EgressHostValidator::class,
            new EgressHostValidator(null, array_unique($allowedRanges))
        );
    }

    /**
     * @dataProvider getBlockedHostUrls
     */
    public function testBlockedHosts($url, $isValid, $message = '')
    {
        EventDispatcher::getInstance()->addObserver('Http.sendHttpRequest', function ($aUrl, $httpEventParams, &$response, &$status, &$headers) {
            $response = 'prevented request';
        });

        StaticContainer::getContainer()->set('http.blocklist.hosts', [
            '*.amazonaws.com',
            'matomo.org',
            'piwik.*',
        ]);

        if (!$isValid) {
            self::expectException(\Exception::class);
            self::expectExceptionMessage($message);
        }

        self::assertEquals('prevented request', Http::sendHttpRequest($url, 5));
    }

    public function getBlockedHostUrls()
    {
        return [
            ['https://test.amazonaws.com/test/download', false, 'Hostname test.amazonaws.com is in list of disallowed hosts'],
            ['https://s3.us-west-2.amazonaws.com/mybucket/puppy.jpg', false, 'Hostname s3.us-west-2.amazonaws.com is in list of disallowed hosts'],
            ['https://s3.us-west-2.amazonaws.de/mybucket/puppy.jpg', true],
            ['https://matomo.org', false, 'Hostname matomo.org is in list of disallowed hosts'],
            ['https://builds.matomo.org', true],
            ['https://piwik.org', false, 'Hostname piwik.org is in list of disallowed hosts'],
            ['https://piwik.com.ax', false, 'Hostname piwik.com.ax is in list of disallowed hosts'],
            ['https://de.piwik.org', true],
        ];
    }
}
