/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { shiftDateByPeriod } from './PeriodSelector.helpers';
import { isSingleCalendarPeriod } from './PeriodSelector.types';

describe('CoreHome/PeriodSelector.helpers', () => {
  it('moves a quarter forward and backward by three months', () => {
    expect(shiftDateByPeriod(new Date(2026, 7, 15), 'quarter', 1)).toEqual(new Date(2026, 10, 1));
    expect(shiftDateByPeriod(new Date(2026, 7, 15), 'quarter', -1)).toEqual(new Date(2026, 4, 1));
  });

  it('does not overflow into the following quarter at the end of a month', () => {
    // Dec 31st minus three months would be Sep 31st, which overflows to Oct 1st (Q4)
    expect(shiftDateByPeriod(new Date(2026, 11, 31), 'quarter', -1)).toEqual(new Date(2026, 8, 1));
  });

  it('treats quarter as a single calendar period', () => {
    expect(isSingleCalendarPeriod('quarter')).toBe(true);
    expect(isSingleCalendarPeriod('range')).toBe(false);
  });
});
