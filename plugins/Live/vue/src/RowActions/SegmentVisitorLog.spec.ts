/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import type { Mock, MockInstance } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const CURRENT_PAGE_SEGMENT = 'countryCode==pt;pageUrl==https%253A%252F%252Fexample.org%252Fexampleblue-travel-tips%252Fessential-example%252Fbest-of-example-1%252F';
const DECODED_CURRENT_PAGE_SEGMENT = decodeURIComponent(CURRENT_PAGE_SEGMENT);
const CATEGORY_ROW_SEGMENT = 'pageUrl=^https%253A%252F%252Fexample.org%252Fcategory';
const SUFFIX_SEGMENT = 'visitEcommerceStatus==ordered';

describe('Live/SegmentVisitorLog row action', () => {
  let rowActionInstance: {
    trigger: (tr: JQuery<HTMLElement>, event: Event) => void;
    openPopover: Mock;
    doOpenPopover: (urlParam: string) => void;
  };
  let openPopoverSpy: MockInstance;

  beforeAll(async () => {
    window.eval(readFileSync(resolve(__dirname, '../../../../CoreHome/javascripts/dataTable_rowactions.js'), 'utf8'));
    window.eval(readFileSync(resolve(__dirname, '../../../../Live/javascripts/rowaction.js'), 'utf8'));
    await Promise.resolve();
    await Promise.resolve();
  });

  beforeEach(() => {
    document.body.innerHTML = `
      <table>
        <tr id="segment-row" data-segment-filter="${CATEGORY_ROW_SEGMENT}">
          <td class="label">
            <span class="value">category</span>
          </td>
        </tr>
      </table>
    `;

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

    openPopoverSpy = vi.spyOn(rowActionInstance, 'openPopover').mockImplementation(() => undefined);
  });

  afterEach(() => {
    if (openPopoverSpy) {
      openPopoverSpy.mockRestore();
    }
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
        intersectSegment: CATEGORY_ROW_SEGMENT,
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
    openPopoverSpy = vi.spyOn(rowActionInstance, 'openPopover').mockImplementation(() => undefined);

    rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

    expect(openPopoverSpy).toHaveBeenCalledWith(
      'Actions.getPageUrls',
      `${DECODED_CURRENT_PAGE_SEGMENT};${SUFFIX_SEGMENT}`,
      expect.objectContaining({
        date: '2012-08-09',
        period: 'day',
        intersectSegment: CATEGORY_ROW_SEGMENT,
      }),
    );
  });

  it('should use only the suffix as the main segment and keep the clicked row at the visit level when there is no current segment', () => {
    rowActionInstance = (window as any).DataTable_RowActions_Registry.getActionByName('SegmentVisitorLog').createInstance({
      param: {
        module: 'Actions',
        action: 'getPageUrls',
        segment: '',
        date: '2012-08-09',
        period: 'day',
        idSite: 1,
      },
      props: {
        segmented_visitor_log_segment_suffix: SUFFIX_SEGMENT,
      },
    });
    openPopoverSpy = vi.spyOn(rowActionInstance, 'openPopover').mockImplementation(() => undefined);

    rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

    // The clicked row must NOT also be appended to the main segment: it is intersected at the visit
    // level only, consistent with the current-segment cases above.
    expect(openPopoverSpy).toHaveBeenCalledWith(
      'Actions.getPageUrls',
      SUFFIX_SEGMENT,
      expect.objectContaining({
        date: '2012-08-09',
        period: 'day',
        intersectSegment: CATEGORY_ROW_SEGMENT,
      }),
    );
  });

  describe('on a comparison row', () => {
    const REPORT_SEGMENT = 'countryCode==it';
    const COMPARED_SEGMENT = 'countryCode==de';

    // Comparison rows carry their series' segment (none for "All visits"), period and date in
    // data-param-override, rendered by CoreVisualizations/templates/_dataTableViz_htmlTable_comparisons.twig
    const setUpComparisonRow = (seriesSegment: string | null, suffix: string, rowFilter = CATEGORY_ROW_SEGMENT) => {
      const paramOverride: Record<string, string> = {
        period: 'month',
        comparePeriods: '',
        date: '2026-07-15',
        compareDates: '',
      };
      if (seriesSegment) {
        paramOverride.segment = seriesSegment;
        paramOverride.compareSegments = '';
      }
      window.$('#segment-row')
        .addClass('comparisonRow')
        .attr('data-segment-filter', rowFilter)
        .attr('data-param-override', JSON.stringify(paramOverride));

      rowActionInstance = (window as any).DataTable_RowActions_Registry.getActionByName('SegmentVisitorLog').createInstance({
        param: {
          module: 'Goals',
          action: 'getReferrerType',
          segment: REPORT_SEGMENT,
          date: '2026-08-15',
          period: 'month',
          idSite: 1,
        },
        props: {
          segmented_visitor_log_segment_suffix: suffix,
        },
      });
      openPopoverSpy = vi.spyOn(rowActionInstance, 'openPopover').mockImplementation(() => undefined);
    };

    // The report row the comparison rows belong to, with the filter it has once the report's queued
    // filters ran (Referrers prepends referrerType==campaign after the comparison rows are built)
    const CAMPAIGN_ROW_SEGMENT = 'referrerName==Google';
    const CAMPAIGN_REPORT_ROW_SEGMENT = `referrerType==campaign;${CAMPAIGN_ROW_SEGMENT}`;
    const addReportRow = (reportRowFilter: string, comparisonRowsInBetween = 0) => {
      const row = window.$('#segment-row');
      row.before(`<tr class="parentComparisonRow" data-segment-filter="${reportRowFilter}"></tr>`);
      for (let i = 0; i < comparisonRowsInBetween; i += 1) {
        const filter = `${COMPARED_SEGMENT};${CAMPAIGN_ROW_SEGMENT}`;
        row.before(`<tr class="comparisonRow" data-segment-filter="${filter}"></tr>`);
      }
    };

    it('should keep the visitor log suffix instead of letting the row override replace the segment', () => {
      setUpComparisonRow(REPORT_SEGMENT, SUFFIX_SEGMENT);

      rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

      expect(openPopoverSpy).toHaveBeenCalledWith(
        'Goals.getReferrerType',
        `${REPORT_SEGMENT};${SUFFIX_SEGMENT}`,
        expect.objectContaining({
          intersectSegment: CATEGORY_ROW_SEGMENT,
        }),
      );
      expect(openPopoverSpy.mock.calls[0][2]).not.toHaveProperty('segment');
    });

    it('should use the segment of a compared series instead of the report segment', () => {
      setUpComparisonRow(COMPARED_SEGMENT, SUFFIX_SEGMENT);

      rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

      expect(openPopoverSpy).toHaveBeenCalledWith(
        'Goals.getReferrerType',
        `${COMPARED_SEGMENT};${SUFFIX_SEGMENT}`,
        expect.objectContaining({
          intersectSegment: CATEGORY_ROW_SEGMENT,
        }),
      );
    });

    it('should not apply the report segment to a row of the "All visits" series', () => {
      setUpComparisonRow(null, '');

      rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

      expect(openPopoverSpy).toHaveBeenCalledWith(
        'Goals.getReferrerType',
        CATEGORY_ROW_SEGMENT,
        expect.not.objectContaining({
          intersectSegment: expect.anything(),
        }),
      );
    });

    it('should use only the suffix as the main segment for a row of the "All visits" series', () => {
      setUpComparisonRow(null, SUFFIX_SEGMENT);

      rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

      expect(openPopoverSpy).toHaveBeenCalledWith(
        'Goals.getReferrerType',
        SUFFIX_SEGMENT,
        expect.objectContaining({
          intersectSegment: CATEGORY_ROW_SEGMENT,
        }),
      );
    });

    it('should pass an encoded series segment through without decoding it again', () => {
      const encodedSeriesSegment = 'countryName==United%2520States';
      setUpComparisonRow(encodedSeriesSegment, '');

      rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

      expect(openPopoverSpy).toHaveBeenCalledWith(
        'Goals.getReferrerType',
        encodedSeriesSegment,
        expect.objectContaining({
          intersectSegment: CATEGORY_ROW_SEGMENT,
        }),
      );
    });

    it('should send the filter of the report row instead of the comparison row filter', () => {
      // The comparison row filter starts with the series segment and misses referrerType==campaign
      setUpComparisonRow(COMPARED_SEGMENT, '', `${COMPARED_SEGMENT};${CAMPAIGN_ROW_SEGMENT}`);
      addReportRow(CAMPAIGN_REPORT_ROW_SEGMENT);

      rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

      expect(openPopoverSpy).toHaveBeenCalledWith(
        'Goals.getReferrerType',
        COMPARED_SEGMENT,
        expect.objectContaining({
          intersectSegment: CAMPAIGN_REPORT_ROW_SEGMENT,
        }),
      );
    });

    it('should send the filter of the report row and still keep the suffix', () => {
      setUpComparisonRow(COMPARED_SEGMENT, SUFFIX_SEGMENT, `${COMPARED_SEGMENT};${CAMPAIGN_ROW_SEGMENT}`);
      addReportRow(CAMPAIGN_REPORT_ROW_SEGMENT);

      rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

      expect(openPopoverSpy).toHaveBeenCalledWith(
        'Goals.getReferrerType',
        `${COMPARED_SEGMENT};${SUFFIX_SEGMENT}`,
        expect.objectContaining({
          intersectSegment: CAMPAIGN_REPORT_ROW_SEGMENT,
        }),
      );
    });

    it('should send the filter of the report row for a row of the "All visits" series', () => {
      setUpComparisonRow(null, '', CAMPAIGN_ROW_SEGMENT);
      addReportRow(CAMPAIGN_REPORT_ROW_SEGMENT);

      rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

      expect(openPopoverSpy).toHaveBeenCalledWith(
        'Goals.getReferrerType',
        CAMPAIGN_REPORT_ROW_SEGMENT,
        expect.not.objectContaining({
          intersectSegment: expect.anything(),
        }),
      );
    });

    it('should find the report row from a comparison row further down the series', () => {
      setUpComparisonRow(COMPARED_SEGMENT, '', `${COMPARED_SEGMENT};${CAMPAIGN_ROW_SEGMENT}`);
      addReportRow(CAMPAIGN_REPORT_ROW_SEGMENT, 2);

      rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

      expect(openPopoverSpy).toHaveBeenCalledWith(
        'Goals.getReferrerType',
        COMPARED_SEGMENT,
        expect.objectContaining({
          intersectSegment: CAMPAIGN_REPORT_ROW_SEGMENT,
        }),
      );
    });

    it('should keep the comparison row filter when there is no report row filter to use', () => {
      setUpComparisonRow(COMPARED_SEGMENT, '', COMPARED_SEGMENT);

      rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

      expect(openPopoverSpy).toHaveBeenCalledWith(
        'Goals.getReferrerType',
        COMPARED_SEGMENT,
        expect.objectContaining({
          intersectSegment: COMPARED_SEGMENT,
        }),
      );
    });

    it('should not take the filter of an earlier report row when its own report row has none', () => {
      // e.g. a "not defined" row, which renders an empty filter
      setUpComparisonRow(COMPARED_SEGMENT, '', COMPARED_SEGMENT);
      addReportRow(CAMPAIGN_REPORT_ROW_SEGMENT);
      addReportRow('');

      rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

      expect(openPopoverSpy).toHaveBeenCalledWith(
        'Goals.getReferrerType',
        COMPARED_SEGMENT,
        expect.objectContaining({
          intersectSegment: COMPARED_SEGMENT,
        }),
      );
    });

    it('should still open the period of the clicked comparison row', () => {
      setUpComparisonRow(REPORT_SEGMENT, SUFFIX_SEGMENT);

      rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

      expect(openPopoverSpy.mock.calls[0][2]).toEqual(expect.objectContaining({
        date: '2026-07-15',
        period: 'month',
      }));
    });
  });

  it('should preserve intersectSegment through the popover URL round-trip and pass it to the visitor log', () => {
    // The cases above stop at openPopover, before doOpenPopover() re-parses the serialized payload
    // and runs it through the allowlist filter. This drives the full round-trip to prove
    // intersectSegment is on the allowlist and actually reaches SegmentedVisitorLog.show(); if it
    // were dropped, only the report segment would survive and the visit-level fix would be defeated.
    const showSpy = vi.fn();
    (window as any).SegmentedVisitorLog = { show: showSpy };
    const propagateSpy = vi.fn();
    (window as any).broadcast = { propagateNewPopoverParameter: propagateSpy };

    // Use the real openPopover so the extraParams are serialized exactly as in production.
    openPopoverSpy.mockRestore();

    rowActionInstance.trigger(window.$('#segment-row'), new window.MouseEvent('click'));

    // broadcast receives 'SegmentVisitorLog:' + urlParam; doOpenPopover() expects the urlParam alone.
    const [popoverName, popoverParam] = propagateSpy.mock.calls[0];
    expect(popoverName).toBe('RowAction');
    const urlParam = (popoverParam as string).replace(/^SegmentVisitorLog:/, '');

    rowActionInstance.doOpenPopover(urlParam);

    expect(showSpy).toHaveBeenCalledWith(
      'Actions.getPageUrls',
      DECODED_CURRENT_PAGE_SEGMENT,
      expect.objectContaining({
        date: '2012-08-09',
        period: 'day',
        intersectSegment: CATEGORY_ROW_SEGMENT,
      }),
    );
  });

  it('should drop keys that are not on the allowlist during the popover URL round-trip', () => {
    const showSpy = vi.fn();
    (window as any).SegmentedVisitorLog = { show: showSpy };

    openPopoverSpy.mockRestore();

    // A crafted payload carrying both the legitimate intersectSegment and a smuggled key.
    const extraParams = { intersectSegment: CATEGORY_ROW_SEGMENT, idGoal: '1' };
    const urlParam = `Actions.getPageUrls:${encodeURIComponent(DECODED_CURRENT_PAGE_SEGMENT)}:${encodeURIComponent(JSON.stringify(extraParams))}`;

    rowActionInstance.doOpenPopover(urlParam);

    const passedExtraParams = showSpy.mock.calls[0][2];
    expect(passedExtraParams.intersectSegment).toBe(CATEGORY_ROW_SEGMENT);
    expect(passedExtraParams.idGoal).toBeUndefined();
  });
});
