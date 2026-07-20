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
