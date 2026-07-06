/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('AIAgents', function () {
  this.fixture = 'Piwik\\Plugins\\AIAgents\\tests\\Fixtures\\AIAgents';

  const url = '?module=CoreHome&action=index&category=General_AIAssistants&subcategory=AIAgents_AIAgentsOverview&idSite=1&period=day&date=2025-07-20';

  it('should display the AI agents overview', async function () {
    await page.goto(url);
    await page.waitForNetworkIdle();

    const widgets = await page.$$('.matomo-widget');
    expect(widgets.length).to.equal(2);
  });

  it('should show the AI assistants report menu items', async function () {
    expect(await page.screenshotSelector('.menuTab.active')).to.matchImage('menu');
  });

  it('should display the list of supported evolution metrics', async function () {
    // the picker options are always present in the DOM (the dropdown is only hidden
    // until the toggle is clicked), so the checked state can be read without opening it
    const selectedMetrics = await page.$$('.metrics-picker__options input:checked');
    expect(selectedMetrics.length).to.equal(1);

    const selectedMetricLabel = await page.$('.metrics-picker__options input:checked ~ .metrics-picker__title');
    expect(await selectedMetricLabel.getProperty('textContent')).to.match(/AI Agent Visits/);
  });

  it('should allow changing displayed metric using sparklines', async function () {
    const sparklines = await page.$$('.sparkline.linked');

    expect(sparklines.length).to.equal(10);
    await sparklines[5].click();
    await page.waitForNetworkIdle();

    const selectedMetrics = await page.$$('.metrics-picker__options input:checked');
    expect(selectedMetrics.length).to.equal(1);

    const selectedMetricLabel = await page.$('.metrics-picker__options input:checked ~ .metrics-picker__title');
    expect(await selectedMetricLabel.getProperty('textContent')).to.match(/Human Visits/);
  });

  it('should allow selecting multiple metrics', async function () {
    let metricLabels;

    // add "AI Agent Visits" (the dropdown must be open to click an option label)
    await page.click('.metrics-picker__toggle');
    await page.waitForSelector('.metrics-picker__options label');
    metricLabels = await page.$$('.metrics-picker__options label');

    await metricLabels[0].click();
    await page.waitForNetworkIdle();

    // add "Visits"
    await page.click('.metrics-picker__toggle');
    await page.waitForSelector('.metrics-picker__options label');
    metricLabels = await page.$$('.metrics-picker__options label');

    await metricLabels[12].click();
    await page.waitForNetworkIdle();

    // check three metrics are selected/visible (picker rebuilt closed after the
    // last selection, so the checked count can be read without reopening it)
    const selectedMetrics = await page.$$('.metrics-picker__options input:checked');
    expect(selectedMetrics.length).to.equal(3);

    await page.mouse.move(-10, -10);

    expect(await page.screenshotSelector('#content')).to.matchImage('overview');
  });
});
