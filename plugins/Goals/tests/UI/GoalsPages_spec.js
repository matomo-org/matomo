/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("GoalsPages", function () {
  this.timeout(5*60*1000); // timeout of 5 minutes per test

  var generalParams = 'idSite=1&period=year&date=2012-08-09',
    urlBaseGeneric = 'module=CoreHome&action=index&',
    urlBase = urlBaseGeneric + generalParams;

  // goals pages
  it('should load the goals > ecommerce page correctly', async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Goals_Ecommerce&subcategory=General_Overview")
    await page.waitForNetworkIdle();

    expect(await page.screenshotSelector('.pageWrap')).to.matchImage('ecommerce');
  });

  it('should load the goals > overview page correctly', async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Goals_Goals&subcategory=General_Overview");
    await page.waitForNetworkIdle();

    expect(await page.screenshotSelector('.pageWrap')).to.matchImage('overview');
  });

  it('should load row evolution with goal metrics', async function() {
    const row = await page.waitForSelector('.reportsByDimensionView tbody tr:first-child');
    await row.hover();

    const icon = await page.waitForSelector('.reportsByDimensionView tbody tr:first-child a.actionRowEvolution');
    await icon.click();

    await page.waitForSelector('.ui-dialog');
    await page.waitForNetworkIdle();

    const dialog = await page.$('.ui-dialog');
    expect(await dialog.screenshot()).to.matchImage('overview_row_evolution');
  });

  it('should load row evolution with goal metrics again when reloading the page url', async function() {
    // page.reload() won't work with url hashes
    const url = await page.evaluate('location.href');
    await page.goto('about:blank');
    await page.goto(url);

    await page.waitForSelector('.ui-dialog');
    await page.waitForNetworkIdle();
    await page.waitForTimeout(200);

    const dialog = await page.$('.ui-dialog');
    expect(await dialog.screenshot()).to.matchImage('overview_row_evolution_reloaded');
  });

  it('should load the goals > management page correctly', async function () {
    await page.goto("?" + generalParams + "&module=Goals&action=manage");
    await page.waitForNetworkIdle();

    expect(await page.screenshotSelector('#content,.top_bar_sites_selector,.entityContainer')).to.matchImage('manage');
  });

  it('should load the goals > single goal page correctly', async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Goals_Goals&subcategory=1");
    await page.waitForNetworkIdle();

    expect(await page.screenshotSelector('.pageWrap')).to.matchImage('individual_goal');
  });

  it('should update the evolution chart if a sparkline is clicked', async function () {
    elem = await page.jQuery('.sparkline.linked:contains(conversion rate)');
    await elem.click();
    await page.waitForNetworkIdle();
    await page.mouse.move(-10, -10);

    expect(await page.screenshotSelector('.pageWrap')).to.matchImage('individual_goal_updated');
  });

  // should load the row evolution [see #11526]
  it('should show rov evolution for goal tables', async function () {
    await page.waitForNetworkIdle();

    const row = await page.waitForSelector('.dataTable tbody tr:first-child');
    await row.hover();

    const icon = await page.waitForSelector('.dataTable tbody tr:first-child a.actionRowEvolution');
    await icon.click();

    await page.waitForSelector('.rowevolution');
    await page.waitForNetworkIdle();

    expect(await page.screenshotSelector('.ui-dialog')).to.matchImage('individual_row_evolution');
  });

  it('should load row evolution with goal metrics again when reloading the page url', async function() {
    // page.reload() won't work with url hashes
    const url = await page.evaluate('location.href');
    await page.goto('about:blank');
    await page.goto(url);

    await page.waitForSelector('.ui-dialog');
    await page.waitForNetworkIdle();
    await page.waitForTimeout(200);

    const dialog = await page.$('.ui-dialog');
    expect(await dialog.screenshot()).to.matchImage('individual_row_evolution_reloaded');
  });
});
