/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("ReferrersPages", function () {

  var generalParams = 'idSite=1&period=year&date=2012-08-09',
    urlBaseGeneric = 'module=CoreHome&action=index&',
    urlBase = urlBaseGeneric + generalParams;

  before(function() {
    testEnvironment.ignoreClearAllViewDataTableParameters = 1;
    testEnvironment.save();
  });

  after(function() {
    delete testEnvironment.ignoreClearAllViewDataTableParameters;
    testEnvironment.save();
  });

  it('should load the referrers > overview page correctly', async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Referrers_Referrers&subcategory=General_Overview");
    await page.waitForNetworkIdle();

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('overview');
  });

  it("should display the another row when selected", async function () {
    await page.hover('.jqplot-seriespicker');

    await page.waitForSelector('.jqplot-seriespicker-popover .pickRow input');
    const element = await page.jQuery('.jqplot-seriespicker-popover .pickRow input:not(:checked):first');
    await element.click();
    await page.waitForNetworkIdle();
    await page.waitForTimeout(250);

    pageWrap = await page.$('#widgetReferrersgetEvolutionGraphforceView1viewDataTablegraphEvolutioncolumnsArray .theWidgetContent');
    expect(await pageWrap.screenshot()).to.matchImage('overview_another_row');
  });

  it('should show previously selected rows on reload', async function () {
    await page.reload();
    await page.waitForNetworkIdle();

    pageWrap = await page.$('#widgetReferrersgetEvolutionGraphforceView1viewDataTablegraphEvolutioncolumnsArray .theWidgetContent');
    expect(await pageWrap.screenshot()).to.matchImage('overview_reloaded');
  });

  it('should load the referrers > overview page correctly', async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Referrers_Referrers&subcategory=Referrers_WidgetGetAll");
    await page.waitForNetworkIdle();

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('allreferrers');
  });

  it('should display metric tooltip correctly', async function () {
    let elem = await page.jQuery('[data-report="Referrers.getReferrerType"] #nb_visits .thDIV');
    await elem.hover();

    let tip = await page.jQuery('.columnDocumentation:visible', {waitFor: true});

    // manipulate the styles a bit, as it's otherwise not visible on screenshot
    await page.evaluate(function () {
      var style = document.createElement('style');
      style.innerHTML = '.permadocs { display: block !important;z-index:150!important;margin-top:0!important; } .dataTable thead{ z-index:150 !important; }';
      $('body').append(style);

      //add index not overlap others
      $('.columnDocumentation:visible').addClass('permadocs');
    });

    await page.waitForTimeout(100);

    expect(await tip.screenshot()).to.matchImage({
      imageName: 'metric_tooltip',
      comparisonThreshold: 0.008
    });
  });

  it('should load the referrers > search engines & keywords page correctly', async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Referrers_Referrers&subcategory=Referrers_SubmenuSearchEngines");
    await page.waitForNetworkIdle();
    await page.mouse.move(-10, -10);

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('search_engines_keywords');
  });

  it('should load the referrers > websites correctly', async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Referrers_Referrers&subcategory=Referrers_SubmenuWebsitesOnly");
    await page.waitForNetworkIdle();
    await page.mouse.move(-10, -10);

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('websites');
  });

  it('should load the referrers > social page correctly', async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Referrers_Referrers&subcategory=Referrers_Socials");
    await page.waitForNetworkIdle();

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('socials');
  });

  it('should load the referrers > campaigns page correctly', async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Referrers_Referrers&subcategory=Referrers_Campaigns");
    await page.waitForNetworkIdle();

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('campaigns');
  });

  it('should load the referrers > ai assistants page correctly', async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Referrers_Referrers&subcategory=Referrers_AIAssistants");
    await page.waitForNetworkIdle();

    await (await page.jQuery('.subDataTable:eq(2) .label')).click();
    await page.mouse.move(-10, -10);
    await page.waitForNetworkIdle();

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('aiassistants');
  });

  it('should load the referrers > ai assistants with secondary dimension', async function () {
    const visibleSpan = await page.jQuery('.datatableRelatedReports li>span:visible');
    await visibleSpan.click();
    await page.waitForNetworkIdle();

    await (await page.jQuery('.subDataTable:eq(2) .label')).click();
    await page.mouse.move(-10, -10);
    await page.waitForNetworkIdle();

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('aiassistants_titles');
  });

  it('should flatten the referrers > ai assistants report correctly', async function () {
    await page.click('.dropdownConfigureIcon');
    await page.click('.dataTableFlatten');
    await page.waitForNetworkIdle();
    await page.mouse.move(-10, -10);

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('aiassistants_titles_flat');
  });

  it('switching to goals view should disable flattening as its not supported', async function () {
    await page.click('.dataTableHeaderControls .activateVisualizationSelection > span');
    await page.click('.dataTableHeaderControls .tableIcon[data-footer-icon-id=tableGoals]');
    await page.mouse.move(-10, -10);
    await page.waitForNetworkIdle();

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('aiassistants_titles_flat_goals');
  });
});
