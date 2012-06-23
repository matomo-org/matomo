<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
class HttpTest extends PHPUnit_Framework_TestCase
{
    /**
     * Dataprovider for testFetchRemoteFile
     */
    public function getMethodsToTest()
    {
        return array(
            array('curl'),
            array('fopen'),
            array('socket'),
        );
    }

    /**
     * @group Core
     * @group Http
     * @dataProvider getMethodsToTest
     */
    public function testFetchRemoteFile($method)
    {
        $this->assertNotNull(Piwik_Http::getTransportMethod());
        $version = Piwik_Http::sendHttpRequestBy($method, 'http://api.piwik.org/1.0/getLatestVersion/', 5);
        $this->assertTrue( (boolean)preg_match('/^([0-9.]+)$/', $version) );
    }

    /**
     * @group Core
     * @group Http
     */
    public function testFetchApiLatestVersion()
    {
        $destinationPath = PIWIK_USER_PATH . '/tmp/latest/LATEST';
        Piwik_Http::fetchRemoteFile('http://api.piwik.org/1.0/getLatestVersion/', $destinationPath, 3);
        $this->assertFileExists($destinationPath);
        $this->assertGreaterThan( 0, filesize($destinationPath) );
    }

    /**
     * @group Core
     * @group Http
     */
    public function testFetchLatestZip()
    {
        $destinationPath = PIWIK_USER_PATH . '/tmp/latest/latest.zip';
        Piwik_Http::fetchRemoteFile('http://piwik.org/latest.zip', $destinationPath, 3);
        $this->assertFileExists($destinationPath);
        $this->assertGreaterThan( 0, filesize($destinationPath) );
    }
}
