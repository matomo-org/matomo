/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { isBooleanLikeSet, resolveExportSupportsFlat } from './DataTableActions.utils';

describe('CoreHome/DataTableActions.utils', () => {
  describe('isBooleanLikeSet', () => {
    it.each([
      [true, true],
      [1, true],
      ['1', true],
      [false, false],
      [0, false],
      ['0', false],
    ])('returns %s => %s', (value: number|string|boolean, expected: boolean) => {
      expect(isBooleanLikeSet(value)).toBe(expected);
    });
  });

  describe('resolveExportSupportsFlat', () => {
    it('keeps flat export available when the report supports flattening', () => {
      expect(resolveExportSupportsFlat(true, 0)).toBe(true);
    });

    it('keeps flat export available when the table is already flat', () => {
      expect(resolveExportSupportsFlat(false, 1)).toBe(true);
    });

    it('disables flat export when neither report nor table state supports it', () => {
      expect(resolveExportSupportsFlat(false, 0)).toBe(false);
    });
  });
});
