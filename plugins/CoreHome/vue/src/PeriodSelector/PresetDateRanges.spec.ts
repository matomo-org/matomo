/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { format } from '../Periods';
import {
  matchPresetFromSelection,
  PRESET_DATE_RANGES,
  resolvePresetDateRange,
} from './PresetDateRanges';

describe('CoreHome/PeriodSelector/PresetDateRanges', () => {
  function assertResolvedPreset(
    presetId: Parameters<typeof resolvePresetDateRange>[0],
    today: string,
    expected: {
      period: string;
      date: string;
      startDate: string;
      endDate: string;
    },
  ) {
    const resolved = resolvePresetDateRange(presetId, new Date(today));

    expect(resolved.period).toBe(expected.period);
    expect(resolved.date).toBe(expected.date);
    expect(format(resolved.startDate)).toBe(expected.startDate);
    expect(format(resolved.endDate)).toBe(expected.endDate);
  }

  it('should resolve all supported presets', () => {
    const ids = PRESET_DATE_RANGES.map((p) => p.id);

    expect(ids).toEqual([
      'today',
      'yesterday',
      'last7days',
      'last30days',
      'last90days',
      'lastWeekMonSun',
      'lastMonth',
      'lastQuarter',
      'lastYear',
      'thisWeekMonToday',
      'thisMonth',
      'thisQuarter',
      'thisYear',
    ]);
  });

  it('should resolve day presets', () => {
    assertResolvedPreset('today', '2026-02-16', {
      period: 'day',
      date: 'today',
      startDate: '2026-02-16',
      endDate: '2026-02-16',
    });

    assertResolvedPreset('yesterday', '2026-02-16', {
      period: 'day',
      date: 'yesterday',
      startDate: '2026-02-15',
      endDate: '2026-02-15',
    });
  });

  it('should resolve last N day presets including today', () => {
    assertResolvedPreset('last7days', '2026-02-16', {
      period: 'range',
      date: '2026-02-10,2026-02-16',
      startDate: '2026-02-10',
      endDate: '2026-02-16',
    });

    assertResolvedPreset('last30days', '2026-02-16', {
      period: 'range',
      date: '2026-01-18,2026-02-16',
      startDate: '2026-01-18',
      endDate: '2026-02-16',
    });

    assertResolvedPreset('last90days', '2026-02-16', {
      period: 'range',
      date: '2025-11-19,2026-02-16',
      startDate: '2025-11-19',
      endDate: '2026-02-16',
    });
  });

  it('should resolve last week from monday to sunday', () => {
    assertResolvedPreset('lastWeekMonSun', '2026-02-15', {
      period: 'week',
      date: 'last week',
      startDate: '2026-02-02',
      endDate: '2026-02-08',
    });
  });

  it('should resolve this week from monday to today including monday-only case', () => {
    assertResolvedPreset('thisWeekMonToday', '2026-02-16', {
      period: 'range',
      date: '2026-02-16,2026-02-16',
      startDate: '2026-02-16',
      endDate: '2026-02-16',
    });

    assertResolvedPreset('thisWeekMonToday', '2026-02-18', {
      period: 'range',
      date: '2026-02-16,2026-02-18',
      startDate: '2026-02-16',
      endDate: '2026-02-18',
    });
  });

  it('should resolve month/year presets', () => {
    assertResolvedPreset('lastMonth', '2026-02-16', {
      period: 'month',
      date: 'lastmonth',
      startDate: '2026-01-01',
      endDate: '2026-01-31',
    });

    assertResolvedPreset('thisMonth', '2026-02-16', {
      period: 'month',
      date: 'today',
      startDate: '2026-02-01',
      endDate: '2026-02-16',
    });

    assertResolvedPreset('lastYear', '2026-02-16', {
      period: 'year',
      date: 'lastyear',
      startDate: '2025-01-01',
      endDate: '2025-12-31',
    });

    assertResolvedPreset('thisYear', '2026-02-16', {
      period: 'year',
      date: 'today',
      startDate: '2026-01-01',
      endDate: '2026-02-16',
    });
  });

  it('should resolve quarter presets as custom ranges', () => {
    assertResolvedPreset('lastQuarter', '2026-02-16', {
      period: 'range',
      date: '2025-10-01,2025-12-31',
      startDate: '2025-10-01',
      endDate: '2025-12-31',
    });

    assertResolvedPreset('thisQuarter', '2026-02-16', {
      period: 'range',
      date: '2026-01-01,2026-02-16',
      startDate: '2026-01-01',
      endDate: '2026-02-16',
    });

    assertResolvedPreset('lastQuarter', '2026-04-01', {
      period: 'range',
      date: '2026-01-01,2026-03-31',
      startDate: '2026-01-01',
      endDate: '2026-03-31',
    });
  });

  it('should match preset from current selection', () => {
    const today = new Date('2026-02-16');

    expect(matchPresetFromSelection('day', '2026-02-16', '2026-02-16', today)).toBe('today');
    expect(matchPresetFromSelection('week', '2026-02-02', '2026-02-08', new Date('2026-02-15'))).toBe('lastWeekMonSun');
    expect(matchPresetFromSelection('range', '2026-01-01', '2026-02-16', today)).toBe('thisQuarter');
    expect(matchPresetFromSelection('range', '2026-01-01', '2026-01-15', today)).toBeNull();
  });
});
