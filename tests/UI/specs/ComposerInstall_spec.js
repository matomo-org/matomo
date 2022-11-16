/*!
 * Matomo - free/libre analytics platform
 *
 * OneClickUpdate screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const request = require('request-promise');
const util = require('node:util');
const exec = util.promisify(require('node:child_process').exec);

describe('ComposerInstall', function () {
  this.fixture = "Piwik\\Tests\\Fixtures\\ComposerInstall";

  const composerInstallDir = 'composerInstall';
  const composerInstallUrl = config.piwikUrl + composerInstallDir + '/index.php';
  const reportingUrl = composerInstallUrl + '?module=CoreHome&action=index&idSite=1&period=day&date=yesterday';
  const trackingUrl = composerInstallUrl + 'matomo.php?rec=1&idsite=1&url=' + encodeURIComponent('http://testsite.com') + '&action_name=' + encodeURIComponent('test action');

  it('should load the reporting UI', async () => {
    await page.goto(reportingUrl);
    await page.waitForSelector('#login_form_login', { visible: true });

    await page.type('#login_form_login', 'superUserLogin');
    await page.type('#login_form_password', 'superUserPass');
    await page.click('#login_form_submit');

    await page.waitForNetworkIdle();
    await page.waitForSelector('.pageWrap');

    const element = await page.$('.pageWrap');
    expect(await element.screenshot()).to.matchImage('main_ui');
  });

  it('should track a request', async () => {
    let response = await request({
      uri: trackingUrl,
    });

    expect(response).to.equal(Buffer.from('R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==', 'base64').toString('utf-8'));
  });

  it('should run core:archive', async () => {
    const command = `cd ${PIWIK_INCLUDE_PATH}/${composerInstallDir} && php ./console core:archive`;
    const result = await exec(command);

    expect(result).to.deep.equal({
      stdout: '',
      stderr: '',
    });
  });
});
