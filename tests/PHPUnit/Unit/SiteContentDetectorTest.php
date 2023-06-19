<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Plugins\SitesManager\SitesManager;
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
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertEquals('osano', $scd->consentManagerId);
        $this->assertFalse($scd->isConnected);
    }

    public function test_detectsConsentManager_Connected()
    {
        $siteData = "<html lang='en'><head><title>A site</title></head><script src='https://osano.com/uhs9879874hthg.js'></script><script>Osano.cm.addEventListener('osano-cm-consent-changed', (change) => { console.log('cm-change'); consentSet(change); });</script></><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertEquals('osano', $scd->consentManagerId);
        $this->assertTrue($scd->isConnected);
    }

    public function test_detectsCMS_wordPress()
    {
        $siteData = "<html lang='en'><head><title>A site</title></head><script src='/wp-content/foo.cs'></script><body>A site<img src='/wp-content/plugins/foo'></body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertEquals('wordpress', $scd->cms);
    }

    public function test_detectsCMS_joomla()
    {
        $siteData = "<html lang='en'><head><title>A site</title></head><body></body></html>";
        $headers = ['expires' => 'Wed, 17 Aug 2005 00:00:00 GMT'];
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, $headers));

        $this->assertEquals('joomla', $scd->cms);
    }

    public function test_detectsCMS_wix()
    {
        $siteData = "<html lang='en'><head><title>A site</title></head><meta value='X-Wix-Published-Version'><body>A sit</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertEquals('wix', $scd->cms);
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
        $scd->detectContent([SiteContentDetector::GA3], null, $this->makeSiteResponse($siteData));

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
        $scd->detectContent([SiteContentDetector::GA4], null, $this->makeSiteResponse($siteData));

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
        $scd->detectContent([SiteContentDetector::GTM], null, $this->makeSiteResponse($siteData));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertTrue($scd->gtm);
    }

    public function test_doesNotDetectsGA_IfNotPresent()
    {
        $siteData = "<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
    }

    public function test_detectCloudFlare_IfPresent()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, ['server' => 'cloudflare']));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertTrue($scd->cloudflare);
    }

    public function test_detectCloudFlare_IfPresent2()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, ['Server' => 'cloudflare']));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertTrue($scd->cloudflare);
    }

    public function test_detectCloudFlare_IfPresent3()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, ['SERVER' => 'cloudflare']));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertTrue($scd->cloudflare);
    }

    public function test_detectCloudFlare_IfPresent4()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, ['cf-ray' => 'test']));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertTrue($scd->cloudflare);
    }

    public function test_detectCloudFlare_IfPresent5()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, ['Cf-Ray' => 'test']));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertTrue($scd->cloudflare);
    }

    public function test_detectCloudFlare_IfPresent6()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData, ['CF-RAY' => 'test']));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertTrue($scd->cloudflare);
    }

    public function test_doesNotDetectsCloudflare_IfNotPresent()
    {
        $siteData = "<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertFalse($scd->cloudflare);
    }

    /**
     * @dataProvider provideVueTestData
     */
    public function test_detectVue($content, $output)
    {
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($content));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertFalse($scd->cloudflare);
        $this->assertEquals($output, $scd->jsFramework);

    }

    public function provideVueTestData()
    {
        return [
            ['node_modules/vue/dist/vue-develpment.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.prod.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.prod.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-browser.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-browser.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-browser.prod.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-browser.prod.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-bundler.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-bundler.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue-min.global.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.prod.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.prod.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-browser.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-browser.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-browser.prod.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-browser.prod.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-bundler.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-bundler.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.global.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.global.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.global.prod.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.global.prod.min.js', 'vue'],
            ['https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vuetmp.runtime.global.prod.min.js', 'unknown'],
            ['test content', 'unknown'],
            ['test content vue', 'unknown'],
        ];
    }
    
    public function test_detectReact_IfPresent()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>const root = ReactDOM.createRoot(container);</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertFalse($scd->cloudflare);
        $this->assertEquals('react', $scd->jsFramework);
    }

    public function test_detectReact_IfPresent2()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react.min.js'></script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertFalse($scd->cloudflare);
        $this->assertEquals('react', $scd->jsFramework);
    }

    public function test_detectReact_IfPresent3()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react.development.min.js'></script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertFalse($scd->cloudflare);
        $this->assertEquals('react', $scd->jsFramework);
    }

    public function test_detectReact_IfPresent4()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react-dom.development.min.js'></script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertFalse($scd->cloudflare);
        $this->assertEquals('react', $scd->jsFramework);
    }

    public function test_detectReact_IfPresent5()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react.development.js'></script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertFalse($scd->cloudflare);
        $this->assertEquals('react', $scd->jsFramework);
    }

    public function test_detectReact_IfPresent6()
    {
        $siteData = "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://localhost.com/js/react-dom.development.js'></script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertFalse($scd->cloudflare);
        $this->assertEquals('react', $scd->jsFramework);
    }

    public function test_doesNotDetectsReact_IfNotPresent()
    {
        $siteData = "<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script></head><body>A site</body></html>";
        $scd = new SiteContentDetector();
        $scd->detectContent([SiteContentDetector::ALL_CONTENT], null, $this->makeSiteResponse($siteData));

        $this->assertFalse($scd->ga3);
        $this->assertFalse($scd->ga4);
        $this->assertFalse($scd->gtm);
        $this->assertFalse($scd->cloudflare);
        $this->assertEquals(SitesManager::JS_FRAMEWORK_UNKNOWN, $scd->jsFramework);
    }
}
