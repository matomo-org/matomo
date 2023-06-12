/*!
 * Matomo - free/libre analytics platform
 *
 * OneClickUpdate screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
  path = require('../../lib/screenshot-testing/support/path');

const request = require('request-promise');
const exec = require('child_process').exec;

describe("OneClickMajorUpdate", function () {
    this.fixture = "Piwik\\Tests\\Fixtures\\FirstPreviousMajorInstall";

    var latestStableUrl = config.piwikUrl + '/latestStableInstall/index.php';
    var settingsUrl = latestStableUrl + '?module=CoreAdminHome&action=home&idSite=1&period=day&date=yesterday';

    it('should show the new version available button in the admin screen', async function () {
        await page.goto(latestStableUrl);
        await page.waitForSelector('#login_form_login', { visible: true });

        await page.type('#login_form_login', superUserLogin);
        await page.type('#login_form_password', superUserPassword);
        await page.click('#login_form_submit');

        await page.waitForNetworkIdle();
        await page.waitForSelector('.pageWrap');

        await page.goto(settingsUrl);

        const element = await page.waitForSelector('#header_message', { visible: true });

        await page.waitForTimeout(250);

        expect(await element.screenshot()).to.matchImage('latest_version_available');
    });

    it('should show the one click update screen when the update button is clicked', async function () {
        await page.click('#header_message');

        await page.waitForNetworkIdle();
        await page.waitForSelector('.content');

        await page.evaluate(() => {
            $('.content').html($('.content').html().replace(/requires Matomo &lt;([0-9])\.[0-9]+\.[0-9]+(?:-(?:rc|b)[0-9]+)?/i, 'requires Matomo <$1.99.99'));
        });

        await page.waitForTimeout(100);

      expect(await page.screenshot({ fullPage: true })).to.matchImage('update_screen');
    });

    it('should fail to automatically update when trying to update over https fails', async function () {
        await page.click('#updateAutomatically');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.content');
        expect(await page.screenshot({ fullPage: true })).to.matchImage('update_fail');
    });

    it('should update successfully and show the finished update screen', async function () {
        await page.click('#updateUsingHttp');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.content');
        expect(await page.screenshot({ fullPage: true })).to.matchImage('update_success');
    });

    it('should login successfully after the update', async function () {
        await page.click('.footer a');
        await page.waitForNetworkIdle();

        // in case a db upgrade is required
        while (true) {
            const submitButton = await page.$('.content input[type=submit]');
            if (submitButton) {
                await submitButton.click();
                await page.waitForNetworkIdle();
                await page.waitForTimeout(250);

                const continueButton = await page.$('.footer a');
                if (continueButton) { // finish page might not be displayed if only one query is executed
                    await continueButton.click();
                    await page.waitForNetworkIdle();
                }
            } else {
                break;
            }
        }

        await page.waitForSelector('.site-without-data', { visible: true });
        await page.waitForNetworkIdle();
        const element  = await page.$('.site-without-data');
        expect(await element.screenshot()).to.matchImage('login');
    });

    it('should have a working cron archiving process', async function () {
        // track one action
        const trackerUrl = config.piwikUrl + "latestStableInstall/piwik.php?";

        await request({
            method: 'POST',
            uri: trackerUrl,
            form: {
                idsite: 1,
                url: 'http://piwik.net/test/url',
                action_name: 'test page',
            },
        });

        // run cron archiving
        const output = await new Promise((resolve, reject) => {
            exec(`${config.php} ${PIWIK_INCLUDE_PATH}/latestStableInstall/console --matomo-domain=${config.phpServer.HTTP_HOST} core:archive`, (error, stdout, stderr) => {
                const output = stdout.toString() + "\n" + stderr.toString();

                if (error) {
                    console.log(`core:archive failed, output: ${output}`);
                    reject(error);
                    return;
                }

                resolve(output);
            });
        });

        // check output has no errors
        expect(output).to.not.match(/ERROR|WARN/g);
    });
});
