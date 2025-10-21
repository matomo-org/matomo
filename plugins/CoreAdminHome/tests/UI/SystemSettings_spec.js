/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SystemSettings", function () {

    this.fixture = "Piwik\\Tests\\Fixtures\\EmptySite";

    var baseUrl = '?module=CoreAdminHome&action=generalSettings';

    async function screenshotPageWrap() {
        const pageWrap = await page.$('.pageWrap');
        const screenshot = await pageWrap.screenshot();
        return screenshot;
    }

    async function setCnilPolicyEnforced(enforced) {
        if (enforced) {
            testEnvironment.overrideConfig('FeatureFlags', 'PrivacyCompliance_feature', 'enabled');
            testEnvironment.overrideConfig('CnilPolicy', 'cnil_v1_policy_enabled', 1);
        } else {
            delete testEnvironment.configOverride.CnilPolicy;
            delete testEnvironment.configOverride.FeatureFlags;
        }
        await testEnvironment.save();
    }

    it('should load the system settings correctly', async function () {
        await page.goto(baseUrl);

        expect(await screenshotPageWrap()).to.matchImage('settings');
    });

    it('should display compliance info for policy controlled settings', async function () {
        await setCnilPolicyEnforced(true);
        await page.goto(baseUrl);
        await page.waitForNetworkIdle();
        await setCnilPolicyEnforced(false);

        expect(await screenshotPageWrap()).to.matchImage('compliance_info');
    });
});
