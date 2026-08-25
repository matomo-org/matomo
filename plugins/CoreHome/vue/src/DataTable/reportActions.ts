/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import type { ReportActionsConfig } from './ReportActions.store';

export type PromotableActionId = 'periods' | 'export' | 'annotations';

/**
 * The actions a header may lift out of its menu, most deserving first.
 *
 * Fixed, rather than carried per report: a report says what it offers, never where it goes. This
 * is also the rendered order, right to left from the menu's trigger, so the first entry is the one
 * that stays out when there is room for a single control.
 *
 * Actions that will never leave the menu are absent on purpose - the configuration items and the
 * visualisation list are only legible as a list.
 */
export const PROMOTABLE_ACTIONS: PromotableActionId[] = ['periods', 'export', 'annotations'];

type Offers = (config: Partial<ReportActionsConfig>) => boolean;

const OFFERS: Record<PromotableActionId, Offers> = {
  // An empty list would promote a control that opens onto nothing.
  periods: (config) => !!config.showPeriods && (config.selectablePeriods || []).length > 0,
  // One control covers both, so either one alone still earns it.
  export: (config) => !!(config.showExport || config.showExportAsImageIcon),
  annotations: (config) => !!config.showAnnotations,
};

/**
 * Which promotable actions this report offers, in priority order.
 *
 * Availability is derived from what the report published rather than declared beside the action,
 * so there is one source for whether an entry exists and the promoted control cannot disagree
 * with the menu entry it came from.
 */
export function promotableActions(
  config?: Partial<ReportActionsConfig> | null,
): PromotableActionId[] {
  // The menu itself renders behind this pair, so nothing can leave a menu that is not there.
  if (!config || !config.showFooter || !config.showFooterIcons) {
    return [];
  }

  return PROMOTABLE_ACTIONS.filter((id) => OFFERS[id](config));
}
