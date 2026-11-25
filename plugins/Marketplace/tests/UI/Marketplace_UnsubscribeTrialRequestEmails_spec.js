/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Marketplace_UnsubscribeTrialRequestEmails', function () {
  this.fixture = "Piwik\\Plugins\\Marketplace\\tests\\Fixtures\\SimpleFixtureTrackFewVisits";
  // unsubscribing turns the emails off for a fixture super user, so a persisted run would send none
  this.optionsOverride = {
    'persist-fixture-data': false,
  };

  const fs = require('fs');
  const mailDir = PIWIK_INCLUDE_PATH + '/tmp';
  const unsubscribeAction = 'unsubscribeTrialRequestEmails';

  // the test environment writes each sent email to tmp/<module>.<action>.mail.json
  function findCapturedMails() {
    return fs.readdirSync(mailDir)
      .filter((file) => file.endsWith('.mail.json'))
      .map((file) => mailDir + '/' + file)
      .filter((path) => fs.readFileSync(path, 'utf8').includes(unsubscribeAction));
  }

  function readUnsubscribeLinkFromMail() {
    const mails = findCapturedMails();
    if (mails.length !== 1) {
      throw new Error(`Expected one captured plugin request email, found ${mails.length}`);
    }

    const { contents } = JSON.parse(fs.readFileSync(mails[0], 'utf8'));
    const href = contents.match(new RegExp('href="([^"]*' + unsubscribeAction + '[^"]*)"'));
    if (!href) {
      throw new Error(`Could not find the unsubscribe link in the email: ${contents}`);
    }

    // the template escapes the href with e('html_attr'), which writes punctuation as hex entities
    return href[1]
      .replace(/&#x([0-9a-f]+);/gi, (entity, hex) => String.fromCharCode(parseInt(hex, 16)))
      .replace(/&amp;/g, '&');
  }

  before(function () {
    findCapturedMails().forEach((path) => fs.unlinkSync(path));

    testEnvironment.overrideConfig('General', 'enable_plugins_admin', '1');
    testEnvironment.consumer = 'validLicenseNoPlugins';
    testEnvironment.idSitesViewAccess = [1];
    testEnvironment.fakeIdentity = 'unsubscribeTrialRequestUser';
    testEnvironment.mockMarketplaceApiService = 1;
    testEnvironment.save();
  });

  after(function () {
    findCapturedMails().forEach((path) => fs.unlinkSync(path));

    delete testEnvironment.consumer;
    delete testEnvironment.fakeIdentity;
    delete testEnvironment.idSitesViewAccess;
    delete testEnvironment.mockMarketplaceApiService;
    testEnvironment.save();
  });

  it('should ask a super user to confirm unsubscribing from the plugin request email link', async function () {
    await page.goto('?module=API&method=Marketplace.requestTrial&pluginName=PaidPlugin1&format=json');

    await page.goto(readUnsubscribeLinkFromMail());
    await page.waitForSelector('.marketplaceUnsubscribePage .loginForm .submit', { visible: true });

    expect(await page.screenshot({ fullPage: true })).to.matchImage('unsubscribe_form');
  });

  it('should confirm the super user was unsubscribed', async function () {
    await page.click('.marketplaceUnsubscribePage .loginForm .submit');
    await page.waitForSelector('.marketplaceUnsubscribePage .loginForm p.message', { visible: true });

    const message = await page.$eval('.marketplaceUnsubscribePage .loginForm p.message', (el) => el.textContent);
    expect(message).to.equal('You have been successfully unsubscribed from plugin request email notifications.');
  });
});
