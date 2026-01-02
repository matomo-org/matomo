/*!
 * Matomo - free/libre analytics platform
 *
 * WidgetLoader screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('WidgetLoader', function () {
  this.fixture = "Piwik\\Tests\\Fixtures\\OneVisit";
  before(function () {
    testEnvironment.testUseMockAuth = 0;
    testEnvironment.save();
  });

  after(function () {
    testEnvironment.testUseMockAuth = 1;
    testEnvironment.save();
  });

  it('should redirect to the landing page when the session cookie is cleared during widget loading', async function () {
    // We try to do an actual login
    await page.goto("");
    await page.type("#login_form_login", superUserLogin);
    await page.type("#login_form_password", superUserPassword);
    await page.click('#login_form_submit');
    await page.waitForNetworkIdle();

    // check dashboard is shown
    await page.waitForSelector('#dashboard');
    expect(await page.$('#dashboard')).to.be.ok;
    await page.clearCookies();

    //Click on Dashboard menu item
    const dashboardMenuSelector = 'div.reportingMenu ul li[data-category-id="Dashboard_Dashboard"] ul li:nth-child(1) a';
    await page.click(dashboardMenuSelector);
    await page.waitForNetworkIdle();

    const loginForm = await page.waitForSelector('#login_form_login');
    expect(loginForm).to.be.ok;

    const errorNotification = await page.waitForSelector('div.system.notification-error');
    expect(errorNotification).to.be.ok;

    const expectedText = 'Error: Your session has expired due to inactivity. Please log in to continue.';
    const notificationText = await page.$eval('div.system.notification-error .notification-body', el => el.textContent.trim());
    expect(notificationText).to.equal(expectedText);

  });
});
