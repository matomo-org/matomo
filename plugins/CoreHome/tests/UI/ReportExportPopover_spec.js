/*!
 * Matomo - free/libre analytics platform
 *
 * ReportExportPopover UI tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('ReportExportPopover', function () {
  this.fixture = "Piwik\\Tests\\Fixtures\\UITestFixture";

  const url = "?module=CoreHome&action=index&idSite=1&period=day&date=2012-08-09"
    + "#?idSite=1&period=day&date=2012-08-09&category=General_Actions&subcategory=General_Pages";

  async function isOptionExpandSubtableVisible() {
    return await page.evaluate(() => (
      $('#reportExport div[name="option_expanded"] div.form-group.matomo-form-field').is(':visible')
    ));
  }
  async function isOptionVisible(optionName) {
    return await page.evaluate((nameValue) => (
      $(`#reportExport div[name="${nameValue}"] div.form-group.matomo-form-field`).is(':visible')
    ), optionName);
  }
  async function clickFormat(format){
    await page.evaluate((formatValue) => {
      const selector = `#reportExport input[name="format"][value="${formatValue}"]`;
      const input = document.querySelector(selector);
      if (input) {
        input.click();
      }
    }, format);
  }
  async function clickOption(optionName) {
    await page.evaluate((nameValue) => {
      const selector = `#reportExport div[name="${nameValue}"] input[type="checkbox"][name="${nameValue}"]`;
      const input = document.querySelector(selector);
      if (input) {
        input.click();
      }
    }, optionName);
  }
  async function expectOptionChecked(optionName, expected) {
    const selector = `#reportExport div[name="${optionName}"] input[type="checkbox"]`;
    await page.waitForSelector(selector);
    const actual = await page.evaluate((sel) => {
      const input = document.querySelector(sel);
      return input ? input.checked : null;
    }, selector);
    expect(actual, `option ${optionName} checked state`).to.equal(expected);
  }
  async function expectExportLinkContains(substring) {
    const href = await page.evaluate(() => (
      document.querySelector('#reportExport a.btn')?.getAttribute('href') || ''
    ));
    expect(href).to.contain(substring);
  }

  async function expectExportLinkNotContains(substring) {
    const href = await page.evaluate(() => (
      document.querySelector('#reportExport a.btn')?.getAttribute('href') || ''
    ));
    expect(href).to.not.contain(substring);
  }

  it('should hide expanded option when CSV, TSV or HTML format is selected and show it for everything else', async function () {
    await page.goto(url);
    await page.waitForNetworkIdle();
    await page.waitForSelector('#widgetActionsgetPageUrls', { visible: true });
    await page.waitForSelector('#widgetActionsgetPageUrls .dataTable', { visible: true });
    await page.waitForFunction(() => (
      !!document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection')
    ));
    await page.evaluate(() => {
      const button = document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection');
      if (button) {
        button.click();
      }
    });
    await page.waitForSelector('#reportExport', { visible: true });
    const formatsToCheck = ['CSV', 'JSON', 'TSV', 'HTML', 'RSS', 'XML'];
    const formatsToHideExpanded = ['CSV', 'TSV', 'HTML'];

    for (const format of formatsToCheck) {
      await clickFormat(format);
      const shouldShowExpanded = !formatsToHideExpanded.includes(format);
      await page.waitForFunction(
        (formatValue) => (
          document.querySelector(`#reportExport input[name="format"][value="${formatValue}"]`)?.checked === true
        ),
        {},
        format,
      );
      const optionIsExpanded = await isOptionExpandSubtableVisible();
      expect(optionIsExpanded, `format ${format} should ${shouldShowExpanded ? 'show' : 'hide'} expanded option`)
        .to.equal(shouldShowExpanded);
    }

  });

  it('should default to TSV with flat selected on open, then keep user subtable selection when switching formats', async function () {
    await page.goto(url);
    await page.waitForNetworkIdle();
    await page.waitForSelector('#widgetActionsgetPageUrls', { visible: true });
    await page.waitForSelector('#widgetActionsgetPageUrls .dataTable', { visible: true });
    await page.waitForFunction(() => (
      !!document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection')
    ));
    await page.evaluate(() => {
      const button = document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection');
      if (button) {
        button.click();
      }
    });
    await page.waitForSelector('#reportExport', { visible: true });

    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="TSV"]')?.checked === true
    ));
    // Intended default on open: TSV + flat (when flat export is available).
    await expectOptionChecked('option_flat', true);
    await expectOptionChecked('option_expanded', false);
    await expectExportLinkContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    await clickFormat('JSON');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="JSON"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', false);
    await expectOptionChecked('option_expanded', true);
    await expectExportLinkNotContains('flat=1');
    await expectExportLinkContains('expanded=1');

    // CSV/TSV do not support expanded. In default mode, CSV/TSV keep flat checked.
    await clickFormat('CSV');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="CSV"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', true);
    await expectExportLinkContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    await clickFormat('JSON');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="JSON"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', false);
    await expectOptionChecked('option_expanded', true);
    await expectExportLinkNotContains('flat=1');
    await expectExportLinkContains('expanded=1');

    // Clear both options and make sure this preference is remembered across format switches.
    await clickOption('option_expanded');
    await expectOptionChecked('option_flat', false);
    await expectOptionChecked('option_expanded', false);
    await expectExportLinkNotContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    await clickFormat('TSV');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="TSV"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', false);
    await expectExportLinkNotContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    await clickFormat('JSON');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="JSON"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', false);
    await expectOptionChecked('option_expanded', false);
    await expectExportLinkNotContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    // Switch to flat preference and verify it is remembered across format switches.
    await clickOption('option_flat');
    await expectOptionChecked('option_flat', true);
    await expectOptionChecked('option_expanded', false);
    await expectExportLinkContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    await clickFormat('CSV');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="CSV"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', true);
    await expectExportLinkContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    // Unticking in CSV should immediately remove flat=1 and be remembered across formats.
    await clickOption('option_flat');
    await expectOptionChecked('option_flat', false);
    await expectExportLinkNotContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    await clickFormat('XML');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="XML"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', false);
    await expectOptionChecked('option_expanded', false);
    await expectExportLinkNotContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    await clickFormat('CSV');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="CSV"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', false);
    await expectExportLinkNotContains('flat=1');
    await expectExportLinkNotContains('expanded=1');
  });

  it('should keep subtable controls available when table is flat and subtables count is zero', async function () {
    await page.goto(url);
    await page.waitForNetworkIdle();
    await page.waitForSelector('#widgetActionsgetPageUrls', { visible: true });
    await page.waitForSelector('#widgetActionsgetPageUrls .dataTable', { visible: true });
    await page.waitForFunction(() => (
      !!document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection')
    ));
    await page.evaluate(() => {
      const reportElement = document.querySelector('#widgetActionsgetPageUrls [data-report]');
      if (!reportElement) {
        return;
      }

      const $reportElement = window.$(reportElement);
      const uiControlObject = $reportElement.data('uiControlObject');
      if (!uiControlObject || !uiControlObject.param) {
        return;
      }

      uiControlObject.numberOfSubtables = 0;
      uiControlObject.param.flat = 1;
      $reportElement.data('uiControlObject', uiControlObject);

      const button = document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection');
      if (button) {
        button.click();
      }
    });
    await page.waitForSelector('#reportExport', { visible: true });

    expect(await isOptionVisible('option_flat')).to.equal(true);
    await expectExportLinkContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    await clickOption('option_flat');
    await expectOptionChecked('option_flat', false);
    await expectExportLinkNotContains('flat=1');

    await clickFormat('CSV');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="CSV"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', false);
    await expectExportLinkNotContains('flat=1');

    await clickFormat('TSV');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="TSV"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', false);
    await expectExportLinkNotContains('flat=1');
  });

  it('should keep flat export available but hide expanded export when a flattenable report has no current subtables', async function () {
    await page.goto(url);
    await page.waitForNetworkIdle();
    await page.waitForSelector('#widgetActionsgetPageUrls', { visible: true });
    await page.waitForSelector('#widgetActionsgetPageUrls .dataTable', { visible: true });
    await page.waitForFunction(() => (
      !!document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection')
    ));
    await page.evaluate(() => {
      const reportElement = document.querySelector('#widgetActionsgetPageUrls [data-report]');
      if (!reportElement) {
        return;
      }

      const $reportElement = window.$(reportElement);
      const uiControlObject = $reportElement.data('uiControlObject');
      if (!uiControlObject || !uiControlObject.param) {
        return;
      }

      uiControlObject.numberOfSubtables = 0;
      uiControlObject.param.flat = 0;
      $reportElement.data('uiControlObject', uiControlObject);

      const button = document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection');
      if (button) {
        button.click();
      }
    });
    await page.waitForSelector('#reportExport', { visible: true });

    expect(await isOptionVisible('option_flat')).to.equal(true);
    expect(await isOptionVisible('option_show_dimensions')).to.equal(false);
    expect(await isOptionExpandSubtableVisible()).to.equal(false);
    await expectExportLinkContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    await clickFormat('CSV');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="CSV"]')?.checked === true
    ));
    await expectExportLinkContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    await clickFormat('TSV');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="TSV"]')?.checked === true
    ));
    await expectExportLinkContains('flat=1');
    await expectExportLinkNotContains('expanded=1');
  });

  it('should keep flat export available even when the table flatten action was hidden after initialization', async function () {
    await page.goto(url);
    await page.waitForNetworkIdle();
    await page.waitForSelector('#widgetActionsgetPageUrls', { visible: true });
    await page.waitForSelector('#widgetActionsgetPageUrls .dataTable', { visible: true });
    await page.waitForFunction(() => (
      !!document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection')
    ));
    await page.evaluate(() => {
      const reportElement = document.querySelector('#widgetActionsgetPageUrls [data-report]');
      const actionsElement = document.querySelector('#widgetActionsgetPageUrls [vue-entry="CoreHome.DataTableActions"]');
      if (!reportElement || !actionsElement) {
        return;
      }

      const $reportElement = window.$(reportElement);
      const $actionsElement = window.$(actionsElement);
      const uiControlObject = $reportElement.data('uiControlObject');
      const actionsApp = $actionsElement.data('vueAppInstance');
      if (!uiControlObject || !uiControlObject.param || !actionsApp) {
        return;
      }

      uiControlObject.numberOfSubtables = 0;
      uiControlObject.param.flat = 0;
      $reportElement.data('uiControlObject', uiControlObject);
      actionsApp.showFlattenTable_ = false;

      const button = document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection');
      if (button) {
        button.click();
      }
    });
    await page.waitForSelector('#reportExport', { visible: true });

    expect(await isOptionVisible('option_flat')).to.equal(true);
    expect(await isOptionExpandSubtableVisible()).to.equal(false);
    await expectExportLinkContains('flat=1');
    await expectExportLinkNotContains('expanded=1');
  });

  it('should not restore flat when it was unchecked after being initially enabled', async function () {
    await page.goto(url);
    await page.waitForNetworkIdle();
    await page.waitForSelector('#widgetActionsgetPageUrls', { visible: true });
    await page.waitForSelector('#widgetActionsgetPageUrls .dataTable', { visible: true });
    await page.waitForFunction(() => (
      !!document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection')
    ));
    await page.evaluate(() => {
      const reportElement = document.querySelector('#widgetActionsgetPageUrls [data-report]');
      if (!reportElement) {
        return;
      }

      const $reportElement = window.$(reportElement);
      const uiControlObject = $reportElement.data('uiControlObject');
      if (!uiControlObject || !uiControlObject.param) {
        return;
      }

      uiControlObject.param.flat = 1;
      $reportElement.data('uiControlObject', uiControlObject);

      const button = document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection');
      if (button) {
        button.click();
      }
    });
    await page.waitForSelector('#reportExport', { visible: true });

    await expectOptionChecked('option_flat', true);
    await clickOption('option_flat');
    await expectOptionChecked('option_flat', false);
    await expectOptionChecked('option_expanded', false);

    await clickFormat('CSV');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="CSV"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', false);
    await expectExportLinkNotContains('flat=1');

    await clickFormat('JSON');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="JSON"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', false);
    await expectOptionChecked('option_expanded', false);
    await expectExportLinkNotContains('flat=1');
  });
});
