/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * Covers the popover title of javascripts/SegmentedVisitorLog.js. A classic script, so it is
 * evaluated in the window scope, not imported.
 */

import type { Mock } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const API_METHOD = 'Referrers.getCampaigns';
const ROW_SEGMENT = 'referrerName==Google';
const CAMPAIGN_ROW_SEGMENT = `referrerType==campaign;${ROW_SEGMENT}`;

const TRANSLATIONS: Record<string, string> = {
  General_Segment: 'Segment',
  General_And: 'and',
  Live_SegmentedVisitorLogTitle: 'Visits log showing visits where %1$s is "%2$s"',
};

describe('Live/SegmentedVisitorLog popover title', () => {
  let setTitle: Mock;
  let originalTranslate: unknown;
  let originalRequire: unknown;

  beforeAll(() => {
    window.eval(readFileSync(resolve(__dirname, '../../../javascripts/SegmentedVisitorLog.js'), 'utf8'));
  });

  beforeEach(() => {
    const globals = window as unknown as Record<string, unknown>;
    originalTranslate = globals._pk_translate;
    originalRequire = globals.require;

    // The report the popover was opened from, with the row the clicked filter belongs to
    document.body.innerHTML = `
      <div class="dataTable" data-report="${API_METHOD}">
        <table>
          <tr data-segment-filter="${ROW_SEGMENT}">
            <td class="label"><span class="value">Google</span></td>
          </tr>
        </table>
      </div>
    `;
    const report = window.$(`[data-report="${API_METHOD}"]`);
    report.data('uiControlObject', { getReportMetadata: () => ({ dimension: 'Campaign' }) });

    setTitle = vi.fn();
    globals.Piwik_Popover = {
      showLoading: () => window.$('<div></div>'),
      setContent: () => undefined,
      setTitle,
    };
    globals.require = () => ({ DataTable: { getDataTableByReport: () => report } });
    globals._pk_translate = (key: string, args: string[] = []) => (TRANSLATIONS[key] || key)
      .replace(/%(\d)\$s/g, (match, index) => args[Number(index) - 1]);
    globals.piwik = { ...(globals.piwik as object), visitorLogEnabled: true };
    globals.vueSanitize = (value: string) => value;

    // Answer the visits log request at once, so the title is set synchronously
    globals.ajaxHelper = function AjaxHelperStub(this: Record<string, unknown>) {
      let callback: (html: string) => void;
      this.addParams = () => undefined;
      this.withTokenInUrl = () => undefined;
      this.setFormat = () => undefined;
      this.setCallback = (cb: (html: string) => void) => { callback = cb; };
      this.send = () => callback('<div></div>');
    };
  });

  afterEach(() => {
    const globals = window as unknown as Record<string, unknown>;
    globals._pk_translate = originalTranslate;
    globals.require = originalRequire;
    document.body.innerHTML = '';
  });

  const show = (segment: string, extraParams: Record<string, string> = {}) => {
    (window as any).SegmentedVisitorLog.show(API_METHOD, segment, extraParams);
    return setTitle.mock.calls[setTitle.mock.calls.length - 1][0];
  };

  it('should show the label of the row the clicked filter belongs to', () => {
    expect(show('visitorType==returning', { intersectSegment: ROW_SEGMENT }))
      .toBe('Visits log showing visits where Campaign is "Google"');
  });

  it('should name the condition instead of an empty value when no row has the filter', () => {
    expect(show('referrerName==NoSuchCampaign'))
      .toBe('Visits log showing visits where Segment is "referrerName==NoSuchCampaign"');
  });

  it('should still name the segment when the report has no dimension', () => {
    window.$(`[data-report="${API_METHOD}"]`).data('uiControlObject', { getReportMetadata: () => ({}) });

    expect(show('')).toBe('Visits log showing visits where Segment is ""');
  });

  it('should list every condition of a filter with more than one', () => {
    expect(show('visitorType==returning', { intersectSegment: CAMPAIGN_ROW_SEGMENT }))
      .toBe('Visits log showing visits where Segment is "referrerType==campaign and referrerName==Google"');
  });
});
