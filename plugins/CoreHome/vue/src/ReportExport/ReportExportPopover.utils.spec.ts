/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  isFormatWithoutExpanded,
  resolveEffectiveSubtableOptions,
  resolveInitialSubtablePreference,
  SubtablePreference,
} from './ReportExportPopover.utils';

describe('CoreHome/ReportExportPopover.utils', () => {
  describe('isFormatWithoutExpanded', () => {
    it.each([
      ['CSV', true],
      ['TSV', true],
      ['JSON', false],
    ])('returns %s => %s', (format: string, expected: boolean) => {
      expect(isFormatWithoutExpanded(format)).toBe(expected);
    });
  });

  describe('resolveInitialSubtablePreference', () => {
    const cases: Array<[boolean, boolean, string, SubtablePreference]> = [
      [true, false, 'TSV', { hasUserPreference: false, preferredMode: null }],
      [true, false, 'JSON', { hasUserPreference: true, preferredMode: 'flat' }],
      [false, true, 'JSON', { hasUserPreference: true, preferredMode: 'expanded' }],
      [false, false, 'JSON', { hasUserPreference: true, preferredMode: null }],
    ];

    it.each(cases)(
      'returns expected preference for initialOptionFlat=%s initialOptionExpanded=%s format=%s',
      (
        initialOptionFlat: boolean,
        initialOptionExpanded: boolean,
        initialReportFormat: string,
        expected: SubtablePreference,
      ) => {
        expect(resolveInitialSubtablePreference(
          initialOptionFlat,
          initialOptionExpanded,
          initialReportFormat,
        )).toEqual(expected);
      },
    );
  });

  describe('resolveEffectiveSubtableOptions', () => {
    const cases: Array<
      [
        boolean,
        boolean,
        string,
        SubtablePreference,
        { optionFlat: boolean; optionExpanded: boolean },
      ]
    > = [
      [false, true, 'JSON', { hasUserPreference: false, preferredMode: null }, { optionFlat: false, optionExpanded: false }],
      [true, true, 'CSV', { hasUserPreference: false, preferredMode: null }, { optionFlat: true, optionExpanded: false }],
      [true, true, 'CSV', { hasUserPreference: true, preferredMode: null }, { optionFlat: false, optionExpanded: false }],
      [true, true, 'JSON', { hasUserPreference: false, preferredMode: null }, { optionFlat: false, optionExpanded: true }],
      [true, true, 'JSON', { hasUserPreference: true, preferredMode: 'flat' }, { optionFlat: true, optionExpanded: false }],
      [true, false, 'JSON', { hasUserPreference: true, preferredMode: 'flat' }, { optionFlat: false, optionExpanded: true }],
      [true, true, 'JSON', { hasUserPreference: true, preferredMode: 'expanded' }, { optionFlat: false, optionExpanded: true }],
      [true, true, 'JSON', { hasUserPreference: true, preferredMode: null }, { optionFlat: false, optionExpanded: false }],
    ];

    it.each(cases)(
      'returns expected effective options for hasSubtables=%s canExportFlat=%s format=%s preference=%j',
      (
        hasSubtables: boolean,
        canExportFlat: boolean,
        reportFormat: string,
        subtablePreference: SubtablePreference,
        expected: { optionFlat: boolean; optionExpanded: boolean },
      ) => {
        expect(resolveEffectiveSubtableOptions(
          hasSubtables,
          canExportFlat,
          reportFormat,
          subtablePreference,
        )).toEqual(expected);
      },
    );
  });
});
