/*!
 * Matomo - free/libre analytics platform
 *
 * Accept invitation UI tests
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Invite', function () {
  this.fixture = 'Piwik\\Plugins\\Login\\tests\\Fixtures\\PendingUsers';
  this.optionsOverride = {
    'persist-fixture-data': false
  };

  var pendingUserUrl = '?module=Login&action=acceptInvitation&token=13cb9dcef6cc70b02a640cee30dc8ce9';
  var wrongUserUrl = '?module=Login&action=acceptInvitation&token=123';

  // The panel only renders once the changes model is the real one - the test environment otherwise
  // swaps in FakeChangesModel, which reads back no changes. Restored in a finally so a failure here
  // cannot leak the panel into the baselines below.
  async function withWhatsNewPanel(assertions) {
    testEnvironment.loadChanges = 1;
    testEnvironment.save();

    try {
      await assertions();
    } finally {
      delete testEnvironment.loadChanges;
      testEnvironment.save();
    }
  }

  it('should display error page', async function (){
    await page.goto(wrongUserUrl);
    expect(await page.screenshot({ fullPage: true })).to.matchImage('error');
  });

  // Runs before the tests below consume the invite token. This is the tallest form sharing the login
  // layout, so it is the one worth verifying next to the panel.
  it('should display set password page beside the What\'s New panel', async function () {
    await withWhatsNewPanel(async function () {
      // The error page above leaves a 3s redirect timer pending, which otherwise aborts the
      // navigation below. Same guard loginUser() uses in the TwoFactorAuth spec.
      await page.goto('about:blank');

      await page.goto(pendingUserUrl);
      await page.waitForSelector('.loginWhatsNew__entry');
      expect(await page.screenshot({ fullPage: true })).to.matchImage('set_password_whats_new');
    });
  });

  it('should display set password page', async function () {
    await page.goto(pendingUserUrl);
    expect(await page.screenshot({ fullPage: true })).to.matchImage('set_password');
  });

  it('password confirmation error', async function () {
    await page.type('#password', 'abcd1234');
    await page.type('#password_confirm', 'abcd123');
    await page.evaluate(function(){
      $('#login_form_submit').click();
    });
    await page.waitForNetworkIdle();
    expect(await page.screenshot({ fullPage: true })).to.matchImage('wrong_password');
  });

  it('it should login success', async function () {
    await page.type('#password', 'abcd1234');
    await page.type('#password_confirm', 'abcd1234');
    await page.evaluate(function(){
      $('#conditionCheck').prop('checked', true);
      $('#login_form_submit').click();
    });
    // should show site without data page
    await page.waitForSelector('#site-without-data', {visible: true});
    await page.evaluate(() => window.stop()); // stop ongoing requests
  });
});
