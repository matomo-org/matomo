<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Plugins\SitesManager\SiteContentDetection\Cloudflare;
use Piwik\Plugins\SitesManager\SiteContentDetection\GoogleAnalytics3;
use Piwik\Plugins\SitesManager\SiteContentDetection\GoogleAnalytics4;
use Piwik\Plugins\SitesManager\SiteContentDetection\GoogleTagManager;
use Piwik\Plugins\SitesManager\SiteContentDetection\Joomla;
use Piwik\Plugins\SitesManager\SiteContentDetection\Osano;
use Piwik\Plugins\SitesManager\SiteContentDetection\ReactJs;
use Piwik\Plugins\SitesManager\SiteContentDetection\VueJs;
use Piwik\Plugins\SitesManager\SiteContentDetection\Wix;
use Piwik\Plugins\SitesManager\SiteContentDetection\Wordpress;
use Piwik\SiteContentDetector;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group SiteContentDetectorTest
*/
class SiteContentDetectorTest extends IntegrationTestCase
{
    public function test_detectsConsentManager_NotConnected()
    {
        $siteData = '<html lang="en"><head><title>A site</title></head><script src="https://osano.com/uhs9879874hthg.js"></script></head><body>A site</body></html>';

        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(Osano::getId()));
        self::assertNotContains(Osano::getId(), $scd->connectedContentManagers);
    }

    public function test_detectsConsentManager_Connected()
    {
        $siteData = "<html lang='en'><head><title>A site</title></head><script src='https://osano.com/uhs9879874hthg.js'></script><script>Osano.cm.addEventListener('osano-cm-consent-changed', (change) => { console.log('cm-change'); consentSet(change); });</script></><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(Osano::getId()));
        self::assertContains(Osano::getId(), $scd->connectedContentManagers);
    }

    public function test_detectsCMS_wordPress()
    {
        $siteData = "<html lang='en'><head><title>A site</title></head><script src='/wp-content/foo.cs'></script><body>A site<img src='/wp-content/plugins/foo'></body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(Wordpress::getId()));
    }

    public function test_detectsCMS_joomla()
    {
        $siteData = "<html lang='en'><head><title>A site</title></head><body></body></html>";
        $headers = ['expires' => 'Wed, 17 Aug 2005 00:00:00 GMT'];
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, $headers));

        self::assertTrue($scd->wasDetected(Joomla::getId()));
    }

    public function test_detectsCMS_wix()
    {
        $siteData = "<html lang='en'><head><title>A site</title></head><meta value='X-Wix-Published-Version'><body>A sit</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(Wix::getId()));
    }

    private function makeSiteResponse($data, $headers = [])
    {
        return ['data' => $data, 'headers' => $headers, 'status' => 200];
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
        $scd->detectContent([GoogleAnalytics3::getId()], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(GoogleAnalytics3::getId()));
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
        $scd->detectContent([GoogleAnalytics4::getId()], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(GoogleAnalytics4::getId()));
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
        $scd->detectContent([GoogleTagManager::getId()], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(GoogleTagManager::getId()));
    }

    public function test_doesNotDetectsGA_IfNotPresent()
    {
        $siteData = "<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertFalse($scd->wasDetected(GoogleAnalytics3::getId()));
        self::assertFalse($scd->wasDetected(GoogleAnalytics4::getId()));
        self::assertFalse($scd->wasDetected(GoogleTagManager::getId()));
    }

    public function test_detectCloudFlare_IfPresent()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, ['server' => 'cloudflare']));

        self::assertTrue($scd->wasDetected(Cloudflare::getId()));
    }

    public function test_detectCloudFlare_IfPresent2()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, ['Server' => 'cloudflare']));

        self::assertTrue($scd->wasDetected(Cloudflare::getId()));
    }

    public function test_detectCloudFlare_IfPresent3()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, ['SERVER' => 'cloudflare']));

        self::assertTrue($scd->wasDetected(Cloudflare::getId()));
    }

    public function test_detectCloudFlare_IfPresent4()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, ['cf-ray' => 'test']));

        self::assertTrue($scd->wasDetected(Cloudflare::getId()));
    }

    public function test_detectCloudFlare_IfPresent5()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, ['Cf-Ray' => 'test']));

        self::assertTrue($scd->wasDetected(Cloudflare::getId()));
    }

    public function test_detectCloudFlare_IfPresent6()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, ['CF-RAY' => 'test']));

        self::assertTrue($scd->wasDetected(Cloudflare::getId()));
    }

    public function test_doesNotDetectsCloudflare_IfNotPresent()
    {
        $siteData = "<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertFalse($scd->wasDetected(Cloudflare::getId()));
    }

    /**
     * @dataProvider provideVueTestData
     */
    public function test_detectVue($content, $result)
    {
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($content));

        self::assertSame($result, $scd->wasDetected(VueJs::getId()));
    }

    public function provideVueTestData()
    {
        return [
            ['node_modules/vue/dist/vue-develpment.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.prod.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.prod.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-browser.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-browser.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-browser.prod.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-browser.prod.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-bundler.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-bundler.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue-min.global.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.prod.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.prod.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-browser.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-browser.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-browser.prod.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-browser.prod.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-bundler.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-bundler.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.global.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.global.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.global.prod.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.global.prod.min.js', true],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vuetmp.runtime.global.prod.min.js', false],
            ['test content', false],
            ['test content vue', false],
        ];
    }
    
    public function test_detectReact_IfPresent()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>const root = ReactDOM.createRoot(container);</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(ReactJs::getId()));
    }

    public function test_detectReact_IfPresent2()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react.min.js'></script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(ReactJs::getId()));
    }

    public function test_detectReact_IfPresent3()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react.development.min.js'></script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(ReactJs::getId()));
    }

    public function test_detectReact_IfPresent4()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react-dom.development.min.js'></script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(ReactJs::getId()));
    }

    public function test_detectReact_IfPresent5()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react.development.js'></script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(ReactJs::getId()));
    }

    public function test_detectReact_IfPresent6()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react-dom.development.js'></script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertTrue($scd->wasDetected(ReactJs::getId()));
    }

    public function test_doesNotDetectsReact_IfNotPresent()
    {
        $siteData = "<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        self::assertFalse($scd->wasDetected(ReactJs::getId()));
    }
}
