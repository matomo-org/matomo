/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { readFileSync } from 'fs';
import { resolve } from 'path';

// Comparison of two segments over a single period: series 0 = "All Visits" (empty segment),
// series 1 = "countryCode==pt". This is the array the app exposes on MatomoUrl, and the index the
// row-evolution action already reads via `data-comparison-series`
// (plugins/CoreHome/javascripts/dataTable_rowactions.js:296-332).
const COMPARE_SEGMENTS = ['', 'countryCode==pt'];
const CLICKED_SERIES_SEGMENT = 'countryCode==pt';
const CLICKED_ROW_SEGMENT = 'pageUrl=@example';

/**
 * Regression coverage for a known, unfixed bug (ticket dev-20553): the "Segmented visits log" row
 * action ignores comparison segments. When the report is in comparison mode and the visits-log icon
 * is clicked on a row belonging to a compared series, the popover should be scoped to THAT series'
 * segment. Today the action never looks at `data-comparison-series` nor
 * `MatomoUrl.parsed.value.compareSegments` (and `filterAllowedExtraParams` drops every `compare*`
 * key), so the compared segment is silently dropped and the popover is scoped to the report's base
 * segment only -- which is why comparison-mode visitors logs come back empty / wrong.
 *
 * This test asserts the expected (post-fix) behaviour and therefore FAILS on current code, confirming
 * the bug. It should turn green once the row action is made comparison-aware.
 */
describe('Live/SegmentVisitorLog row action in comparison mode', () => {
  let rowActionInstance: {
    trigger: (tr: JQuery<HTMLElement>, event: Event) => void;
    openPopover: (apiMethod: string, segment: string, extraParams: unknown) => void;
  };

  beforeAll(() => {
    window.eval(readFileSync(resolve(__dirname, '../../../../CoreHome/javascripts/dataTable_rowactions.js'), 'utf8'));
    window.eval(readFileSync(resolve(__dirname, '../../../../Live/javascripts/rowaction.js'), 'utf8'));
  });

  beforeEach(() => {
    // App is in comparison mode. The row-evolution action reads exactly this to resolve a clicked
    // series to its segment; the segmented visitor log must do the same.
    (window as any).CoreHome = {
      MatomoUrl: { parsed: { value: { compareSegments: COMPARE_SEGMENTS } } },
    };

    // The clicked row belongs to comparison series 1 (the "countryCode==pt" series).
    document.body.innerHTML = `
      <table>
        <tr id="segment-row" class="comparisonRow" data-comparison-series="1"
            data-segment-filter="${CLICKED_ROW_SEGMENT}">
          <td class="label"><span class="value">example</span></td>
        </tr>
      </table>
    `;

    const action = (window as any).DataTable_RowActions_Registry.getActionByName('SegmentVisitorLog');

    // In comparison mode the page's base segment is "All Visits"; the segment that matters lives in
    // compareSegments for the clicked series.
    rowActionInstance = action.createInstance({
      param: {
        module: 'Actions',
        action: 'getPageUrls',
        segment: '',
        date: '2012-08-09',
        period: 'day',
        idSite: 1,
      },
      props: {},
    });
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  it('scopes the visitor log to the clicked comparison series segment', () => {
    const openPopoverSpy = vi
      .spyOn(rowActionInstance, 'openPopover')
      .mockImplementation(() => undefined);

    rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

    // The compared series' segment (countryCode==pt) must reach the popover request, otherwise the
    // visitor log shows the wrong series' visits (or none). It is currently dropped entirely.
    expect(openPopoverSpy).toHaveBeenCalledWith(
      'Actions.getPageUrls',
      expect.stringContaining(CLICKED_SERIES_SEGMENT),
      expect.anything(),
    );
  });
});
