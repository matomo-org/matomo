/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("WhatIsNew", function () {
    this.timeout(0);
    this.fixture = 'Piwik\\Tests\\Fixtures\\CreateChanges';

    before(function () {
      testEnvironment.optionsOverride = {
          loadChanges: '1'
      };

      testEnvironment.overrideConfig('General', {
        enable_internet_features: 0
      });

      testEnvironment.save();
    });

    it('should show the what is new changes popup', async function() {
        await page.goto('?module=CoreHome');
        await page.$('.whatisnew');
        await page.waitForTimeout(1000);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('what_is_new');
    });
});
