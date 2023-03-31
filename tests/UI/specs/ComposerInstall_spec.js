/*!
 * Matomo - free/libre analytics platform
 *
 * OneClickUpdate screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const request = require('request-promise');
const exec = require('child_process').exec;

describe('ComposerInstall', function () {
  this.fixture = "Piwik\\Tests\\Fixtures\\ComposerInstall";
  this.optionsOverride = {
    // ensure install is clean up after the tests
    'persist-fixture-data': false
  };

  const composerInstallDir = 'composerInstall';
  const composerInstallUrl = config.piwikUrl + composerInstallDir;
  const reportingUrl = composerInstallUrl + '/index.php';
  const trackingUrl = config.piwikUrl + composerInstallDir + '/matomo.php?rec=1&idsite=1&url=' + encodeURIComponent('http://testsite.com') + '&action_name=' + encodeURIComponent('test action');

  it('should load the reporting UI', async () => {
    await page.goto(reportingUrl);
    await page.waitForSelector('#login_form_login', { visible: true });

    await page.type('#login_form_login', superUserLogin);
    await page.type('#login_form_password', superUserPassword);
    await page.click('#login_form_submit');

    await page.waitForNetworkIdle();
    await page.waitForSelector('.pageWrap');

    expect(await page.screenshot({ fullPage: true })).to.matchImage('main_ui');
  });

  it('should track a request', async () => {
    let response = await request({
      uri: trackingUrl,
    });

    expect(response).to.equal(Buffer.from('R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==', 'base64').toString('utf-8'));
  });

  it('should run core:archive', async () => {
    await page.goto('about:blank'); // required or puppeteer keeps handling request errors

    const result = await new Promise((resolve) => {
      exec(`${config.php} ${PIWIK_INCLUDE_PATH}/${composerInstallDir}/console core:archive`, (err, stdout, stderr) => {
        resolve({ stdout, stderr });
      });
    });

    expect(result.stderr).to.equal('');
    expect(result.stdout).to.have.string('Reports for today will be processed at most every');
    expect(result.stdout).to.have.string('Start processing');
    expect(result.stdout).to.have.string('Processed 4 archives');
  });
});
