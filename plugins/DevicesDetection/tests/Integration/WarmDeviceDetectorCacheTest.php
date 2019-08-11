<?php


namespace Piwik\Plugins\DevicesDetection\tests\Integration;


use Piwik\DeviceDetector\DeviceDetectorCacheEntry;
use Piwik\DeviceDetector\DeviceDetectorFactory;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

class WarmDeviceDetectorCacheTest extends ConsoleCommandTestCase
{
    public function testWritesUserAgentsToFile()
    {
        $testFile = __DIR__ . '/files/useragents1.csv';

        $this->applicationTester->run(array(
            'command' => 'devicedetector:warmcache',
            'input-file' => $testFile
        ));

        $userAgents = array(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 12_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko'
        );

        $this->assertContains("Written 3 cache entries to file", $this->applicationTester->getDisplay());
        foreach ($userAgents as $userAgent) {
            $this->assertUserAgentWrittenToFile($userAgent);
        }
    }

    public function testInputFileDoesntExist()
    {
        $testFile = __DIR__ . '/files/notarealfile.csv';

        $this->applicationTester->run(array(
            'command' => 'devicedetector:warmcache',
            'input-file' => $testFile
        ));

        $this->assertContains("File $testFile not found", $this->applicationTester->getDisplay());
    }

    public function testNotSkippingHeaderRow()
    {
        $testFile = __DIR__ . '/files/useragentsnoheader.csv';

        $this->applicationTester->run(array(
            'command' => 'devicedetector:warmcache',
            'input-file' => $testFile,
            '--skip-header-row' => false
        ));

        $userAgents = array(
            'Mozilla/5.0 (iPad; CPU OS 12_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 12_3_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Mobile/15E148 Safari/604.1',
            'Dalvik/2.1.0 (Linux; U; Android 6.0.1; SM-J510FN Build/MMB29M)'
        );

        $this->assertContains("Written 3 cache entries to file", $this->applicationTester->getDisplay());
        foreach ($userAgents as $userAgent) {
            $this->assertUserAgentWrittenToFile($userAgent);
        }
    }

    public function testSkipsUserAgentsInIgnoreList()
    {
        $testFile = __DIR__ . '/files/useragentstoignore.csv';

        $this->applicationTester->run(array(
            'command' => 'devicedetector:warmcache',
            'input-file' => $testFile
        ));

        $this->assertContains("Written 0 cache entries to file", $this->applicationTester->getDisplay());

        $userAgent = 'Amazon-Route53-Health-Check-Service (ref d14cb74a-74d4-4400-940d-1579e3f0181b; report http://amzn.to/1vsZADi)';
        $this->assertUserAgentNotWrittenToFile($userAgent);
    }

    public function testClearsExistingFilesFromCache()
    {
        $userAgent = 'Mozilla/5.0 (Linux; Android 8.0.0; SAMSUNG SM-G930F Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/9.4 Chrome/67.0.3396.87 Mobile Safari/537.36';
        $cacheFilePath = DeviceDetectorCacheEntry::getCachePath($userAgent);

        file_put_contents($cacheFilePath, "<?php return array();", LOCK_EX);

        $this->assertFileExists($cacheFilePath);
        $this->assertFileExists(PIWIK_DOCUMENT_ROOT . DeviceDetectorCacheEntry::CACHE_DIR . 'd4');
        $testFile = __DIR__ . '/files/useragents1.csv';

        $this->applicationTester->run(array(
            'command' => 'devicedetector:warmcache',
            'input-file' => $testFile
        ));

        // It wasn't in the list of user agents from the CSV file so it should have been removed
        // Folder should be removed too as there's no useragents that should have been written there
        $this->assertFileNotExists($cacheFilePath);
        $this->assertFileNotExists(PIWIK_DOCUMENT_ROOT . DeviceDetectorCacheEntry::CACHE_DIR . 'd4');
    }

    public function testDoesntProcessAllRowsWhenCounterSet()
    {
        $testFile = __DIR__ . '/files/useragents1.csv';

        $this->applicationTester->run(array(
            'command' => 'devicedetector:warmcache',
            'input-file' => $testFile,
            '--count' => 2
        ));

        $userAgents = array(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 12_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko'
        );

        $this->assertContains("Written 2 cache entries to file", $this->applicationTester->getDisplay());
        $userAgent3 = 'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko';
        $this->assertUserAgentNotWrittenToFile($userAgent3);
    }

    private function assertUserAgentNotWrittenToFile($userAgent)
    {
        $expectedFilePath = DeviceDetectorCacheEntry::getCachePath($userAgent);
        $this->assertFileNotExists($expectedFilePath);
    }

    private function assertUserAgentWrittenToFile($userAgent)
    {
        $expectedFilePath = DeviceDetectorCacheEntry::getCachePath($userAgent);
        $this->assertFileExists($expectedFilePath);

        DeviceDetectorFactory::clearInstancesCache();
        $deviceDetectionFromFile = DeviceDetectorFactory::getInstance($userAgent, true);
        DeviceDetectorFactory::clearInstancesCache();
        $deviceDetectionParsed = DeviceDetectorFactory::getInstance($userAgent, false);

        $this->assertInstanceOf("\Piwik\DeviceDetector\DeviceDetectorCacheEntry", $deviceDetectionFromFile);
        $this->assertInstanceOf("\DeviceDetector\DeviceDetector", $deviceDetectionParsed);
        $this->assertEquals($deviceDetectionParsed->getBot(), $deviceDetectionFromFile->getBot());
        $this->assertEquals($deviceDetectionParsed->getBrand(), $deviceDetectionFromFile->getBrand());
        $this->assertEquals($deviceDetectionParsed->getClient(), $deviceDetectionFromFile->getClient());
        $this->assertEquals($deviceDetectionParsed->getDevice(), $deviceDetectionFromFile->getDevice());
        $this->assertEquals($deviceDetectionParsed->getModel(), $deviceDetectionFromFile->getModel());
        $this->assertEquals($deviceDetectionParsed->getOs(), $deviceDetectionFromFile->getOs());
    }
}