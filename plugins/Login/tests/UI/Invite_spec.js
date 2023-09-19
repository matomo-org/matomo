/*!
 * Matomo - free/libre analytics platform
 *
 * Accept invitation UI tests
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Invite', function () {
  this.timeout(0);
  this.fixture = 'Piwik\\Plugins\\Login\\tests\\Fixtures\\PendingUsers';
  this.optionsOverride = {
    'persist-fixture-data': false
  };

  var pendingUserUrl = '?module=Login&action=acceptInvitation&token=13cb9dcef6cc70b02a640cee30dc8ce9';
  var wrongUserUrl = '?module=Login&action=acceptInvitation&token=123';

  it('should display error page', async function (){
    await page.goto(wrongUserUrl);
    expect(await page.screenshot({ fullPage: true })).to.matchImage('error');
  });

  it('should display set password page', async function () {
    await page.goto(pendingUserUrl);
    expect(await page.screenshot({ fullPage: true })).to.matchImage('set_password');
  });

  it('password confirmation error', async function () {
    await page.type('#password', 'abcd1234');
    await page.type('#password_confirm', 'abcd123');
    await page.evaluate(function(){
      $('#login_form_submit').click();
    });
    await page.waitForNetworkIdle();
    expect(await page.screenshot({ fullPage: true })).to.matchImage('wrong_password');
  });

  it('it should login success', async function () {
    await page.type('#password', 'abcd1234');
    await page.type('#password_confirm', 'abcd1234');
    await page.evaluate(function(){
      $('#conditionCheck').prop('checked', true);
      $('#login_form_submit').click();
    });
    // should show site without data page
    await page.waitForSelector('#site-without-data', {visible: true});
    await page.evaluate(() => window.stop()); // stop ongoing requests
  });
});
