<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\tests\System;

use Piwik\Piwik;
use Piwik\Plugins\Tour\Engagement\ChallengeSetupConsentManager;
use Piwik\SiteContentDetector;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group ConsentManagerDetectionTest
 * @group TourTest
 * @group Plugins
 */
class ConsentManagerDetectionTest extends SystemTestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_detectConsentManager_disableWhenNotDetected()
    {
        $siteData = [];
        $challenge = new ChallengeSetupConsentManager(new SiteContentDetector(), $siteData);
        $this->assertTrue($challenge->isDisabled());
    }

    public function test_detectConsentManager_detectedButNotConnected()
    {
        $siteData = $this->makeSiteResponse('<html><head><script src="https://osano.com/uhs9879874hthg.js"></script></head><body>A site</body></html>');
        $challenge = new ChallengeSetupConsentManager(new SiteContentDetector(), $siteData);
        $this->assertFalse($challenge->isDisabled());
        $this->assertFalse($challenge->isCompleted(Piwik::getCurrentUserLogin()));
        $this->assertEquals('osano', $challenge->getConsentManagerId());
    }

    public function test_detectConsentManager_detectedAndConnected()
    {
        $siteData = $this->makeSiteResponse("<html><head><script src='https://osano.com/uhs9879874hthg.js'></script><script>Osano.cm.addEventListener('osano-cm-consent-changed', (change) => { console.log('cm-change'); consentSet(change); });</script></head><body>A site</body></html>");
        $challenge = new ChallengeSetupConsentManager(new SiteContentDetector(), $siteData);
        $this->assertFalse($challenge->isDisabled());
        $this->assertTrue($challenge->isCompleted(Piwik::getCurrentUserLogin()));
        $this->assertEquals('osano', $challenge->getConsentManagerId());
    }

    private function makeSiteResponse($data, $headers = [])
    {
        return ['data' => $data, 'headers' => $headers, 'status' => 200];
    }

}
