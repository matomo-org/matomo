/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    path = require('../../../../tests/lib/screenshot-testing/support/path');

describe("PluginUpdate", function () {
    this.fixture = "Piwik\\Tests\\Fixtures\\EmptySite";

    const pluginName = 'TestPluginUpdate';
    const installedPluginPath = path.join(PIWIK_INCLUDE_PATH, '/plugins/', pluginName);

    function removeInstalledPlugin() {
        fs.rmSync(installedPluginPath, { recursive: true, force: true });
    }

    async function uploadPlugin(version) {
        await page.goto('?module=CorePluginsAdmin&action=plugins&idSite=1&period=day&date=yesterday');
        await page.waitForSelector('.uploadPlugin');
        await page.click('.uploadPlugin');
        await page.waitForSelector('#uploadPluginForm', { visible: true });

        const zip = path.join(PIWIK_INCLUDE_PATH, `/plugins/CorePluginsAdmin/tests/resources/${pluginName}-${version}.zip`);
        await (await page.$('#uploadPluginForm input[name=pluginZip]')).uploadFile(zip);
        await page.type('#uploadPluginForm input[name=confirmPassword]', superUserPassword);

        await Promise.all([
            page.waitForNavigation(),
            page.click('#uploadPluginForm .startUpload'),
        ]);
    }

    async function getSuccessMessage() {
        return page.evaluate(() => $('.pageWrap p:contains("successfully installed")').text().trim());
    }

    before(function () {
        removeInstalledPlugin();

        testEnvironment.overrideConfig('General', 'enable_plugin_upload', 1);
        testEnvironment.save();
    });

    after(function () {
        removeInstalledPlugin();

        delete testEnvironment.mockMarketplaceApiService;
        delete testEnvironment.mockMarketplacePluginUpdate;
        delete testEnvironment.forceEnablePluginUpdateChecks;
        delete testEnvironment.pluginsToLoad;
        testEnvironment.save();
    });

    it('should install a plugin from an uploaded zip', async function () {
        await uploadPlugin('1.0.0');

        expect(await getSuccessMessage()).to.equal(`You have successfully installed the plugin ${pluginName} 1.0.0.`);
    });

    // the upload request still has the old plugin classes loaded, so rendering the result in it called
    // the old main class from a new class (the CrashAnalytics "Update selected" fatal)
    it('should show the result of updating a loaded plugin using the new plugin code', async function () {
        testEnvironment.pluginsToLoad = [pluginName];
        testEnvironment.save();

        await uploadPlugin('2.0.0');

        expect(await getSuccessMessage()).to.equal(`You have successfully installed the plugin ${pluginName} 2.0.0.`);
        expect(await page.evaluate(() => window.testPluginUpdateVersion)).to.equal(2);
    });

    it('should show the result of updating a loaded plugin from the Marketplace using the new plugin code', async function () {
        await uploadPlugin('1.0.0');
        expect(await getSuccessMessage()).to.equal(`You have successfully installed the plugin ${pluginName} 1.0.0.`);

        testEnvironment.forceEnablePluginUpdateChecks = 1;
        testEnvironment.mockMarketplaceApiService = 1;
        testEnvironment.mockMarketplacePluginUpdate = {
            name: pluginName,
            version: '2.0.0',
            zip: `plugins/CorePluginsAdmin/tests/resources/${pluginName}-2.0.0.zip`,
        };
        testEnvironment.save();

        await page.goto('?module=CorePluginsAdmin&action=plugins&idSite=1&period=day&date=yesterday');
        await page.waitForSelector(`#select-plugin-${pluginName}`);
        await page.evaluate((name) => $(`#select-plugin-${name}`).click(), pluginName);

        await Promise.all([
            page.waitForNavigation(),
            page.click('#update-selected-plugins'),
        ]);

        const successMessage = await page.evaluate(() => $('.pageWrap p:contains("successfully updated")').text().trim());
        expect(successMessage).to.equal(`You have successfully updated the plugin ${pluginName} 2.0.0.`);
        expect(await page.evaluate(() => window.testPluginUpdateVersion)).to.equal(2);
    });
});
