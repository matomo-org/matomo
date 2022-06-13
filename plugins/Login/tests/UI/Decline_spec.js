/*!
 * Matomo - free/libre analytics platform
 *
 * login & password reset screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('Decline', function () {
  this.timeout(0);
  this.fixture = 'Piwik\\Plugins\\Login\\tests\\Fixtures\\PendingUsers';

  var pendingUserUrl = '?module=Login&action=declineInvitation&token=13cb9dcef6cc70b02a640cee30dc8ce9';



  it('should display decline invite page', async function () {
    await page.goto(pendingUserUrl);
    expect(await page.screenshot({ fullPage: true })).to.matchImage('default');
  });

  it('should display decline success page', async function () {
    await page.evaluate(function(){
      $('#login_form_submit').click();
    });
    expect(await page.screenshot({ fullPage: true })).to.matchImage('success');
  });

});