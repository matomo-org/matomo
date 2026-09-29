/*!
 * Matomo - free/libre analytics platform
 *
 * UI tests for the links that open a plugin's Marketplace details from outside the Marketplace.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Marketplace_PluginDetailsLinks', function () {
    this.fixture = 'Piwik\\Plugins\\Marketplace\\tests\\Fixtures\\SimpleFixtureTrackFewVisits';

    before(function () {
        testEnvironment.overrideConfig('General', 'enable_plugins_admin', '1');
        testEnvironment.consumer = 'noLicense';
        testEnvironment.mockMarketplaceApiService = 1;
        testEnvironment.save();
    });

    async function waitForDetailsPage()
    {
        await page.waitForSelector('.marketplacePluginDetails__content', { visible: true });
    }

    it('should send the old popover URL on to the details page', async function () {
        // the plugin details used to open in a popover; bookmarks and older links still carry it
        await page.goto('?module=CoreAdminHome&action=home&idSite=1&period=day&date=yesterday'
            + '&popover=browsePluginDetail%243APaidPlugin1');

        await waitForDetailsPage();

        const url = await page.url();
        expect(url).to.contain('module=Marketplace');
        expect(url).to.contain('action=overview');
        expect(url).to.contain('showPlugin=PaidPlugin1');
        expect(url).to.not.contain('popover=');
        expect(await page.$('.ui-dialog .pluginDetails')).to.equal(null);

        const title = await page.evaluate(() => $('.marketplacePluginDetails__title').text().trim());
        expect(title).to.equal('Paid Plugin 1');
    });

    it('should link a plugin in the plugin management table to its details page', async function () {
        await page.goto('?module=CorePluginsAdmin&action=plugins&idSite=1&period=day&date=yesterday');

        const link = await page.waitForSelector('#plugins td.name a[matomo-plugin-name]', { visible: true });
        const href = await link.evaluate((element) => element.getAttribute('href'));
        expect(href).to.contain('module=Marketplace');
        expect(href).to.contain('action=overview');
        expect(href).to.contain('#?showPlugin=');

        await link.click();
        await waitForDetailsPage();

        expect(await page.$('.ui-dialog .pluginDetails')).to.equal(null);
    });
});
