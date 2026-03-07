/*!
 * Matomo - free/libre analytics platform
 *
 * SegmentEditor ui tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SegmentManagementPageTest", function () {
  this.fixture = 'Piwik\\Plugins\\SegmentEditor\\tests\\Fixtures\\SegmentManagementPageFixture';

  var generalParams = 'idSite=1&period=range&date=2010-03-06,2010-03-08';
  var url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Visitors&subcategory=CoreHome_Segments';
  const globalSegment = {
    id: null,
    name: 'UI Test Global Segment',
    definition: 'countryCode==fr',
  };
  const siteSegment = {
    id: null,
    name: 'UI Test Site Segment',
    definition: 'visitCount>=1',
  };
  const xssSegment = {
    id: null,
    name: '<script>alert("testsegment");</script>',
    definition: 'browserCode==FF',
  };
  const realtimeSegment = {
    id: null,
    name: 'UI Test Realtime Segment',
    definition: 'browserCode==FF',
  };
  const complexDashboardSegment = {
    id: null,
    name: 'UI Test Complex Dashboard Segment',
    definition: 'browserName!=s%2525232%252526%252523--_*%25252B%25253F%252523%252520%252520%2525235%252522%2527%252526%25253C%25253E.22%25252C3,browserName==s%2525232%252526%252523--_*%25252B%25253F%252523%252520%252520%2525235%252522%2527%252526%25253C%25253E.22%25252C3;browserName!=s%2525232%252526%252523--_*%25252B%25253F%252523%252520%252520%2525235%252522%2527%252526%25253C%25253E.22%25252C3',
  };

  before(async function () {
    await switchToAdminUser();
    const allSegmentsResponse = await testEnvironment.callApi('SegmentEditor.getAll', {});
    assignSegmentIdsFromApiResponse(allSegmentsResponse);
  });

  it("should load correctly", async function() {
    await page.goto(url);
    await page.waitForNetworkIdle();

    expect(await page.screenshot({ fullPage: true })).to.matchImage('initial');
  });

  it("should expose the expected segment panel public API contract", async function() {
    await openPage();

    const apiContract = await page.evaluate(() => {
      const api = window.matomoPluginSegmentEditor && window.matomoPluginSegmentEditor.panelAPI;
      return {
        exists: !!api,
        methods: {
          getSegmentFromId: typeof (api && api.getSegmentFromId) === 'function',
          toggleStarredSegment: typeof (api && api.toggleStarredSegment) === 'function',
          onSegmentsStarChange: typeof (api && api.onSegmentsStarChange) === 'function',
        },
      };
    });

    expect(apiContract.exists).to.equal(true);
    expect(apiContract.methods.getSegmentFromId).to.equal(true);
    expect(apiContract.methods.toggleStarredSegment).to.equal(true);
    expect(apiContract.methods.onSegmentsStarChange).to.equal(true);
  });

  describe("Tooltips", function () {
    it("should show the correct edit tooltip for global vs site segments", async function() {
      await openPage();

      const expectedEditTitles = await page.evaluate(() => ({
        global: _pk_translate('General_CanEditGlobalSegment'),
        site: _pk_translate('General_CanEditSiteSegment'),
      }));

      const globalTitle = await getElementTooltip(`[data-edit-segment="${globalSegment.id}"]`);
      expect(globalTitle).to.equal(expectedEditTitles.global);

      const siteTitle = await getElementTooltip(`[data-edit-segment="${siteSegment.id}"]`);
      expect(siteTitle).to.equal(expectedEditTitles.site);
    });

    it("should update star tooltip after starring and unstarring a segment", async function() {
      await openPage();

      const expectedTitles = await page.evaluate(() => ({
        star: _pk_translate('General_CanStarSiteSegment'),
        unstarPrefix: _pk_translate('General_CanUnstarSiteSegment'),
      }));

      await setPageSegmentStarState(siteSegment.name, siteSegment.id, false);
      await waitForSegmentStarTooltipContains(siteSegment.name, expectedTitles.star);

      const beforeStarTitle = await getSegmentStarTooltip(siteSegment.name);
      expect(beforeStarTitle).to.contain(expectedTitles.star);

      await setPageSegmentStarState(siteSegment.name, siteSegment.id, true);
      await waitForSegmentStarTooltipContains(siteSegment.name, expectedTitles.unstarPrefix);

      const afterStarTitle = await getSegmentStarTooltip(siteSegment.name);
      expect(afterStarTitle).to.contain(expectedTitles.unstarPrefix);
      expect(afterStarTitle).to.not.equal(beforeStarTitle);

      await setPageSegmentStarState(siteSegment.name, siteSegment.id, false);
      await waitForSegmentStarTooltipContains(siteSegment.name, expectedTitles.star);

      const afterUnstarTitle = await getSegmentStarTooltip(siteSegment.name);
      expect(afterUnstarTitle).to.contain(expectedTitles.star);
    });

    it("should show correct action tooltips for a global segment when user has only view access to current site", async function() {
      await setPageSegmentStarState(globalSegment.name, globalSegment.id, false);
      await switchToViewUser();

      try {
        await openPage();

        const titles = await getSegmentActionState(globalSegment.name);

        const expectedTitles = await page.evaluate(() => ({
          star: _pk_translate('General_CanNotStarGlobalSegment'),
          edit: _pk_translate('General_CanNotEditGlobalSegment'),
          delete: _pk_translate('General_CanNotDeleteGlobalSegment'),
        }));

        expect(titles.rowCount).to.equal(1);
        expect(titles.starTitle).to.equal(expectedTitles.star);
        expect(titles.editTitle).to.equal(expectedTitles.edit);
        expect(titles.deleteTitle).to.equal(expectedTitles.delete);

        expect(titles.starState).to.equal('disabled');
        expect(titles.editState).to.equal('disabled');
        expect(titles.deleteState).to.equal('disabled');
      } finally {
        await switchToAdminUser();
      }
    });
  });

  it("should show data for pre-processed segments and no data for real-time segments", async function() {
    await openPage();

    await page.waitForFunction((preProcessedName, realtimeName) => {
      return $(`tr[data-segment-name="${preProcessedName}"]`).length
        && $(`tr[data-segment-name="${realtimeName}"]`).length;
    }, {}, siteSegment.name, realtimeSegment.name);

    await page.waitForFunction((preProcessedName, realtimeName) => {
      const $preProcessedRow = $(`tr[data-segment-name="${preProcessedName}"]`);
      const $realtimeRow = $(`tr[data-segment-name="${realtimeName}"]`);
      const $preProcessedCells = $preProcessedRow.find('td.entityTable_Numeric');
      const $realtimeCells = $realtimeRow.find('td.entityTable_Numeric');

      const preProcessedVisits = ($preProcessedCells.eq(0).text() || '').trim();
      const preProcessedActions = ($preProcessedCells.eq(1).text() || '').trim();
      const realtimeVisits = ($realtimeCells.eq(0).text() || '').trim();
      const realtimeActions = ($realtimeCells.eq(1).text() || '').trim();

      return /[0-9]/.test(preProcessedVisits)
        && /[0-9]/.test(preProcessedActions)
        && realtimeVisits === '-'
        && realtimeActions === '-';
    }, {}, siteSegment.name, realtimeSegment.name);

    const tableData = {
      preProcessed: await getSegmentRowNumericData(siteSegment.name),
      realtime: await getSegmentRowNumericData(realtimeSegment.name),
    };

    expect(tableData.preProcessed.visits).to.match(/[0-9]/);
    expect(tableData.preProcessed.actions).to.match(/[0-9]/);
    expect(tableData.realtime.visits).to.equal('-');
    expect(tableData.realtime.actions).to.equal('-');
  });

  it("should mark realtime and pre-processed rows via data-is-realtime", async function() {
    await openPage();

    await page.waitForFunction((preProcessedName, realtimeName) => {
      return $(`tr[data-segment-name="${preProcessedName}"]`).length
        && $(`tr[data-segment-name="${realtimeName}"]`).length;
    }, {}, siteSegment.name, realtimeSegment.name);

    const rowTypes = await page.evaluate((preProcessedName, realtimeName) => {
      return {
        preProcessed: $(`tr[data-segment-name="${preProcessedName}"]`).attr('data-is-realtime') || '',
        realtime: $(`tr[data-segment-name="${realtimeName}"]`).attr('data-is-realtime') || '',
      };
    }, siteSegment.name, realtimeSegment.name);

    expect(rowTypes.preProcessed).to.not.equal('1');
    expect(rowTypes.realtime).to.equal('1');
  });

  describe("Segments order", function () {
    it("should move starred segment to order 1", async function() {
      await openPage();

      await setPageSegmentStarState(siteSegment.name, siteSegment.id, false);
      const initialState = await getPageSegmentState(siteSegment.name, siteSegment.id);

      await setPageSegmentStarState(siteSegment.name, siteSegment.id, true);
      await page.waitForFunction((name) => {
        const $row = $(`tr[data-segment-name="${name}"]`);
        return $row.attr('data-segment-order') === '1';
      }, {}, siteSegment.name);

      const starredState = await getPageSegmentState(siteSegment.name, siteSegment.id);
      const globalIndexWhenStarred = await getSegmentRowDomIndex(globalSegment.name);
      const realtimeIndexWhenStarred = await getSegmentRowDomIndex(realtimeSegment.name);

      expect(starredState.hasStarClass).to.equal(true);
      expect(starredState.order).to.equal('1');
      expect(starredState.domIndex).to.be.lessThan(globalIndexWhenStarred);
      expect(starredState.domIndex).to.be.lessThan(realtimeIndexWhenStarred);

      await setPageSegmentStarState(siteSegment.name, siteSegment.id, false);
      await page.waitForFunction((name) => {
        const $row = $(`tr[data-segment-name="${name}"]`);
        return !$row.hasClass('segmentStarred')
          && $row.attr('data-segment-order') === '0';
      }, {}, siteSegment.name);

      const unstarredState = await getPageSegmentState(siteSegment.name, siteSegment.id);

      expect(unstarredState.hasStarClass).to.equal(false);
      expect(unstarredState.order).to.equal('0');
      expect(unstarredState.domIndex).to.equal(initialState.domIndex);
    });

    it("should keep segment order unchanged when starring fails", async function() {
      await openPage();

      await setPageSegmentStarState(siteSegment.name, siteSegment.id, false);
      const initialState = await getPageSegmentState(siteSegment.name, siteSegment.id);

      await installStarApiErrorMock();
      try {
        await page.evaluate((name) => {
          const $row = $(`tr[data-segment-name="${name}"]`);
          $row.find('[data-star]').trigger('click');
        }, siteSegment.name);

        await page.waitForFunction(() => (window.__segmentStarApiErrorCount || 0) > 0);
        await page.waitForFunction((name, id, expectedStarred, expectedOrder) => {
          const $row = $(`tr[data-segment-name="${name}"]`);
          const segment = window.matomoPluginSegmentEditor.panelAPI.getSegmentFromId(id);
          return $row.hasClass('segmentStarred') === expectedStarred
            && ($row.attr('data-segment-order') || '') === expectedOrder
            && !!segment
            && segment.starred === expectedStarred;
        }, {}, siteSegment.name, siteSegment.id, initialState.hasStarClass, initialState.order);

        const finalState = await getPageSegmentState(siteSegment.name, siteSegment.id);
        expect(finalState.hasStarClass).to.equal(initialState.hasStarClass);
        expect(finalState.order).to.equal(initialState.order);
        expect(finalState.domIndex).to.equal(initialState.domIndex);
      } finally {
        await restoreStarApiMock();
      }
    });

    it("should move selected segment to order 2", async function() {
      await openPage();
      await setPageSegmentStarState(siteSegment.name, siteSegment.id, false);

      await openSegmentPanel();

      await page.evaluate((name) => {
        const $items = $('.segmentEditorPanel .segmentList li');
        const $row = $items.filter(function () {
          return $(this).text().indexOf(name) !== -1;
        });
        $row.find('.segname').trigger('click');
      }, siteSegment.name);

      await page.waitForFunction((name) => {
        const $row = $(`tr[data-segment-name="${name}"]`);
        return $row.attr('data-segment-order') || '';
      }, {}, siteSegment.name);

      const selectedState = await getPageSegmentState(siteSegment.name, siteSegment.id);
      const globalIndexWhenSelected = await getSegmentRowDomIndex(globalSegment.name);
      const realtimeIndexWhenSelected = await getSegmentRowDomIndex(realtimeSegment.name);

      expect(selectedState.order).to.equal('2');
      expect(selectedState.domIndex).to.be.lessThan(globalIndexWhenSelected);
      expect(selectedState.domIndex).to.be.lessThan(realtimeIndexWhenSelected);
    });
  });

  describe("Segments synchronization between page and panel", function () {

    it("should reflect segment star state in the editor panel after starring on the page", async function() {
      await openPage();

      await setPageSegmentStarState(siteSegment.name, siteSegment.id, false);
      await openSegmentPanel();
      await waitForPanelSegment(siteSegment.id);

      await page.waitForFunction((id) => {
        const $row = $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`);
        const segment = window.matomoPluginSegmentEditor.panelAPI.getSegmentFromId(id);
        return !$row.hasClass('segmentStarred')
          && !!segment
          && segment.starred === false;
      }, {}, siteSegment.id);

      const isUnstarredInPanel = await page.evaluate((id) => {
        const $row = $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`);
        return !$row.hasClass('segmentStarred');
      }, siteSegment.id);

      expect(isUnstarredInPanel).to.equal(true);

      await setPageSegmentStarState(siteSegment.name, siteSegment.id, true);

      await page.waitForFunction((id) => {
        return $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`).hasClass('segmentStarred');
      }, {}, siteSegment.id);

      const isStarredInPanel = await page.evaluate((id) => {
        const $row = $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`);
        return $row.hasClass('segmentStarred');
      }, siteSegment.id);

      expect(isStarredInPanel).to.equal(true);

      await setPanelSegmentStarState(siteSegment.id, false);
      await page.waitForFunction((name) => {
        const $row = $(`tr[data-segment-name="${name}"]`);
        return !$row.hasClass('segmentStarred');
      }, {}, siteSegment.name);
    });

    it("should reflect segment star state in the page after starring on the editor panel", async function() {
      await openPage();
      await setPageSegmentStarState(siteSegment.name, siteSegment.id, false);

      await openSegmentPanel();
      await waitForPanelSegment(siteSegment.id);

      await setPanelSegmentStarState(siteSegment.id, true);
      await page.waitForFunction((name, id) => {
        const $row = $(`tr[data-segment-name="${name}"]`);
        const segment = window.matomoPluginSegmentEditor.panelAPI.getSegmentFromId(id);
        return $row.hasClass('segmentStarred')
          && !!segment
          && segment.starred === true;
      }, {}, siteSegment.name, siteSegment.id);

      const isStarredOnPage = await page.evaluate((name) => {
        const $row = $(`tr[data-segment-name="${name}"]`);
        return $row.hasClass('segmentStarred');
      }, siteSegment.name);

      expect(isStarredOnPage).to.equal(true);

      await setPanelSegmentStarState(siteSegment.id, false);
      await page.waitForFunction((name, id) => {
        const $row = $(`tr[data-segment-name="${name}"]`);
        const segment = window.matomoPluginSegmentEditor.panelAPI.getSegmentFromId(id);
        return !$row.hasClass('segmentStarred')
          && !!segment
          && segment.starred === false;
      }, {}, siteSegment.name, siteSegment.id);
    });
  });

  describe("Actions", function () {
    it("should open the editor panel form when clicking on edit", async function() {
      await openPage();

      await page.waitForSelector(`[data-edit-segment="${siteSegment.id}"]`, { visible: true });
      await page.click(`[data-edit-segment="${siteSegment.id}"]`);

      await page.waitForFunction((segmentName) => {
        const $panel = $('.segmentEditorPanel');
        if (!$panel.hasClass('editing')) {
          return false;
        }
        const $name = $panel.find('.segment-content > h3 > span');
        return $name.length && $name.text().trim() === segmentName;
      }, {}, siteSegment.name);

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
      expect(formState.formName).to.equal(siteSegment.name);
    });

    it("should open delete confirmation when clicking on delete", async function() {
      await openPage();

      await page.waitForSelector(`[data-delete-segment="${siteSegment.id}"]`, { visible: true });
      await page.click(`[data-delete-segment="${siteSegment.id}"]`);

      await page.waitForSelector('.modal.open', { visible: true });

      const modalState = await page.evaluate(() => {
        const $modal = $('.modal.open');
        const text = $modal.text() || '';
        return {
          isVisible: $modal.length > 0,
          hasConfirmText: text.toLowerCase().indexOf('delete') !== -1 || text.indexOf('?') !== -1,
        };
      });

      expect(modalState.isVisible).to.equal(true);
      expect(modalState.hasConfirmText).to.equal(true);

      const footerButtonCount = await page.evaluate(() => $('.modal.open .modal-footer a:visible').length);
      expect(footerButtonCount).to.be.greaterThan(0);

      await page.evaluate(() => {
        const button = $('.modal.open .modal-footer a:visible').last().get(0);
        if (!button) {
          throw new Error('No visible modal footer button found');
        }
        button.click();
      });

      await page.waitForTimeout(500);
      await page.waitForFunction(() => $('.modal.open:visible').length === 0);
      await page.waitForSelector(`[data-delete-segment="${siteSegment.id}"]`, { visible: true });
    });

    it("should open dashboard from all visits and global segment and update top segment selector", async function() {
      await openPage();

      const allVisitsName = await page.evaluate(() => _pk_translate('SegmentEditor_DefaultAllVisits'));

      await clickDashboardLinkForSegment(allVisitsName);
      await page.waitForFunction((expectedName) => {
        const text = $('.segmentationContainer .segmentationTitle').text().trim();
        return text.indexOf(expectedName) !== -1;
      }, {}, allVisitsName);

      await openPage();

      await clickDashboardLinkForSegment(globalSegment.name);
      await page.waitForFunction((expectedName) => {
        const text = $('.segmentationContainer .segmentationTitle').text().trim();
        return text.indexOf(expectedName) !== -1;
      }, {}, globalSegment.name);
    });

    it("should open dashboard from a complex encoded segment and preserve its definition", async function() {
      await openPage();

      await clickDashboardLinkForSegment(complexDashboardSegment.name);
      await page.waitForFunction((expectedName) => {
        const text = $('.segmentationContainer .segmentationTitle').text().trim();
        return text.indexOf(expectedName) !== -1;
      }, {}, complexDashboardSegment.name);

      const appliedSegment = await page.evaluate(() => {
        const hash = window.location.hash || '';
        const query = hash.indexOf('?') >= 0 ? hash.slice(hash.indexOf('?') + 1) : window.location.search.slice(1);
        return new URLSearchParams(query).get('segment') || '';
      });

      expect(appliedSegment).to.equal(complexDashboardSegment.definition);
    });
  });

  it("should not trigger alert when hovering xss segment name", async function() {
    await openPage();

    await page.evaluate(() => {
      window.__segmentXssAlertCount = 0;
      window.alert = function () {
        window.__segmentXssAlertCount += 1;
      };
    });

    await page.waitForSelector(`[data-edit-segment="${xssSegment.id}"]`, { visible: true });
    await page.evaluate((id) => {
      const $row = $(`[data-edit-segment="${id}"]`).closest('tr');
      const nameCell = $row.find('td[title]').first().get(0);
      if (!nameCell) {
        throw new Error('XSS segment name cell not found');
      }
      nameCell.dispatchEvent(new MouseEvent('mouseenter', { bubbles: true }));
      nameCell.dispatchEvent(new MouseEvent('mouseover', { bubbles: true }));
    }, xssSegment.id);
    await page.waitForTimeout(300);

    const alertCount = await page.evaluate(() => window.__segmentXssAlertCount || 0);
    expect(alertCount).to.equal(0);
  });

  function assignSegmentIdsFromApiResponse(response) {
    const segments = normalizeSegmentsResponse(response);

    const findSegmentId = (target) => {
      const expectedSite = target === globalSegment ? 0 : 1;
      const match = segments.find((segment) => {
        const segmentSite = parseInt(segment && segment.enable_only_idsite, 10) || 0;
        return segment
          && segment.definition === target.definition
          && segmentSite === expectedSite;
      });

      return parseInt(match && (match.idsegment || match.idSegment), 10) || 0;
    };

    globalSegment.id = findSegmentId(globalSegment);
    siteSegment.id = findSegmentId(siteSegment);
    xssSegment.id = findSegmentId(xssSegment);
    realtimeSegment.id = findSegmentId(realtimeSegment);
    complexDashboardSegment.id = findSegmentId(complexDashboardSegment);
  }

  function normalizeSegmentsResponse(segments) {
    if (Array.isArray(segments)) {
      return segments;
    }
    if (segments && Array.isArray(segments.value)) {
      return segments.value;
    }
    if (segments && typeof segments === 'object') {
      return Object.values(segments).filter((value) => value && typeof value === 'object');
    }
    return [];
  }

  async function getSegmentRowNumericData(segmentName) {
    return await page.evaluate((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      const $numericCells = $row.find('td.entityTable_Numeric');
      return {
        visits: ($numericCells.eq(0).text() || '').trim(),
        actions: ($numericCells.eq(1).text() || '').trim(),
      };
    }, segmentName);
  }

  async function openPage() {
    await page.goto(url);
    await page.waitForNetworkIdle();
  }

  async function openSegmentPanel() {
    await page.click('.segmentationContainer .title');
    await page.waitForSelector('.segmentEditorPanel .segmentList', { visible: true });
  }

  async function waitForPanelSegment(segmentId) {
    await page.waitForFunction((id) => {
      const $panel = $('.segmentEditorPanel');
      return $panel.hasClass('expanded')
        && $panel.find(`.segmentList li[data-idsegment="${id}"]`).length > 0;
    }, {}, segmentId);
  }

  async function getElementTooltip(selector) {
    return await page.evaluate((sel) => {
      const $el = $(sel);
      return $el.data('ui-tooltip-title') || $el.attr('title') || '';
    }, selector);
  }

  async function getSegmentStarTooltip(segmentName) {
    return await page.evaluate((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      const $starButton = $row.find('[data-star]');
      return $starButton.data('ui-tooltip-title') || $starButton.attr('title') || '';
    }, segmentName);
  }

  async function waitForSegmentStarTooltipContains(segmentName, expectedTitlePart) {
    await page.waitForFunction((name, expectedTitle) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      const $starButton = $row.find('[data-star]');
      const title = $starButton.data('ui-tooltip-title') || $starButton.attr('title') || '';
      return title.indexOf(expectedTitle) !== -1;
    }, {}, segmentName, expectedTitlePart);
  }

  async function getSegmentActionState(segmentName) {
    return await page.evaluate((name) => {
      const getTitle = ($el) => $el.data('ui-tooltip-title') || $el.attr('title') || '';
      const $row = $(`tr[data-segment-name="${name}"]`);
      const $starButton = $row.find('[data-star]');
      const $editButton = $row.find('[data-edit-segment]');
      const $deleteButton = $row.find('[data-delete-segment]');
      return {
        rowCount: $row.length,
        starTitle: getTitle($starButton),
        editTitle: getTitle($editButton),
        deleteTitle: getTitle($deleteButton),
        starState: $starButton.attr('data-state') || '',
        editState: $editButton.attr('data-state') || '',
        deleteState: $deleteButton.attr('data-state') || '',
      };
    }, segmentName);
  }

  async function getPageSegmentState(segmentName, segmentId) {
    return await page.evaluate((name, id) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      const segment = window.matomoPluginSegmentEditor.panelAPI.getSegmentFromId(id);
      const domIndex = $('table.entityTable tbody tr[data-segment-name]').index($row);
      return {
        hasStarClass: $row.hasClass('segmentStarred'),
        order: $row.attr('data-segment-order') || '',
        domIndex,
        modelStarred: !!(segment && segment.starred),
      };
    }, segmentName, segmentId);
  }

  async function getSegmentRowDomIndex(segmentName) {
    return await page.evaluate((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return $('table.entityTable tbody tr[data-segment-name]').index($row);
    }, segmentName);
  }

  async function installStarApiErrorMock() {
    await page.evaluate(() => {
      window.__segmentStarApiErrorCount = 0;
      if (!window.__segmentOriginalAjaxHelperSend) {
        window.__segmentOriginalAjaxHelperSend = window.ajaxHelper.prototype.send;
      }
      window.ajaxHelper.prototype.send = function () {
        const method = (this.getParams && this.getParams.method) || (this.postParams && this.postParams.method) || '';
        if (method === 'SegmentEditor.star' || method === 'SegmentEditor.unstar') {
          window.__segmentStarApiErrorCount += 1;
          const callback = this.callback;
          if (typeof callback === 'function') {
            setTimeout(() => callback({ result: 'error', message: 'Simulated API error in UI test' }), 0);
          }
          return null;
        }
        return window.__segmentOriginalAjaxHelperSend.apply(this, arguments);
      };
    });
  }

  async function restoreStarApiMock() {
    await page.evaluate(() => {
      if (window.__segmentOriginalAjaxHelperSend) {
        window.ajaxHelper.prototype.send = window.__segmentOriginalAjaxHelperSend;
      }
      delete window.__segmentStarApiErrorCount;
    });
  }

  async function setPageSegmentStarState(segmentName, segmentId, shouldBeStarred) {
    await page.waitForFunction((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return $row.length && $row.find('[data-star]').length;
    }, {}, segmentName);

    const isStarred = await page.evaluate((name) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      return $row.hasClass('segmentStarred');
    }, segmentName);

    if (isStarred !== shouldBeStarred) {
      await page.evaluate((name) => {
        const $row = $(`tr[data-segment-name="${name}"]`);
        $row.find('[data-star]').trigger('click');
      }, segmentName);
    }

    await page.waitForFunction((name, id, desiredState) => {
      const $row = $(`tr[data-segment-name="${name}"]`);
      const segment = window.matomoPluginSegmentEditor.panelAPI.getSegmentFromId(id);
      return $row.hasClass('segmentStarred') === desiredState
        && !!segment
        && segment.starred === desiredState;
    }, {}, segmentName, segmentId, shouldBeStarred);
  }

  function switchToAdminUser() {
    delete testEnvironment.idSitesViewAccess;
    delete testEnvironment.idSitesWriteAccess;
    delete testEnvironment.idSitesAdminAccess;
    delete testEnvironment.idSitesCapabilities;
    delete testEnvironment.fakeIdentity;
    return testEnvironment.save();
  }

  function switchToViewUser() {
    testEnvironment.idSitesViewAccess = [1];
    delete testEnvironment.idSitesWriteAccess;
    delete testEnvironment.idSitesAdminAccess;
    delete testEnvironment.idSitesCapabilities;
    testEnvironment.fakeIdentity = 'viewUserLogin';
    return testEnvironment.save();
  }

  async function setPanelSegmentStarState(segmentId, shouldBeStarred) {
    await page.waitForFunction((id) => {
      return $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`).length > 0;
    }, {}, segmentId);

    const isStarred = await page.evaluate((id) => {
      const $row = $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`);
      return $row.hasClass('segmentStarred');
    }, segmentId);

    if (isStarred !== shouldBeStarred) {
      await page.evaluate((id) => {
        const $row = $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`);
        $row.find('[data-star]').trigger('click');
      }, segmentId);
    }

    await page.waitForFunction((id, desiredState) => {
      const $row = $(`.segmentEditorPanel .segmentList li[data-idsegment="${id}"]`);
      const segment = window.matomoPluginSegmentEditor.panelAPI.getSegmentFromId(id);
      return $row.hasClass('segmentStarred') === desiredState
        && !!segment
        && segment.starred === desiredState;
    }, {}, segmentId, shouldBeStarred);
  }

  async function clickDashboardLinkForSegment(segmentName) {
    await page.waitForFunction((name) => {
      const row = Array.from(document.querySelectorAll('tr[data-segment-name]')).find((element) => (
        element.getAttribute('data-segment-name') === name
      ));
      return !!(row && row.querySelector('.icon-show'));
    }, {}, segmentName);

    const previousUrl = await page.url();
    const navigationPromise = page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 10000 }).catch(() => null);
    await page.evaluate((name) => {
      const row = Array.from(document.querySelectorAll('tr[data-segment-name]')).find((element) => (
        element.getAttribute('data-segment-name') === name
      ));
      const link = row && row.querySelector('.icon-show');
      if (!link) {
        throw new Error(`Dashboard link not found for segment "${name}"`);
      }
      link.click();
    }, segmentName);
    await navigationPromise;
    await page.waitForFunction((oldUrl) => {
      return window.location.href !== oldUrl
        && window.location.href.indexOf('category=Dashboard_Dashboard') !== -1;
    }, {}, previousUrl);
    await page.waitForSelector('.segmentationContainer .segmentationTitle', { visible: true });
    await page.waitForNetworkIdle();
  }
});
