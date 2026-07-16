/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { RANGE_PERIOD } from './PeriodSelector.types';

export interface ApplyEnabledState {
  uiSelectionType: 'period' | 'preset';
  uiSelectedPeriod: string;
  hasPendingNonRangePeriodChange: boolean;
  hasPendingPresetSelection: boolean;
  isRangeValid: boolean | null;
  isCompareDirty: boolean;
  isComparing: boolean | null;
  comparePeriodType: string;
  isCompareRangeValid: boolean;
}

export function isApplyButtonEnabled(state: ApplyEnabledState): boolean {
  if (state.hasPendingNonRangePeriodChange) {
    return false;
  }

  if (state.uiSelectionType === 'period'
      && state.uiSelectedPeriod !== RANGE_PERIOD
      && !state.isCompareDirty
  ) {
    return true;
  }

  if (state.uiSelectedPeriod === RANGE_PERIOD
      && !state.hasPendingPresetSelection
      && !state.isRangeValid
  ) {
    return false;
  }

  if (state.isComparing
      && state.comparePeriodType === 'custom'
      && !state.isCompareRangeValid
  ) {
    return false;
  }

  return true;
}

export type NonRangeApplyAction =
  | { type: 'stop' }
  | { type: 'close' }
  | { type: 'commit'; date: string; period: string };

export interface NonRangeApplyState {
  hasPendingNonRangePeriodChange: boolean;
  isCompareDirty: boolean;
  shouldCloseSelectorWithoutApplying: boolean;
  appliedPeriod: string;
  hasCommittedRangeBounds: boolean;
  rollingDateParam: string | null;
  appliedRangeStartDate: string | null;
  appliedRangeEndDate: string | null;
  formattedAppliedAnchorDate: string | null;
}

export function getApplyButtonAction(state: NonRangeApplyState): NonRangeApplyAction {
  if (state.hasPendingNonRangePeriodChange) {
    return { type: 'stop' };
  }

  if (!state.isCompareDirty) {
    return state.shouldCloseSelectorWithoutApplying
      ? { type: 'close' }
      : { type: 'stop' };
  }

  if (state.appliedPeriod === RANGE_PERIOD) {
    if (!state.hasCommittedRangeBounds) {
      return { type: 'stop' };
    }

    const rangeDateValue = `${state.appliedRangeStartDate},${state.appliedRangeEndDate}`;

    return {
      type: 'commit',
      date: state.rollingDateParam || rangeDateValue,
      period: RANGE_PERIOD,
    };
  }

  if (!state.formattedAppliedAnchorDate) {
    return { type: 'stop' };
  }

  return {
    type: 'commit',
    date: state.rollingDateParam || state.formattedAppliedAnchorDate,
    period: state.appliedPeriod,
  };
}
