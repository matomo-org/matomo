/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  getPresetIdFromPeriodAndDate,
  resolvePresetDateRange,
} from './PresetDateRangeResolver';

describe('PresetDateRangeResolver', () => {
  it('resolves every preset id from its concrete period/date value', () => {
    const today = new Date('2026-02-16');

    const testCases = [
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
    ] as const;

    for (const presetId of testCases) {
      const resolvedPreset = resolvePresetDateRange(presetId, today);

      expect(
        getPresetIdFromPeriodAndDate(resolvedPreset.period, resolvedPreset.date, today),
      ).toBe(presetId);
    }
  });

  it('provides tokenized urlDate values for presets that support rolling URLs', () => {
    const today = new Date('2026-02-16');

    expect(resolvePresetDateRange('today', today).urlDate).toBe('today');
    expect(resolvePresetDateRange('last7days', today).urlDate).toBe('last7');
    expect(resolvePresetDateRange('lastMonth', today).urlDate).toBe('lastmonth');
    expect(resolvePresetDateRange('thisMonth', today).urlDate).toBe('today');
  });

  it('falls back to concrete urlDate values for presets without rolling URL support', () => {
    const today = new Date('2026-02-16');

    const lastQuarter = resolvePresetDateRange('lastQuarter', today);
    const thisQuarter = resolvePresetDateRange('thisQuarter', today);

    expect(lastQuarter.urlDate).toBe(lastQuarter.date);
    expect(thisQuarter.urlDate).toBe(thisQuarter.date);
  });

  it('resolves week presets from any date in the matching week', () => {
    const today = new Date('2026-06-02');

    expect(getPresetIdFromPeriodAndDate('week', '2026-05-26', today)).toBe('lastWeekMonSun');
  });

  it('resolves month and year presets from any date in the matching period', () => {
    const today = new Date('2026-02-16');

    expect(getPresetIdFromPeriodAndDate('month', '2026-01-02', today)).toBe('lastMonth');
    expect(getPresetIdFromPeriodAndDate('year', '2025-06-15', today)).toBe('lastYear');
  });

  it('resolves current-period presets from any date in the current period', () => {
    const today = new Date('2026-06-02');

    expect(getPresetIdFromPeriodAndDate('week', '2026-06-01', today)).toBe('thisWeekMonToday');
    expect(getPresetIdFromPeriodAndDate('month', '2026-06-01', today)).toBe('thisMonth');
    expect(getPresetIdFromPeriodAndDate('year', '2026-01-23', today)).toBe('thisYear');
  });

  it('resolves thisQuarter only for the exact start-of-quarter to today range', () => {
    const today = new Date('2026-02-16');

    expect(getPresetIdFromPeriodAndDate('range', '2026-01-01,2026-02-16', today))
      .toBe('thisQuarter');
    expect(getPresetIdFromPeriodAndDate('range', '2026-01-01,2026-03-31', today))
      .toBeNull();
    expect(getPresetIdFromPeriodAndDate('range', '2026-01-01,2026-02-15', today))
      .toBeNull();
  });

  it('falls back to tokenized period/date values for historic hash values', () => {
    const today = new Date('2026-02-16');

    expect(getPresetIdFromPeriodAndDate('day', 'today', today)).toBe('today');
    expect(getPresetIdFromPeriodAndDate('day', 'yesterday', today)).toBe('yesterday');
    expect(getPresetIdFromPeriodAndDate('range', 'last7', today)).toBe('last7days');
    expect(getPresetIdFromPeriodAndDate('range', 'last30', today)).toBe('last30days');
    expect(getPresetIdFromPeriodAndDate('range', 'last90', today)).toBe('last90days');
    expect(getPresetIdFromPeriodAndDate('week', 'lastweek', today)).toBe('lastWeekMonSun');
    expect(getPresetIdFromPeriodAndDate('month', 'lastmonth', today)).toBe('lastMonth');
    expect(getPresetIdFromPeriodAndDate('year', 'lastyear', today)).toBe('lastYear');
    expect(getPresetIdFromPeriodAndDate('week', 'today', today)).toBe('thisWeekMonToday');
    expect(getPresetIdFromPeriodAndDate('month', 'today', today)).toBe('thisMonth');
    expect(getPresetIdFromPeriodAndDate('year', 'today', today)).toBe('thisYear');
  });

  it('returns null for non-matching period/date values', () => {
    const today = new Date('2026-02-16');

    expect(getPresetIdFromPeriodAndDate('day', '2026-02-14', today)).toBeNull();
    expect(getPresetIdFromPeriodAndDate('range', '2026-02-01,2026-02-14', today)).toBeNull();
    expect(getPresetIdFromPeriodAndDate('week', '2026-02-03', today)).toBeNull();
  });
});
