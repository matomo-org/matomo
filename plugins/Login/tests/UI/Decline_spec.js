/*!
 * Matomo - free/libre analytics platform
 *
 * Decline invitation UI tests
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Decline', function () {
  this.fixture = 'Piwik\\Plugins\\Login\\tests\\Fixtures\\PendingUsers';
  this.optionsOverride = {
    'persist-fixture-data': false
  };

  var pendingUserUrl = '?module=Login&action=declineInvitation&token=13cb9dcef6cc70b02a640cee30dc8ce9';

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

  // Runs before the test below declines the invitation and spends the token.
  it('should display decline invite page beside the What\'s New panel', async function () {
    await withWhatsNewPanel(async function () {
      await page.goto(pendingUserUrl);
      await page.waitForSelector('.loginWhatsNew__entry');
      expect(await page.screenshot({ fullPage: true })).to.matchImage('default_whats_new');
    });
  });

  it('should display decline invite page', async function () {
    await page.goto(pendingUserUrl);
    expect(await page.screenshot({ fullPage: true })).to.matchImage('default');
  });

  it('should display decline success page', async function () {
    await page.evaluate(function(){
      $('#login_form_submit').click();
    });
    await page.waitForNetworkIdle();
    expect(await page.screenshot({ fullPage: true })).to.matchImage('success');
  });
});
