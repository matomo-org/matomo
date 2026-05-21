/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("GoalsPages", function () {
  var generalParams = 'idSite=1&period=year&date=2012-08-09',
    urlBaseGeneric = 'module=CoreHome&action=index&',
    urlBase = urlBaseGeneric + generalParams;
  const findSparkline = async function (text, childSelector = null) {
    await page.waitForFunction((sparklineText, selector) => {
      const sparkline = window.$('.sparkline').filter((index, element) => {
        return window.$(element).text().toLowerCase().includes(sparklineText);
      }).first();

      if (!sparkline.length) {
        return false;
      }

      return selector ? !!sparkline.find(selector).length : true;
    }, {}, text, childSelector);

    return page.evaluateHandle((sparklineText, selector) => {
      const sparkline = window.$('.sparkline').filter((index, element) => {
        return window.$(element).text().toLowerCase().includes(sparklineText);
      }).first();

      return selector ? sparkline.find(selector).get(0) : sparkline.get(0);
    }, text, childSelector);
  };

  const trackRequests = async function (action) {
    const requests = [];
    const requestHandler = (request) => {
      requests.push({
        resourceType: request.resourceType(),
        url: request.url(),
      });
    };

    page.webpage.on('request', requestHandler);
    try {
      await action();
    } finally {
      page.webpage.removeListener('request', requestHandler);
    }

    return requests;
  };

  // goals pages
  it('should load the goals > ecommerce page correctly', async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Goals_Ecommerce&subcategory=General_Overview")
    await page.waitForNetworkIdle();

    expect(await page.screenshotSelector('.pageWrap')).to.matchImage('ecommerce');
  });

  it('should show the correct relative data for the revenue in-cart tooltip', async function() {
    var monthParams = 'idSite=1&period=month&date=2012-01-09';
    await page.goto("?" + urlBase + "#?" + monthParams + "&category=Goals_Ecommerce&subcategory=General_Overview");
    await page.waitForNetworkIdle();
    const element = await findSparkline('left in cart', '.metricEvolution:last');
    await element.hover();
    const tooltip = await page.waitForSelector('.ui-tooltip', { visible: true });
    expect(await tooltip.screenshot()).to.matchImage('revenue_incart_tooltip');
  });

  it('should show the selected last year comparison period in an ecommerce sparkline tooltip', async function() {
    var compareMonthParams = 'idSite=1&period=month&date=2012-01-09&compareDates[]=2011-01-01&comparePeriods[]=month';
    await page.goto("?" + urlBaseGeneric + compareMonthParams + "#?" + compareMonthParams + "&category=Goals_Ecommerce&subcategory=General_Overview");
    await page.waitForNetworkIdle();

    const element = await findSparkline('left in cart', '.metricEvolution:last');
    await element.hover();
    await page.waitForSelector('.ui-tooltip', { visible: true });
    const tooltipContent = await page.evaluate(() => $('.ui-tooltip:visible').text());

    expect(tooltipContent).to.contain('January 2012');
    expect(tooltipContent).to.contain('January 2011');
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
    const elem = await findSparkline('conversion rate');
    await elem.click();
    await page.waitForNetworkIdle();
    await page.mouse.move(-10, -10);

    expect(await page.screenshotSelector('.pageWrap')).to.matchImage('individual_goal_updated');
  });

  it('should include the abandoned cart goal in ecommerce abandoned cart sparkline links', async function () {
    var monthParams = 'idSite=1&period=month&date=2012-01-09';
    await page.goto("?" + urlBase + "#?" + monthParams + "&category=Goals_Ecommerce&subcategory=General_Overview");
    await page.waitForNetworkIdle();

    const sparklineImage = await findSparkline('left in cart', 'img');
    const dataSrc = await page.evaluate((element) => element.getAttribute('data-src'), sparklineImage);

    expect(dataSrc).to.contain('idGoal=ecommerceAbandonedCart');

    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Goals_Goals&subcategory=1");
    await page.waitForNetworkIdle();
  });

  it('should reload the main evolution graph with the abandoned cart goal when an abandoned cart sparkline is clicked', async function () {
    var monthParams = 'idSite=1&period=month&date=2012-01-09';
    await page.goto("?" + urlBase + "#?" + monthParams + "&category=Goals_Ecommerce&subcategory=General_Overview");
    await page.waitForNetworkIdle();

    const requests = await trackRequests(async () => {
      const sparkline = await findSparkline('left in cart');
      await sparkline.click();
      await page.waitForNetworkIdle();
    });

    const evolutionGraphRequest = requests.find((request) => {
      return request.resourceType === 'xhr'
        && request.url.indexOf('module=Goals') !== -1
        && request.url.indexOf('action=getEvolutionGraph') !== -1
        && request.url.indexOf('idGoal=ecommerceAbandonedCart') !== -1;
    });

    expect(evolutionGraphRequest).to.be.ok;

    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Goals_Goals&subcategory=1");
    await page.waitForNetworkIdle();
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
