<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\SiteContentDetector;

/**
 * @group Core
 * @group SiteContentDetectorTest
*/
class SiteContentDetectorTest extends \PHPUnit\Framework\TestCase
{

    public function test_detectsConsentManager_NotConnected()
    {
        $siteData = '<html lang="en"><head><title>A site</title></head><script src="https://osano.com/uhs9879874hthg.js"></script></head><body>A site</body></html>';

        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $siteData);

        $this->assertEquals('osano', $scd->consentManagerId);
        $this->assertFalse($scd->isConnected);
    }

    public function test_detectsConsentManager_Connected()
    {
        $siteData = "<html lang='en'><head><title>A site</title></head><script src='https://osano.com/uhs9879874hthg.js'></script><script>Osano.cm.addEventListener('osano-cm-consent-changed', (change) => { console.log('cm-change'); consentSet(change); });</script></><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $siteData);

        $this->assertEquals('osano', $scd->consentManagerId);
        $this->assertTrue($scd->isConnected);
    }

    public function test_detectsGA3_IfPresent()
    {
        $siteData = "<html lang=\"en\"><head><title>A site</title></head><script><script>
                     (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                     (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                     m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                     })(window,document,'script','//xxxxxx/analytics.js','ga');
                     ga('create', 'UA-xxxxxxxx-x', 'xxxxxx.com');
                     ga('send', 'pageview');
                     </script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::GA3], null, $siteData);

        $this->assertEmpty($scd->consentManagerId);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertTrue($scd->ga3);
    }

    public function test_detectsGA4_IfPresent()
    {
        $siteData = "<html lang=\"en\"><head></head><title>A site</title></head><script><script>
                     <!-- Google tag (gtag.js) -->
                    <script async src='https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID'></script>
                    <script>window.dataLayer = window.dataLayer || [];
                            function gtag(){window.dataLayer.push(arguments);}
                            gtag('js', new Date());
                            gtag('config', 'GA_TRACKING_ID');
                    </script>
                    </head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::GA4], null, $siteData);

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->gtm);
        $this->assertTrue($scd->ga4);
    }

    public function test_detectsGTM_IfPresent()
    {
        $siteData = "<html lang=\"en\"><head><title>A site</title></head>
                     <!-- Google Tag Manager -->
                     <script type='hash84759fa843b-text/javascript'>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                     new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                     j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                     'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                     })(window,document,'script','dataLayer','GTM-NRTVJJC');</script>
                     <!-- End Google Tag Manager -->                     
                     </head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::GTM], null, $siteData);

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertTrue($scd->gtm);
    }

    public function test_doesNotDetectsGA_IfNotPresent()
    {
        $siteData = "<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $siteData);

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
    }

}
