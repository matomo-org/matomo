/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('FeedbackQuestion', function () {
  this.timeout(0);
  this.fixture = 'Piwik\\Plugins\\Feedback\\tests\\Fixtures\\FeedbackQuestionBannerFixture';

  var url = '?module=CoreHome&action=index&idSite=1&period=day&date=2019-07-11&forceFeedbackTest=1';

  before(async function () {
    await page.webpage.setViewport({
      width: 1250,
      height: 768,
    });
  });

  it('should display question banner', async function () {
    await page.goto(url);
    await page.waitForNetworkIdle();

    var banner = await page.waitForSelector('.trialHeader', { visible: true });
    expect(await banner.screenshot()).to.matchImage('feedback_banner');
  });

  it('should display popup when banner button is clicked', async function () {
    await page.click('.trialHeader .btn');
    await page.waitForNetworkIdle();

    var popup = await page.waitForSelector('.modal', { visible: true });
    expect(await popup.screenshot()).to.matchImage('feedback_popup');
  });

  it('should show error when blank content submit', async function () {
    await page.click('.modal .modal-footer a:nth-child(1)');
    var popup = await page.waitForSelector('.modal', { visible: true });
    expect(await popup.screenshot()).to.matchImage('feedback_failed');
  });

  it('should show success when banner is submit', async function () {
    await page.type('#message', 'test');
    await page.click('.modal .modal-footer a:nth-child(1)');
    await page.waitForNetworkIdle();
    var popup = await page.waitForSelector('.modal', { visible: true });
    expect(await popup.screenshot()).to.matchImage('feedback_success');
  });
});
