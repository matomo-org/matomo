/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("FeedbackQuestion", function () {
  this.timeout(0);
  this.fixture = "Piwik\\Plugins\\Feedback\\tests\\Fixtures\\FeedbackQuestionBannerFixture";

  var url = "?module=CoreHome&action=index&idSite=1&period=day&date=2019-07-11&forceFeedbackTest=1";

  before(async function() {
    await page.webpage.setViewport({
      width: 1250,
      height: 768
    });
  });

  it('should display question banner when next reminder date is in past', async function () {
    await page.goto(url);
    await page.waitForNetworkIdle();

    var banner = await page.waitForSelector('.trialHeader', { visible: true });
    expect(await banner.screenshot()).to.matchImage('feedback_banner');
  });

  it('should display popup when banner button is clicked', async function () {

  });

  it('should show close popup cancel button is clicked', async function () {

  });

  it('should show close popup cancel button is clicked', async function () {

  });

  it('should remove banner when close button is clicked', async function () {

  });



});
