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

  // Tests only see change data when they ask for it - the test environment otherwise swaps in
  // FakeChangesModel, which reads back nothing. Restored in a finally so a failure here cannot leak
  // the panel into the baselines below.
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

  function measurePanelBesideForm() {
    const de = document.documentElement;
    const primary = document.querySelector('.loginLayout__primary').getBoundingClientRect();
    const panel = document.querySelector('.loginWhatsNew').getBoundingClientRect();

    return {
      entries: document.querySelectorAll('.loginWhatsNew__entry').length,
      panelRightOfForm: panel.left >= primary.right,
      panelVisible: panel.width > 0 && panel.height > 0,
      noOverflow: de.scrollWidth <= de.clientWidth,
    };
  }

  // Runs before the test below declines the invitation and spends the token. The panel has its own
  // baseline in LoginWhatsNew, so this only checks the decline page sits beside it. The viewport is
  // deliberately left alone - setViewport would persist into the baselines below.
  it('should display decline invite page beside the What\'s New panel', async function () {
    await withWhatsNewPanel(async function () {
      await page.goto(pendingUserUrl);
      await page.waitForSelector('.loginWhatsNew__entry');

      expect(await page.evaluate(measurePanelBesideForm)).to.deep.equal({
        entries: 3, panelRightOfForm: true, panelVisible: true, noOverflow: true,
      });
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
