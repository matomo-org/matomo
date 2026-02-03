/*!
 * Matomo - free/libre analytics platform
 *
 * SegmentEditor Compare tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('SegmentComparison', () => {
  var generalParams = 'idSite=1&period=year&date=2012-08-09';
  it('should not allow comparing segments more than the limit set', async function() {
    const configLimit = 2;
    const maxSegments = configLimit + 1;
    testEnvironment.overrideConfig('General', 'data_comparison_segment_limit', configLimit);
    await testEnvironment.save();
    const dashUrl = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=Dashboard_Dashboard&subcategory=1'
    // Need to reload here since overrideConfig above does not really
    // reflect well when the config is used in javascript
    // start a fresh navigation so the new config is injected
    await page.goto('about:blank');
    await page.waitForNetworkIdle();
    await page.goto(dashUrl);
    await page.waitForNetworkIdle();
    // We grab the max limit message
    const maxLimitMessage = await page.evaluate(
      (limit) => _pk_translate('General_MaximumNumberOfSegmentsComparedIs', [limit]),
      maxSegments);

    // We check that the title attribute is still not the max limit message
    let title = await page.$eval('.segmentationContainer .segmentList li:last-child', (el) => el.getAttribute('title'));
    expect(title).to.not.equal(maxLimitMessage);

    await page.waitForSelector('.segmentationContainer');
    // We check that the number of <li> elements is greater than the limit we set
    const liElemLength = await page.$$eval('.segmentListContainer .segmentList li', (e) => e.length);
    expect(liElemLength).to.be.greaterThan(maxSegments);

    // We check that the number of segments compared is 1 at the start
    let comparedCount = await page.$$eval(
      '.segmentListContainer .segmentList li.comparedSegment',
      (nodes) => nodes.length,
    );
    expect(comparedCount).to.equal(1);

    // Making sure that the list is closed initially before the loop starts
    const segmentListIsExpanded = await page.evaluate(() => !!document.querySelector('.segmentEditorPanel.expanded'));
    if (segmentListIsExpanded) {
      await page.click('.segmentationContainer .title');
      await page.waitForTimeout(100);
    }

    // We want to click all the segments so that we can check that it stops at the limit
    for (let i=0; i<liElemLength; i++) {
      await page.click('.segmentationContainer .title');
      await page.waitForTimeout(100);
      const elements = await page.$$('.segmentListContainer .segmentList li button.compareSegment');
      if (!elements[i]) break;
      await elements[i].click();
      await page.waitForTimeout(100);
    }

    // We check that the number of segments compared is now equal to the limit we set
    comparedCount = await page.$$eval(
      '.segmentListContainer .segmentList li.comparedSegment',
      (nodes) => nodes.length,
    );
    expect(comparedCount).to.equal(maxSegments);
  });
});
