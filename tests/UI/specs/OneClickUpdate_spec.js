/*!
 * Matomo - free/libre analytics platform
 *
 * OneClickUpdate screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
  path = require('../../lib/screenshot-testing/support/path');

const request = require('request-promise');
const exec = require('child_process').exec;

describe("OneClickUpdate", function () {
    this.fixture = "Piwik\\Tests\\Fixtures\\LatestStableInstall";

    const latestStableUrl = config.piwikUrl + '/latestStableInstall/index.php';
    const settingsUrl = latestStableUrl + '?module=CoreAdminHome&action=home&idSite=1&period=day&date=yesterday';

    async function openHttpsFailureScreen() {
        // Recreate the HTTPS failure state directly so the rest of the test
        // does not depend on browser history or transport-specific behavior.
        await page.evaluate((oneClickResultsUrl) => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = oneClickResultsUrl;

            [
                ['httpsFail', '1'],
                ['error', 'Simulated SSL certificate failure'],
                ['messages', 'a:0:{}'],
            ].forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }, latestStableUrl + '?module=CoreUpdater&action=oneClickResults');

        await page.waitForNetworkIdle();
        await page.waitForSelector('#updateUsingHttp', { visible: true });
    }

    before(async function () {
        // The updater page loads helper code that may register shortcuts before
        // Mousetrap is available in this screenshot-test environment.
        await page.evaluateOnNewDocument(() => {
            window.Mousetrap = window.Mousetrap || {
                bind: () => {},
                trigger: () => {},
            };
        });
    });

    it('should show the new version available button in the admin screen', async function () {
        await page.goto(latestStableUrl);
        await page.waitForNetworkIdle();
        await page.waitForSelector('#login_form_login', { visible: true });

        await page.type('#login_form_login', superUserLogin);
        await page.type('#login_form_password', superUserPassword);
        await page.evaluate(function(){
          $('#login_form_submit').click();
        });

        await page.waitForNetworkIdle();
        await page.waitForSelector('.pageWrap');

        await page.goto(settingsUrl);
        await page.waitForNetworkIdle();

        const element = await page.waitForSelector('#header_message', { visible: true });
        expect(await element.screenshot()).to.matchImage('latest_version_available');
    });

    it('should show the one click update screen when the update button is clicked', async function () {
        await page.click('#header_message');

        await page.waitForNetworkIdle();
        await page.waitForSelector('.content');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('update_screen');
    });

    it('should fail to automatically update when trying to update over https fails', async function () {
        await openHttpsFailureScreen();
        expect(await page.$('#updateUsingHttp')).to.be.ok;
        expect(await page.$('#updateUsingHttps')).to.be.ok;
        expect(await page.$('.alert-warning')).to.be.ok;
    });

    it('should fail when a directory is not writable', async function () {
        await openHttpsFailureScreen();
        // Force the updater to hit the writable-directory error path.
        fs.chmodSync(path.join(PIWIK_INCLUDE_PATH, '/latestStableInstall/core'), 0o555);
        await page.click('#updateUsingHttp');
        await page.waitForSelector('.alert-danger', { visible: true });
        const heading = await page.$eval('.header h1', node => node.textContent);
        expect(heading).to.match(/update error/i);
        expect(await page.$('.alert-danger')).to.be.ok;
        expect(await page.$('.footer a')).to.be.ok;
    });

    it('should update successfully and show the finished update screen', async function () {
        await openHttpsFailureScreen();
        // Restore permissions so the same flow can complete successfully.
        fs.chmodSync(path.join(PIWIK_INCLUDE_PATH, '/latestStableInstall/core'), 0o777);
        await page.click('#updateUsingHttp');
        await page.waitForSelector('.footer a', { visible: true });
        const heading = await page.$eval('.header h1', node => node.textContent);
        expect(heading).to.match(/successfully updated/i);
        expect(await page.$('.footer a')).to.be.ok;
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

        // avoid taking an unnecessary screenshot, as knowing we land on #site-without-data is enough
        await page.waitForSelector('#site-without-data', { visible: true });
        await page.evaluate(() => window.stop()); // stop ongoing requests
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
