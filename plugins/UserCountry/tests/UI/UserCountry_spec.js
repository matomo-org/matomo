/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("UserCountry", function () {

    afterEach(function () {
        delete testEnvironment.unloadGeoIp2;
        testEnvironment.save();
    });

    it('should show geolocation admin without additional providers', async function () {
        testEnvironment.unloadGeoIp2 = 1;
        testEnvironment.save();

        await page.goto("?module=UserCountry&action=adminIndex");

        expect(await page.screenshotSelector('#content')).to.matchImage('admin_no_providers');
    });

    it('should show geolocation admin with GeoIP2 providers', async function () {
        testEnvironment.pluginsToLoad = ['GeoIp2'];
        testEnvironment.save();

        await page.goto("?module=UserCountry&action=adminIndex");

        expect(await page.screenshotSelector('#content')).to.matchImage('admin_geoip2');
    });

});
