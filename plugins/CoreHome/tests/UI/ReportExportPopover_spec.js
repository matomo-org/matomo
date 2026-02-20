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

  it('should hide expanded option when CSV or TSV format is selected and show it for everything else', async function () {
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
    const formatsToHideExpanded = ['CSV', 'TSV'];

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

  it('should keep subtable option selection when switching formats and not force flat for CSV/TSV', async function () {
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

    await clickFormat('JSON');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="JSON"]')?.checked === true
    ));
    await expectOptionChecked('option_expanded', true);

    await clickFormat('CSV');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="CSV"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', false);
    await expectExportLinkNotContains('flat=1');
    await expectExportLinkContains('expanded=1');

    await clickFormat('HTML');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="HTML"]')?.checked === true
    ));
    await expectOptionChecked('option_expanded', true);
    // Now we want to set the option_flat to check later that it remembers the choice we had
    // and not return to its default 'option_expanded'
    await clickOption('option_flat')

    await clickFormat('TSV');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="TSV"]')?.checked === true
    ));
    await expectOptionChecked('option_flat', true);
    await expectExportLinkContains('flat=1');
    await expectExportLinkNotContains('expanded=1');

    await clickFormat('XML');
    await page.waitForFunction(() => (
      document.querySelector('#reportExport input[name="format"][value="XML"]')?.checked === true
    ));
    // Check here that it 'remembered' the previous choice that option_flat
    await expectOptionChecked('option_flat', true);

    // Now we want to uncheck option_flat so we can check later
    // that it 'remembers' when both options are unchecked
    await clickOption('option_flat')
    await expectOptionChecked('option_flat', false);
    await expectOptionChecked('option_expanded', false);

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
    // We check here that it remembers both options should stay unchecked
    await expectOptionChecked('option_expanded', false);
    await expectOptionChecked('option_flat', false);

  });

  it('should hide subtable controls but preserve flat export when no subtables are available and flat is preset', async function () {
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

    expect(await isOptionVisible('option_flat')).to.equal(false);
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
});
