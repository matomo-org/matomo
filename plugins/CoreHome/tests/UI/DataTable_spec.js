/*!
 * Matomo - free/libre analytics platform
 *
 * UI regression for DataTable sorting & AJAX reloading.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('DataTable', function () {
  this.optionsOverride = {
    'persist-fixture-data': false
  };

  const devicesUrl = "?module=CoreHome&action=index&idSite=3&period=day&date=yesterday&category=General_Visitors&subcategory=DevicesDetection_Devices";
  const emptyDevicesUrl = "?module=CoreHome&action=index&idSite=3&period=day&date=2011-01-01&category=General_Visitors&subcategory=DevicesDetection_Devices";
  const widgetSelector = '#widgetDevicesDetectiongetType';
  const selectors = {
    metricColumn: `${widgetSelector} #nb_uniq_visitors`,
    limitDropdownTrigger: `${widgetSelector} .limitSelection .select-wrapper input.select-dropdown`,
    limitDropdownMenu: `${widgetSelector} .limitSelection .select-wrapper ul.select-dropdown`,
    // One 3-dots trigger opens one menu holding every action, so the visualisation list and the
    // config items no longer have separate triggers of their own.
    actionsTrigger: `${widgetSelector} .reportHeader__actionsTrigger`,
    actionsMenu: `${widgetSelector} .reportHeader__actionsMenu`,
    visualizationButtons: `${widgetSelector} .reportHeader__actionsMenu .dataTableFooterIcons .tableIcon[data-footer-icon-id]`,
    // Scoped to the widget: the page shows several reports and each renders its own copy of the
    // menu in its own header, so an unscoped selector picks whichever comes first in the document.
    totalsRowToggle: `${widgetSelector} .dataTableShowTotalsRow`,
    percentageValuesToggle: `${widgetSelector} .dataTableShowPercentageValues`,
    // scoped to the widget: the page shows four reports, each with its own header search
    searchInput: `${widgetSelector} .reportHeader__search .mtm-searchInput__input`,
    totalsRow: `${widgetSelector} table.dataTable tr.totalsRow`,
    footerMessage: `${widgetSelector} .datatableFooterMessage`,
  };

  async function openDevicesDetectionWidget() {
    await page.goto(devicesUrl);
    await page.waitForNetworkIdle();
    await page.waitForSelector(widgetSelector, { visible: true });
    await page.waitForNetworkIdle();
  }

  async function loadWidget() {
    await openDevicesDetectionWidget();
    const widget = await page.$(widgetSelector);
    expect(widget).to.be.ok;
    return widget;
  }

  async function trackViewDataTableRequests(action) {
    let ajaxRequestCount = 0;
    const requestHandler = (request) => {
      if (request.resourceType() === 'xhr' && request.url().indexOf('saveViewDataTableParameters') !== -1) {
        ajaxRequestCount += 1;
      }
    };
    page.webpage.on('request', requestHandler);
    try {
      await action();
    } finally {
      page.webpage.off('request', requestHandler);
    }
    return ajaxRequestCount;
  }

  async function interactWithColumnSortingAndLimit() {
    await page.waitForSelector(selectors.metricColumn, { visible: true });
    await page.click(selectors.metricColumn);
    await page.waitForNetworkIdle();
    await page.click(selectors.metricColumn);
    await page.waitForNetworkIdle();

    await page.waitForSelector(selectors.limitDropdownTrigger, { visible: true });
    await page.click(selectors.limitDropdownTrigger);

    await page.waitForSelector(selectors.limitDropdownMenu, { visible: true });
    const dropdown = await page.$(selectors.limitDropdownMenu);
    expect(dropdown).to.be.ok;
    await page.click(`${selectors.limitDropdownMenu} li:nth-child(1)`);
    await page.waitForNetworkIdle();
  }

  async function openActionsMenu() {
    await page.waitForSelector(selectors.actionsTrigger, { visible: true });
    await page.click(selectors.actionsTrigger);
    await page.waitForSelector(selectors.actionsMenu, { visible: true });
  }

  async function changeVisualization() {
    await openActionsMenu();

    await page.waitForFunction((selector) => document.querySelectorAll(selector).length >= 2, {}, selectors.visualizationButtons);
    await page.$$eval(selectors.visualizationButtons, (buttons) => {
      if (buttons.length < 2) {
        throw new Error('Not enough visualization buttons found');
      }
      buttons[1].click();
    });
    await page.waitForNetworkIdle();
  }

  async function toggleTotalsRow(times = 1) {
    for (let i = 0; i < times; i += 1) {
      await openActionsMenu();
      await page.waitForSelector(selectors.totalsRowToggle, { visible: true });
      await page.click(selectors.totalsRowToggle);
      await page.waitForNetworkIdle();
    }
  }

  async function togglePercentageValues(times = 1) {
    for (let i = 0; i < times; i += 1) {
      await openActionsMenu();
      await page.waitForSelector(selectors.percentageValuesToggle, { visible: true });
      await page.click(selectors.percentageValuesToggle);
      await page.waitForNetworkIdle();
    }
  }

  async function searchForPattern(pattern) {
    await page.waitForSelector(selectors.searchInput, { visible: true });
    await page.focus(selectors.searchInput);
    await page.keyboard.type(pattern);
    // the search is debounced; waitForNetworkIdle waits it out (its idle threshold exceeds the
    // debounce delay) and then for the reload it triggers to settle
    await page.waitForNetworkIdle();
  }

  function toNumber(formattedValue) {
    return parseInt(String(formattedValue).replace(/[^0-9]/g, ''), 10);
  }

  async function readTotalsRow() {
    return page.$eval(selectors.totalsRow, (row) => ({
      isFiltered: row.classList.contains('filteredTotalsRow'),
      label: row.querySelector('td.label').innerText.trim(),
      notes: Array.from(row.querySelectorAll('.totalsRowNote')).map((note) => note.innerText.trim()),
      firstMetric: row.querySelector('td.column .value').innerText.trim(),
    }));
  }

  it('should total every row of the report when no table search is active', async function () {
    await loadWidget();
    await toggleTotalsRow();

    const totalsRow = await readTotalsRow();

    expect(totalsRow.isFiltered).to.be.false;
    expect(totalsRow.label).to.equal('Totals');
    expect(totalsRow.notes).to.be.empty;
    expect(await page.$(selectors.footerMessage)).to.be.null;

    await toggleTotalsRow();
  });

  it('should only total the rows matching the table search when a search is active', async function () {
    await loadWidget();
    await toggleTotalsRow();

    const reportTotal = (await readTotalsRow()).firstMetric;
    const rowCount = (await page.$$(`${widgetSelector} table.dataTable tbody tr:not(.totalsRow)`)).length;

    const firstRowLabel = await page.$eval(
      `${widgetSelector} table.dataTable tbody tr:not(.totalsRow) td.label .value`,
      (cell) => cell.innerText.trim(),
    );
    await searchForPattern(firstRowLabel);

    const filteredRowCount = (await page.$$(`${widgetSelector} table.dataTable tbody tr:not(.totalsRow)`)).length;
    expect(filteredRowCount).to.be.below(rowCount);

    const totalsRow = await readTotalsRow();

    expect(totalsRow.isFiltered).to.be.true;
    expect(totalsRow.label).to.contain('Filtered total');
    expect(totalsRow.label).to.contain('Matching current table filter');
    expect(totalsRow.notes.some((note) => /(of .* report total|overall .*)/.test(note))).to.be.true;
    expect(toNumber(totalsRow.firstMetric)).to.be.below(toNumber(reportTotal));

    const footerMessage = await page.$eval(selectors.footerMessage, (node) => node.innerText.trim());
    expect(footerMessage).to.contain('Note: Showing filtered results.');
    expect(footerMessage).to.contain('are not recalculated by the filter');

    await toggleTotalsRow();
  });

  async function readHeaderLayout() {
    return page.evaluate((widgetSel) => {
      const widget = document.querySelector(widgetSel);
      const dataTable = widget.querySelector('.dataTable');
      const title = widget.querySelector('.reportHeader__title');

      return {
        headers: widget.querySelectorAll('.reportHeader').length,
        headingTag: title.tagName,
        title: title.innerText.trim(),
        actionBarsInTable: dataTable.querySelectorAll('.dataTableHeaderControls').length,
        actionsInHeader: widget.querySelectorAll('.reportHeader__actions').length,
        widgetControls: widget.querySelectorAll('.reportHeader .widgetControls__action').length,
      };
    }, widgetSelector);
  }

  async function readFirstMetricCell() {
    return page.evaluate((widgetSel) => {
      const cell = document.querySelector(`${widgetSel} table.dataTable tbody tr td.column`);

      return {
        // the hover value is `visibility: hidden` until the column is hovered, so innerText is empty
        value: cell.querySelector('span.value').textContent.trim(),
        hover: cell.querySelector('span.ratio').textContent.trim(),
      };
    }, widgetSelector);
  }

  it('should render the report header once, with no widget controls', async function () {
    await loadWidget();

    const layout = await readHeaderLayout();
    expect(layout.headers).to.be.equal(1);
    expect(layout.headingTag).to.be.equal('H2');
    // the widget name from the metadata still wins over the report's own title
    expect(layout.title).to.be.equal('Device type');
    expect(layout.widgetControls).to.be.equal(0);
    // the actions moved into the header: nothing is rendered around the table any more
    expect(layout.actionBarsInTable).to.be.equal(0);
    expect(layout.actionsInHeader).to.be.equal(1);
  });

  it('should keep the header across sorting, limit and visualization changes', async function () {
    await loadWidget();

    await interactWithColumnSortingAndLimit();
    expect((await readHeaderLayout()).headers).to.be.equal(1);

    await changeVisualization();
    expect((await readHeaderLayout()).headers).to.be.equal(1);

    await toggleTotalsRow();
    const layout = await readHeaderLayout();
    expect(layout.headers).to.be.equal(1);
    expect(layout.title).to.be.equal('Device type');
  });

  it('should render the header of a report without data', async function () {
    await page.goto(emptyDevicesUrl);
    await page.waitForNetworkIdle();
    await page.waitForSelector(`${widgetSelector} .dataTable.isDataTableEmpty`, { visible: true });
    await page.waitForNetworkIdle();

    const layout = await readHeaderLayout();
    expect(layout.headers).to.be.equal(1);
    expect(layout.title).to.be.equal('Device type');
  });

  // A report shown without a content block card keeps the metrics of the plain `h2` it used to
  // be, and ReportHeader.less carries hand-copied values to do so. Compare against a real `h2`
  // rendered in the same page so the copies fail loudly if the global heading styles ever change.
  it('should give a report shown without a card the metrics of a plain heading', async function () {
    const ecommerceLog = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09"
      + "#?idSite=1&period=year&date=2012-08-09&category=Goals_Ecommerce&subcategory=Goals_EcommerceLog";

    await page.goto(ecommerceLog);
    await page.waitForNetworkIdle();
    await page.waitForSelector('.reportHeader--plainTitle', { visible: true });

    const parity = await page.evaluate(() => {
      const header = document.querySelector('.reportHeader--plainTitle');
      const title = header.querySelector('.reportHeader__title');
      const probe = document.createElement('h2');
      probe.textContent = title.innerText;
      header.parentNode.insertBefore(probe, header);
      const of = getComputedStyle(title);
      const op = getComputedStyle(probe);
      const result = {
        height: [header.offsetHeight, probe.offsetHeight],
        fontSize: [of.fontSize, op.fontSize],
        lineHeight: [of.lineHeight, op.lineHeight],
        color: [of.color, op.color],
      };
      probe.remove();
      return result;
    });

    Object.keys(parity).forEach((k) => {
      expect(parity[k][0], k).to.be.equal(parity[k][1]);
    });
  });

  it('should allow saving of preference for normal user when changing sorting and table limits', async function () {
    await loadWidget();
    const reportPage = await page.$('.reporting-page');
    expect(reportPage).to.be.ok;

    const ajaxRequestCount = await trackViewDataTableRequests(async () => {
      await interactWithColumnSortingAndLimit();
    });

    expect(ajaxRequestCount).to.be.equal(3);
  });

  it('should allow saving preference when changing the visualization of the devices widget', async function () {
    await loadWidget();

    const ajaxRequestCount = await trackViewDataTableRequests(async () => {
      await changeVisualization();
    });

    expect(ajaxRequestCount).to.be.equal(1);
  });

  it('should allow saving preference when toggling the totals row via the configuration menu', async function () {
    await loadWidget();

    const ajaxRequestCount = await trackViewDataTableRequests(async () => {
      await toggleTotalsRow(2);
    });

    expect(ajaxRequestCount).to.be.equal(2);
  });

  // The configure icon used to be highlighted while a non-default setting was active. There is no
  // configure icon any more, so only the swap itself is asserted here; the menu entry's own wording
  // and accessible name are covered in DataTableActions.component.spec.ts.
  it('should swap absolute and percentage values when the percentage setting is toggled', async function () {
    await loadWidget();

    const absolute = await readFirstMetricCell();
    expect(absolute.value).to.match(/^[\d,.]+$/);
    expect(absolute.hover).to.match(/%$/);

    await togglePercentageValues();

    const percentage = await readFirstMetricCell();
    expect(percentage.value).to.be.equal(absolute.hover);
    expect(percentage.hover).to.be.equal(absolute.value);

    await togglePercentageValues();

    const restored = await readFirstMetricCell();
    expect(restored.value).to.be.equal(absolute.value);
    expect(restored.hover).to.be.equal(absolute.hover);
  });

  it('should allow saving preference when toggling percentage values via the configuration menu', async function () {
    await loadWidget();

    const ajaxRequestCount = await trackViewDataTableRequests(async () => {
      await togglePercentageValues(2);
    });

    expect(ajaxRequestCount).to.be.equal(2);
  });

  it('should keep showing percentages in an expanded subtable', async function () {
    const subtablesUrl = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers"
      + "&actionToWidgetize=getWebsites&idSite=1&period=year&date=2012-01-12&viewDataTable=table"
      + "&show_percentage_values=1";

    await page.goto(subtablesUrl);
    await page.waitForNetworkIdle();
    await page.waitForSelector('tr.subDataTable', { visible: true });

    await page.click('tr.subDataTable td.label');
    await page.waitForNetworkIdle();
    await page.waitForSelector('.subDataTableContainer table.dataTable tbody tr td.column', { visible: true });

    const subtableCell = await page.evaluate(() => {
      const cell = document.querySelector('.subDataTableContainer table.dataTable tbody tr td.column');

      return {
        value: cell.querySelector('span.value').textContent.trim(),
        hover: cell.querySelector('span.ratio')?.textContent.trim() ?? null,
      };
    });

    expect(subtableCell.value).to.match(/%$/);
    expect(subtableCell.hover).to.match(/^[\d,.]+$/);
  });

  it('should show percentages on comparison rows as well as on their parent row', async function () {
    const comparingUrl = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers"
      + "&actionToWidgetize=getWebsites&idSite=1&period=year&date=2012-01-12&viewDataTable=table"
      + "&compareDates[]=2011-01-31&comparePeriods[]=year&show_percentage_values=1";

    await page.goto(comparingUrl);
    await page.waitForNetworkIdle();
    await page.waitForSelector('tr.comparisonRow', { visible: true });

    const cells = await page.evaluate(() => ['tr.parentComparisonRow', 'tr.comparisonRow']
      .map((rowSelector) => {
        const cell = document.querySelector(`${rowSelector} td.column`);

        return {
          value: cell.querySelector('span.value').textContent.trim(),
          hover: cell.querySelector('span.ratio').textContent.trim(),
        };
      }));

    cells.forEach((cell) => {
      expect(cell.value).to.match(/%$/);
      // comparison rows append their period over period change, which stays next to the
      // hover value exactly as it did before percentages could be shown
      expect(cell.hover).to.match(/^[\d,.]+( \([+-][\d,.]+%\))?$/);
    });
  });

  describe('As anonymous user', function () {
    before(async function () {
      await testEnvironment.callApi('UsersManager.setUserAccess', {
        userLogin: 'anonymous',
        access: 'view',
        idSites: [3],
      });
      testEnvironment.testUseMockAuth = 0;
      await testEnvironment.save();
    });

    after(async function () {
      testEnvironment.testUseMockAuth = 1;
      await testEnvironment.save();
    });

    it('should not save preferences when anonymous user performs datatable actions', async function () {
      await loadWidget();

      const ajaxRequestCount = await trackViewDataTableRequests(async () => {
        await interactWithColumnSortingAndLimit();
      });

      expect(ajaxRequestCount).to.be.equal(0);
    });

    it('should not save preferences when anonymous user changes visualization', async function () {
      await loadWidget();

      const ajaxRequestCount = await trackViewDataTableRequests(async () => {
        await changeVisualization();
      });

      expect(ajaxRequestCount).to.be.equal(0);
    });

    it('should not save preferences when anonymous user toggles totals row', async function () {
      await loadWidget();

      const ajaxRequestCount = await trackViewDataTableRequests(async () => {
        await toggleTotalsRow(2);
      });

      expect(ajaxRequestCount).to.be.equal(0);
    });
  });
});
