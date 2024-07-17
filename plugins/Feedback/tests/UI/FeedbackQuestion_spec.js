/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('FeedbackQuestion', function () {
  this.timeout(5*60*1000); // timeout of 5 minutes per test
  this.fixture = 'Piwik\\Plugins\\Feedback\\tests\\Fixtures\\FeedbackQuestionBannerFixture';

  var url = '?module=CoreHome&action=index&idSite=1&period=day&date=2019-07-11&forceFeedbackTest=1';

  it('should display question banner', async function () {
    await page.setCookie({
      name: 'feedback-question',
      value: '0',
      expire: '3600',
      'url': 'http://localhost/tests/PHPUnit/proxy/' + url,
    });
    await page.goto(url);
    await page.waitForNetworkIdle();

    var banner = await page.waitForSelector('.bannerHeader', { visible: true });
    expect(await banner.screenshot()).to.matchImage('feedback_banner');
  });

  it('should display popup when banner button is clicked', async function () {
    await page.click('.bannerHeader .btn');
    await page.waitForNetworkIdle();

    var popup = await page.waitForSelector('.modal', { visible: true });
    expect(await popup.screenshot()).to.matchImage('feedback_popup');
  });

  it('should show error when blank content submit', async function () {
    await page.click('.modal .modal-footer a:nth-child(1)');
    await page.waitForNetworkIdle();
    var popup = await page.waitForSelector('.modal.open', { visible: true });
    expect(await popup.screenshot()).to.matchImage('feedback_failed');
  });

  it('should show success when banner is submit', async function () {
    await page.type('#message', 'test content, do not send emails');
    await page.click('.modal .modal-footer a:nth-child(1)');
    await page.waitForNetworkIdle();
    var popup = await page.waitForSelector('.modal.open', { visible: true });
    expect(await popup.screenshot()).to.matchImage('feedback_success');
  });
});
