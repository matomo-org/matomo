/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("UserCountry", function () {

    afterEach(function () {
        delete testEnvironment.pluginsToUnload;
        testEnvironment.save();
    });

    it('should show geolocation admin without additional providers', async function () {
        testEnvironment.pluginsToUnload = ['GeoIp2'];
        testEnvironment.save();

        await page.goto("?module=UserCountry&action=adminIndex");

        expect(await page.screenshotSelector('#content')).to.matchImage('admin_no_providers');
    });

    it('should show geolocation admin with GeoIP2 providers', async function () {
        testEnvironment.pluginsToLoad = ['GeoIp2', 'Provider'];
        testEnvironment.save();

        await page.goto("?module=UserCountry&action=adminIndex");

        await page.evaluate(function(){
            $('#geoipdb-update-info').html($('#geoipdb-update-info').html().replace(/dbip-city-lite-[\d]{4}-[\d]{2}\.mmdb\.gz</, 'dbip-city-lite-2020-04.mmdb.gz<'));
        });

        expect(await page.screenshotSelector('#content')).to.matchImage('admin_geoip2');
    });

    it('should show geolocation admin with GeoIP2 providers (without Provider plugin)', async function () {
        testEnvironment.pluginsToLoad = ['GeoIp2'];
        testEnvironment.pluginsToUnload = ['Provider'];
        testEnvironment.save();

        await page.reload();

        await page.evaluate(function(){
            $('#geoipdb-update-info').html($('#geoipdb-update-info').html().replace(/dbip-city-lite-[\d]{4}-[\d]{2}\.mmdb\.gz</, 'dbip-city-lite-2020-04.mmdb.gz<'));
        });

        expect(await page.screenshotSelector('#content')).to.matchImage('admin_geoip2_no_provider');
    });

});
