/*!
 * Matomo - free/libre analytics platform
 *
 * Clicks a point of an evolution graph and checks the url broadcast.propagateNewPage() navigates to.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('BroadcastPropagateNewPage', function () {
  const baseUrl = '?module=Widgetize&action=iframe&moduleToWidgetize=VisitsSummary&actionToWidgetize=getEvolutionGraph'
    + '&idSite=1&period=day&date=2012-01-31';

  // a segment value with characters that have a special meaning in urls and regular expressions
  const segment = 'browserCode==xx|moduleToWidgetize=VisitsSummary&|zz[]=';

  async function clickGraphAndGetNewUrl() {
    const graph = await page.waitForSelector('.piwik-graph');
    await page.waitForNetworkIdle();

    const boundingBox = await graph.boundingBox();
    const x = boundingBox.x + boundingBox.width / 2;
    const y = boundingBox.y + boundingBox.height / 2;

    // the click handler needs the tick under the pointer, which is set on mouse move
    await page.mouse.move(x, y);
    await Promise.all([
      page.waitForNavigation(),
      page.mouse.click(x, y),
    ]);

    return new URL(page.url());
  }

  it('should keep the current url parameters when the segment contains special characters', async function () {
    await page.goto(baseUrl + '&segment=' + encodeURIComponent(segment));

    const newUrl = await clickGraphAndGetNewUrl();

    expect(newUrl.searchParams.get('moduleToWidgetize')).to.equal('VisitsSummary');
    expect(newUrl.searchParams.get('actionToWidgetize')).to.equal('getEvolutionGraph');
    expect(newUrl.searchParams.getAll('segment')).to.deep.equal([segment]);
    expect(newUrl.searchParams.getAll('segment[]')).to.deep.equal([]);
  });
});
