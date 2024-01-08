/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("EmptySite", function () {

  this.fixture = "Piwik\\Tests\\Fixtures\\EmptySite";

  const generalParams = 'idSite=1&period=day&date=2010-01-03';
  const urlToTest = "?" + generalParams + "&module=CoreHome&action=index";

  before(function () {
    testEnvironment.detectedContentDetections = [];
    testEnvironment.connectedConsentManagers = [];
    testEnvironment.save();
  });

  after(function () {
    // unset all detections so fake class is no longer used
    delete testEnvironment.detectedContentDetections;
    delete testEnvironment.connectedConsentManagers;
    testEnvironment.save();
  });

  async function makeTrackingCodeStatic() {
    await page.waitForNetworkIdle();
    await page.evaluate(function () {
      // ensure hostname and container id is always the same
      var selector = $('.codeblock, [vue-directive="CoreHome.CopyToClipboard"]');
      selector.each(function(){
        let $elem = $(this);
        $elem.text($elem.text().replace(/u="\/\/(.*)";/g, 'u="//localhost/tests/PHPUnit/proxy/";'));
        $elem.text($elem.text().replace(/host='http\/\/(.*)',/g, 'host=\'http://localhost/tests/PHPUnit/proxy/\','));
        $elem.text($elem.text().replace(/http(.*)container_(.*).js/g, 'http://localhost/js/container_test123.js'));
        $elem.text($elem.text().replace(new RegExp('https?://' + document.location.host + '/'), 'http://localhost/'));
      });
    });
  }

  it('should initially show the no data page overview', async function () {
    await page.goto(urlToTest);
    await page.waitForSelector('#start-tracking-method-list'); // wait till list is shown

    const pageElement = await page.$('.page');
    expect(await pageElement.screenshot()).to.matchImage('emptySiteDashboard');
  });

  it('should show the tracking code when selected', async function () {
    await page.evaluate(() => $('#start-tracking-detection a[href="#matomo"]')[0].click());

    // wait till url check field is filled with data, which means loading has finished.
    await page.waitForFunction(() => $('#baseUrl').val());
    await makeTrackingCodeStatic();

    const pageElement = await page.$('.page');
    expect(await pageElement.screenshot()).to.matchImage('trackingCode');
  });

  it('should show the advanced tracking options when clicked', async function () {
    await page.evaluate(() => $('.advance-option a').click());

    // wait till checkbox isn't disabled anymore, which means loading has finished.
    await page.waitForFunction(() => !$('#javascript-tracking-all-subdomains').is(':disabled'));

    const pageElement = await page.$('.page');
    expect(await pageElement.screenshot()).to.matchImage('showAdvancedTrackingOptions');
  });

  it('should hide the advanced tracking options when clicked', async function () {
    await page.evaluate(() => $('.advance-option a').click());

    await page.waitForSelector('#javascript-advanced-options', {hidden: true, timeout: 1000});
  });

  it('should show SPA/PWA details when clicked', async function () {
    await page.click('#start-tracking-back');
    await page.evaluate(() => $('#start-tracking-method-list a[href="#spapwa"]')[0].click());

    await makeTrackingCodeStatic();

    const pageElement = await page.$('.page');
    expect(await pageElement.screenshot()).to.matchImage('spa_pwa');
  });

  it('should show the Other methods when clicked', async function () {
    await page.click('#start-tracking-back');
    await page.evaluate(() => $('#start-tracking-method-list a[href="#other"]')[0].click());
    await makeTrackingCodeStatic();

    const pageElement = await page.$('.page');
    expect(await pageElement.screenshot()).to.matchImage('other');
  });

  it('should show the google tag manager details when clicked', async function () {
    await page.click('#start-tracking-back');
    await page.evaluate(() => $('#start-tracking-method-list a[href="#googletagmanager"]')[0].click());
    await makeTrackingCodeStatic();

    const pageElement = await page.$('.page');
    expect(await pageElement.screenshot()).to.matchImage('gtm');
  });

  it('should show the wordpress details when clicked', async function () {
    await page.click('#start-tracking-back');
    await page.evaluate(() => $('#start-tracking-method-list a[href="#wordpress"]')[0].click());

    // wait till url check field is filled with data, which means loading has finished.
    await page.waitForFunction(() => $('#baseUrl').val());
    await makeTrackingCodeStatic();

    const pageElement = await page.$('.page');
    expect(await pageElement.screenshot()).to.matchImage('wordpress');
  });

  it('should show the vue js details when clicked', async function () {
    await page.click('#start-tracking-back');
    await page.evaluate(() => $('#start-tracking-method-list a[href="#vuejs"]')[0].click());
    await makeTrackingCodeStatic();

    const pageElement = await page.$('.page');
    expect(await pageElement.screenshot()).to.matchImage('vuejs');
  });

  it('should directly show details when provided as url param', async function () {
    testEnvironment.detectedContentDetections = ['GoogleTagManager', 'WordPress'];
    testEnvironment.connectedConsentManagers = [];
    testEnvironment.save();

    await page.goto(urlToTest + "#?" + generalParams + "&activeTab=integrations");
    await page.waitForSelector('#start-tracking-details'); // wait till details ar shown
    await makeTrackingCodeStatic();

    const pageElement = await page.$('.page');
    expect(await pageElement.screenshot()).to.matchImage('integrations');
  });

  // take one full screenshot when a detected method is shown
  it('should suggest wordpress method when detected, other detections should be shown first', async function () {
    testEnvironment.detectedContentDetections = ['WordPress', 'VueJs'];
    testEnvironment.connectedConsentManagers = [];
    testEnvironment.save();

    await page.goto('about:blank');
    await page.goto(urlToTest);
    await page.waitForSelector('#start-tracking-method-list'); // wait till list is shown

    const pageElement = await page.$('.page');
    expect(await pageElement.screenshot()).to.matchImage('detected_wordpress');
  });

  // only take shots from recommended method for other detections
  it('should prefer gtm method over others when detected', async function () {
    testEnvironment.detectedContentDetections = ['GoogleTagManager', 'WordPress', 'VueJs'];
    testEnvironment.connectedConsentManagers = [];
    testEnvironment.save();

    await page.goto('about:blank');
    await page.goto(urlToTest);
    await page.waitForSelector('#start-tracking-method-list'); // wait till list is shown

    const pageElement = await page.$('#start-tracking-detection');
    expect(await pageElement.screenshot()).to.matchImage('detected_gtm');
  });

  it('should should show a notification on the tracking code screen when a consent manager is detected', async function () {
    testEnvironment.detectedContentDetections = ['Osano'];
    testEnvironment.connectedConsentManagers = ['Osano'];
    testEnvironment.save();

    await page.goto('about:blank');
    await page.goto(urlToTest);
    await page.waitForSelector('#start-tracking-method-list'); // wait till list is shown

    await page.evaluate(() => $('#start-tracking-detection a[href="#matomo"]')[0].click());

    // wait till url check field is filled with data, which means loading has finished.
    await page.waitForFunction(() => $('#baseUrl').val());
    await makeTrackingCodeStatic();

    const pageElement = await page.$('.page');
    expect(await pageElement.screenshot()).to.matchImage('detected_osano');
  });


  it.skip('should have button to send tracking code to developer', async function () {
    var mailtoLink = await page.$eval('.emailTrackingCode', link => link.getAttribute('href'));

    // Check that it's a mailto link with correct subject line
    expect(mailtoLink).to.include('mailto:?subject=Matomo%20Analytics%20Tracking%20Code&body');
    // Check that template rendered and only contains chars that are OK in all mail clients (e.g. no HTML at all)
    expect(mailtoLink).to.match(/^mailto:\?[a-zA-Z0-9&%=.,_*()\[\]'"-]*$/);
  });

  it('should be possible to ignore this screen for one hour', async function () {
    await page.goto('about:blank');
    await page.goto(urlToTest);

    await page.click('.ignoreSitesWithoutData');
    await page.waitForSelector('#dashboardWidgetsArea');
    await page.waitForNetworkIdle();

    // ensure dashboard widgets are loaded
    const widgetsCount = await page.evaluate(() => $('.widget').length);
    expect(widgetsCount).to.be.greaterThan(1);
  });
});
