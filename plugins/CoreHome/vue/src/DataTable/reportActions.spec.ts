/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { PROMOTABLE_ACTIONS, promotableActions } from './reportActions';
import type { ReportActionsConfig } from './ReportActions.store';

function config(overrides: Partial<ReportActionsConfig> = {}): Partial<ReportActionsConfig> {
  return {
    showFooter: true,
    showFooterIcons: true,
    showPeriods: true,
    selectablePeriods: ['day', 'week'],
    showExport: true,
    showExportAsImageIcon: true,
    showAnnotations: true,
    ...overrides,
  };
}

describe('CoreHome/reportActions', () => {
  describe('promotableActions', () => {
    it('lists what the report offers, in priority order', () => {
      expect(promotableActions(config())).toEqual(['periods', 'export', 'annotations']);
    });

    it('keeps that order whatever the config lists first', () => {
      expect(promotableActions(config())).toEqual(PROMOTABLE_ACTIONS);
    });

    it('drops an action the report does not offer, keeping the rest in order', () => {
      expect(promotableActions(config({ showExport: false, showExportAsImageIcon: false })))
        .toEqual(['periods', 'annotations']);
    });

    it('offers export when only the image half is available', () => {
      expect(promotableActions(config({ showExport: false }))).toContain('export');
    });

    it('offers export when only the data half is available', () => {
      expect(promotableActions(config({ showExportAsImageIcon: false }))).toContain('export');
    });

    it('does not promote a period control that would open onto nothing', () => {
      expect(promotableActions(config({ selectablePeriods: [] }))).not.toContain('periods');
    });

    it.each([
      ['showFooter', { showFooter: false }],
      ['showFooterIcons', { showFooterIcons: false }],
    ])('promotes nothing when %s is off, because the menu itself does not render', (
      _name: string,
      off: Partial<ReportActionsConfig>,
    ) => {
      expect(promotableActions(config(off))).toEqual([]);
    });

    it.each([
      ['no config', undefined],
      ['a null config', null],
      ['an empty config', {}],
    ])('promotes nothing given %s', (
      _name: string,
      value: Partial<ReportActionsConfig>|null|undefined,
    ) => {
      expect(promotableActions(value)).toEqual([]);
    });
  });
});
