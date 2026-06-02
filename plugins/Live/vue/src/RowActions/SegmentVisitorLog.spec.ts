/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { readFileSync } from 'fs';
import { resolve } from 'path';

const CURRENT_PAGE_SEGMENT = 'countryCode==pt;pageUrl==https%253A%252F%252Fexample.org%252Fexampleblue-travel-tips%252Fessential-example%252Fbest-of-example-1%252F';
const DECODED_CURRENT_PAGE_SEGMENT = decodeURIComponent(CURRENT_PAGE_SEGMENT);
const CATEGORY_ROW_SEGMENT = 'pageUrl=^https%253A%252F%252Fexample.org%252Fcategory';
const PAGE_TITLE_ROW_SEGMENT = 'pageTitle==Peek%2BInside%2BParadise%2BSlideshow%2B%257C%2BExample%2BBlue';
const SUFFIX_SEGMENT = 'visitEcommerceStatus==ordered';

describe('Live/SegmentVisitorLog row action', () => {
  let rowActionInstance: { trigger: (tr: JQuery<HTMLElement>, event: Event) => void; openPopover: jest.Mock };
  let openPopoverSpy: jest.SpyInstance;
  let originalRequire: any;
  let originalAjaxHelper: any;
  let originalVisitorLogEnabled: boolean;
  let originalVueSanitize: any;
  let originalTranslate: any;

  beforeAll(async () => {
    window.eval(readFileSync(resolve(__dirname, '../../../../CoreHome/javascripts/dataTable_rowactions.js'), 'utf8'));
    window.eval(readFileSync(resolve(__dirname, '../../../../Live/javascripts/SegmentedVisitorLog.js'), 'utf8'));
    window.eval(readFileSync(resolve(__dirname, '../../../../Live/javascripts/rowaction.js'), 'utf8'));
    await Promise.resolve();
    await Promise.resolve();
  });

  beforeEach(() => {
    document.body.innerHTML = `
      <table>
        <tr id="segment-row" data-segment-filter="${CATEGORY_ROW_SEGMENT}">
          <td class="label">
            <span class="label">category</span>
          </td>
        </tr>
      </table>
      <div
        class="dataTable"
        data-report="Actions.getPageUrls"
      ></div>
      <div
        class="dataTable"
        data-report="Actions.getPageTitles"
      ></div>
    `;

    originalRequire = (window as any).require;
    (window as any).require = (moduleName: string) => {
      if (moduleName === 'piwik/UI') {
        return {
          DataTable: {
            getDataTableByReport: (apiMethod: string) => {
              return document.querySelector(`[data-report="${apiMethod}"]`);
            },
          },
        };
      }

      return originalRequire ? originalRequire(moduleName) : undefined;
    };

    const pageUrlsTable = window.$('[data-report="Actions.getPageUrls"]');
    pageUrlsTable.data('uiControlObject', {
      getReportMetadata: () => ({
        dimension: 'Page URL',
      }),
    });

    const pageTitlesTable = window.$('[data-report="Actions.getPageTitles"]');
    pageTitlesTable.data('uiControlObject', {
      getReportMetadata: () => ({
        dimension: 'Page Title',
      }),
    });

    originalVisitorLogEnabled = (window as any).piwik?.visitorLogEnabled;
    (window as any).piwik = {
      ...(window as any).piwik,
      visitorLogEnabled: true,
    };

    originalAjaxHelper = (window as any).ajaxHelper;
    originalVueSanitize = (window as any).vueSanitize;
    originalTranslate = (window as any)._pk_translate;
    (window as any)._pk_translate = (key: string, args: string[] = []) => `${key} ${args.join(' ')}`;
    (window as any).vueSanitize = (value: string) => value;
    (window as any).ajaxHelper = class {
      callback!: (html: string) => void;

      addParams() {
        return this;
      }

      withTokenInUrl() {
        return this;
      }

      setCallback(callback: (html: string) => void) {
        this.callback = callback;
        return this;
      }

      setFormat() {
        return this;
      }

      send() {
        this.callback(
          '<div><h2 class="enrichedHeadline"><span class="title">Segmented Visits Log</span></h2></div>',
        );
      }
    };

    window.Piwik_Popover = {
      showLoading: jest.fn((title: string) => {
        const popover = window.$(
          `<div id="Piwik_Popover"><h2 class="enrichedHeadline"><span class="title">${title}</span></h2></div>`,
        );
        window.$('body').append(popover);

        return popover;
      }),
      setContent: jest.fn((html: string) => {
        window.$('#Piwik_Popover').html(html);
      }),
      setTitle: jest.fn(),
      onClose: jest.fn(),
      isOpen: jest.fn(() => true),
      close: jest.fn(),
    } as any;

    const action = (window as any).DataTable_RowActions_Registry.getActionByName('SegmentVisitorLog');

    rowActionInstance = action.createInstance({
      param: {
        module: 'Actions',
        action: 'getPageUrls',
        segment: CURRENT_PAGE_SEGMENT,
        date: '2012-08-09',
        period: 'day',
        idSite: 1,
      },
      props: {},
    });

    openPopoverSpy = jest.spyOn(rowActionInstance, 'openPopover').mockImplementation(() => undefined);
  });

  afterEach(() => {
    if (openPopoverSpy) {
      openPopoverSpy.mockRestore();
    }
    (window as any).require = originalRequire;
    (window as any).ajaxHelper = originalAjaxHelper;
    (window as any).vueSanitize = originalVueSanitize;
    (window as any)._pk_translate = originalTranslate;
    (window as any).piwik = {
      ...(window as any).piwik,
      visitorLogEnabled: originalVisitorLogEnabled,
    };
    (window as any).Piwik_Popover = undefined;
    document.body.innerHTML = '';
  });

  it('should keep the current report segment and send the clicked row as a dedicated visitor-log filter', () => {
    const row = window.$('#segment-row');

    rowActionInstance.trigger(row, new window.MouseEvent('click'));

    expect(openPopoverSpy).toHaveBeenCalledWith(
      'Actions.getPageUrls',
      DECODED_CURRENT_PAGE_SEGMENT,
      expect.objectContaining({
        date: '2012-08-09',
        period: 'day',
        additionalSegment: CATEGORY_ROW_SEGMENT,
      }),
    );
  });

  it('should append the configured visitor log suffix to the current segment and keep the clicked row separate', () => {
    rowActionInstance = (window as any).DataTable_RowActions_Registry.getActionByName('SegmentVisitorLog').createInstance({
      param: {
        module: 'Actions',
        action: 'getPageUrls',
        segment: CURRENT_PAGE_SEGMENT,
        date: '2012-08-09',
        period: 'day',
        idSite: 1,
      },
      props: {
        segmented_visitor_log_segment_suffix: SUFFIX_SEGMENT,
      },
    });
    openPopoverSpy = jest.spyOn(rowActionInstance, 'openPopover').mockImplementation(() => undefined);

    rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

    expect(openPopoverSpy).toHaveBeenCalledWith(
      'Actions.getPageUrls',
      `${DECODED_CURRENT_PAGE_SEGMENT};${SUFFIX_SEGMENT}`,
      expect.objectContaining({
        date: '2012-08-09',
        period: 'day',
        additionalSegment: CATEGORY_ROW_SEGMENT,
      }),
    );
  });

  it('should render the popup title from the clicked row label instead of the raw combined segment', () => {
    window.$('[data-report="Actions.getPageTitles"] tr').remove();
    window.$('[data-report="Actions.getPageTitles"]').append(`
      <table>
        <tr data-segment-filter="${PAGE_TITLE_ROW_SEGMENT}">
          <td class="label">
            <span class="value">Peek Inside Paradise Slideshow | Example Blue</span>
          </td>
        </tr>
      </table>
    `);

    (window as any).SegmentedVisitorLog.show('Actions.getPageTitles', DECODED_CURRENT_PAGE_SEGMENT, {
      additionalSegment: PAGE_TITLE_ROW_SEGMENT,
    });

    const setTitleCalls = (window.Piwik_Popover.setTitle as jest.Mock).mock.calls;
    const finalTitle = setTitleCalls[setTitleCalls.length - 1][0];

    expect(finalTitle).toContain('Peek Inside Paradise Slideshow | Example Blue');
    expect(finalTitle).not.toContain('pageUrl==');
    expect(finalTitle).not.toContain('pageTitle==');
  });
});
