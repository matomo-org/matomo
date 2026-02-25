/*!
 * Matomo - free/libre analytics platform
 *
 * SegmentEditor screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SegmentManagementPageTest", function () {
  this.fixture = 'Piwik\\Tests\\Fixtures\\OneVisitorTwoVisits';

  var generalParams = 'idSite=1&period=range&date=2010-03-06,2010-03-08';
  var url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Visitors&subcategory=CoreHome_Segments';
  var globalSegmentId;
  var siteSegmentId;
  var globalSegmentName = 'UI Test Global Segment';
  var siteSegmentName = 'UI Test Site Segment';
  var segmentDefinition = 'visitCount>=1';

  before(async function () {
    testEnvironment.configOverride.General = {
      browser_archiving_disabled_enforce: '1',
      enable_browser_archiving_triggering: '0',
    };
    testEnvironment.optionsOverride = {
      enableBrowserTriggerArchiving: '0',
    };
    testEnvironment.save();

    const globalSegmentResult = await testEnvironment.callApi('SegmentEditor.add', {
      name: globalSegmentName,
      definition: segmentDefinition,
      idSite: 0,
      autoArchive: 1,
      enableAllUsers: 1,
    });

    const siteSegmentResult = await testEnvironment.callApi('SegmentEditor.add', {
      name: siteSegmentName,
      definition: segmentDefinition,
      idSite: 1,
      autoArchive: 1,
      enableAllUsers: 1,
    });

    globalSegmentId = extractSegmentId(globalSegmentResult);
    siteSegmentId = extractSegmentId(siteSegmentResult);

    testEnvironment.configOverride.General = {
      browser_archiving_disabled_enforce: '0',
      enable_browser_archiving_triggering: '1',
    };
    testEnvironment.optionsOverride = {
      enableBrowserTriggerArchiving: '1',
    };
    testEnvironment.save();

    await testEnvironment.callApi('VisitsSummary.get', {
      idSite: 1,
      period: 'range',
      date: '2010-03-06,2010-03-08',
      segment: segmentDefinition,
    });
  });

  after(async function () {
    if (globalSegmentId) {
      await testEnvironment.callApi('SegmentEditor.delete', { idSegment: globalSegmentId });
    }
    if (siteSegmentId) {
      await testEnvironment.callApi('SegmentEditor.delete', { idSegment: siteSegmentId });
    }
  });

  it("should load correctly", async function() {
    await page.goto(url);
    await page.waitForNetworkIdle();

    expect(await page.screenshot({ fullPage: true })).to.matchImage('initial');
  });

  it("should show the correct edit tooltip for global vs site segments", async function() {
    await page.goto(url);
    await page.waitForNetworkIdle();

    const globalTitle = await page.evaluate((id) => {
      const $editButton = $(`[data-edit-segment="${id}"]`);
      return $editButton.data('ui-tooltip-title') || $editButton.attr('title') || '';
    }, globalSegmentId);
    expect(globalTitle).to.contain('global segment');

    const siteTitle = await page.evaluate((id) => {
      const $editButton = $(`[data-edit-segment="${id}"]`);
      return $editButton.data('ui-tooltip-title') || $editButton.attr('title') || '';
    }, siteSegmentId);
    expect(siteTitle).to.contain('for this website');
  });

  it("should open the editor panel form when clicking edit segment on a row", async function() {
    await page.goto(url);
    await page.waitForNetworkIdle();

    await page.waitForSelector(`[data-edit-segment="${siteSegmentId}"]`, { visible: true });
    await page.click(`[data-edit-segment="${siteSegmentId}"]`);

    await page.waitForFunction((segmentName) => {
      const $panel = $('.segmentEditorPanel');
      if (!$panel.hasClass('editing')) {
        return false;
      }
      const $name = $panel.find('.segment-content > h3 > span');
      return $name.length && $name.text().trim() === segmentName;
    }, {}, siteSegmentName);

    const formState = await page.evaluate(() => {
      const $panel = $('.segmentEditorPanel');
      return {
        isEditing: $panel.hasClass('editing'),
        hasForm: $panel.find('.segment-element').length > 0,
        formName: $panel.find('.segment-content > h3 > span').text().trim(),
      };
    });

    expect(formState.isEditing).to.equal(true);
    expect(formState.hasForm).to.equal(true);
    expect(formState.formName).to.equal(siteSegmentName);
  });

  it("should update star state and order when starring and unstarring", async function() {
    await page.goto(url);
    await page.waitForNetworkIdle();

    await page.waitForFunction((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return $row.length && $row.find('[data-star]').length;
    }, {}, siteSegmentName);

    await page.evaluate((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      $row.find('[data-star]').trigger('click');
    }, siteSegmentName);

    await page.waitForFunction((id) => {
      const segment = window.SegmentEditorPanel.getSegmentFromId(id);
      return segment && segment.starred === true;
    }, {}, siteSegmentId);
    const starredState = await page.evaluate((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return {
        hasStarClass: $row.hasClass('segmentStarred'),
        order: $row.attr('data-segment-order'),
      };
    }, siteSegmentName);

    expect(starredState.hasStarClass).to.equal(true);
    expect(starredState.order).to.equal('1');

    await page.evaluate((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      $row.find('[data-star]').trigger('click');
    }, siteSegmentName);

    await page.waitForFunction((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return !$row.hasClass('segmentStarred');
    }, {}, siteSegmentName);
    const unstarredState = await page.evaluate((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return {
        hasStarClass: $row.hasClass('segmentStarred'),
        order: $row.attr('data-segment-order'),
      };
    }, siteSegmentName);

    expect(unstarredState.hasStarClass).to.equal(false);
    expect(unstarredState.order).to.equal('0');
  });

  it("should move selected segment to order 2 after selecting in the top control dropdown", async function() {
    await page.goto(url);
    await page.waitForNetworkIdle();

    await page.click('.segmentationContainer .title');
    await page.waitForSelector('.segmentEditorPanel .segmentList', { visible: true });

    await page.evaluate((name) => {
      const $items = $('.segmentEditorPanel .segmentList li');
      const $row = $items.filter(function () {
        return $(this).text().indexOf(name) !== -1;
      });
      $row.find('.segname').trigger('click');
    }, siteSegmentName);

    await page.waitForFunction((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return $row.attr('data-segment-order') || '';
    }, {}, siteSegmentName);

    const selectedOrder = await page.evaluate((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return $row.attr('data-segment-order') || '';
    }, siteSegmentName);

    expect(selectedOrder).to.equal('2');
  });

  it("should reflect segment star state in the editor panel after starring on the page", async function() {
    await page.goto(url);
    await page.waitForNetworkIdle();

    await page.waitForFunction((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return $row.length && $row.find('[data-star]').length;
    }, {}, siteSegmentName);

    await page.evaluate((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      $row.find('[data-star]').trigger('click');
    }, siteSegmentName);

    await page.waitForFunction((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return $row.hasClass('segmentStarred');
    }, {}, siteSegmentName);

    await page.click('.segmentationContainer .title');
    await page.waitForSelector('.segmentEditorPanel .segmentList', { visible: true });

    await page.waitForFunction((id) => {
      const $panel = $('.segmentEditorPanel');
      return $panel.hasClass('expanded')
        && $panel.find(`.segmentList li[data-idsegment="${id}"]`).length > 0;
    }, {}, siteSegmentId);

    await page.waitForFunction((id) => {
      return $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`).hasClass('segmentStarred');
    }, {}, siteSegmentId);

    const isStarredInPanel = await page.evaluate((id) => {
      const $row = $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`);
      return $row.hasClass('segmentStarred');
    }, siteSegmentId);

    expect(isStarredInPanel).to.equal(true);

    await page.evaluate((id) => {
      const $row = $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`);
      $row.find('[data-star]').trigger('click');
    }, siteSegmentId);

    await page.waitForFunction((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return !$row.hasClass('segmentStarred');
    }, {}, siteSegmentName);
  });

  it("should reflect segment star state in the page after starring on the editor panel", async function() {
    await page.goto(url);
    await page.waitForNetworkIdle();

    await page.click('.segmentationContainer .title');
    await page.waitForSelector('.segmentEditorPanel .segmentList', { visible: true });

    await page.waitForFunction((id) => {
      const $panel = $('.segmentEditorPanel');
      return $panel.hasClass('expanded')
        && $panel.find(`.segmentList li[data-idsegment="${id}"]`).length > 0;
    }, {}, siteSegmentId);

    await page.evaluate((id) => {
      const $row = $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`);
      $row.find('[data-star]').trigger('click');
    }, siteSegmentId);

    await page.waitForFunction((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return $row.hasClass('segmentStarred');
    }, {}, siteSegmentName);

    const isStarredOnPage = await page.evaluate((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return $row.hasClass('segmentStarred');
    }, siteSegmentName);

    expect(isStarredOnPage).to.equal(true);

    await page.evaluate((id) => {
      const $row = $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`);
      $row.find('[data-star]').trigger('click');
    }, siteSegmentId);

    await page.waitForFunction((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return !$row.hasClass('segmentStarred');
    }, {}, siteSegmentName);
  });

  function extractSegmentId(result) {
    if (result && typeof result === 'object' && typeof result.value !== 'undefined') {
      return parseInt(result.value, 10) || 0;
    }
    return parseInt(result, 10) || 0;
  }
});
