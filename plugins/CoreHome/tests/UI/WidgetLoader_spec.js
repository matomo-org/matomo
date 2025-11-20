/*!
 * Matomo - free/libre analytics platform
 *
 * WidgetLoader screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('WidgetLoader', function () {
  this.timeout(0);

  this.fixture = "Piwik\\Tests\\Fixtures\\OneVisit";

  before(function () {
      testEnvironment.testUseMockAuth = 0;
      testEnvironment.save();
    });

  it('should redirect to the landing page when the session cookie is cleared during widget loading', async function () {
    // We try to do an actual login
    await page.goto("");
    await page.type("#login_form_login", superUserLogin);
    await page.type("#login_form_password", superUserPassword);
    await page.evaluate(function(){
      $('#login_form_submit').click();
    });
    await page.waitForNetworkIdle();
    // check dashboard is shown
    await page.waitForSelector('#dashboard');
    expect(await page.$('#dashboard')).to.be.ok;
    await page.clearCookies();

    //Click on Dashboard menu item
    await page.click('div.reportingMenu ul li[data-category-id="Dashboard_Dashboard"] ul li:nth-child(1) a');
    await page.waitForNetworkIdle();

    const screenshot = await page.screenshot();
    expect(screenshot).to.matchImage('not_logged_in');
  });
});
