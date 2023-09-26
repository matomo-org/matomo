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
    this.optionsOverride = {
      'persist-fixture-data': false
    };

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
        await page.goto('');
        await page.waitForSelector('.whatisnew', {visible:true});
        await page.waitForNetworkIdle();

        const popup = await page.$('.what-is-new-popup');
        expect(await popup.screenshot()).to.matchImage('what_is_new');
    });

    it('should show a badge with count in menu', async function() {
        await page.click('.ui-dialog-titlebar-close');
        await page.waitForSelector('.ui-widget-overlay', {hidden: true});
        const menu = await page.$('.nav-wrapper .right');
        expect(await menu.screenshot()).to.matchImage('menu');
    });
});
