/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import type { ReportActionsConfig } from './ReportActions.store';

export type PromotableActionId = 'periods' | 'export' | 'annotations';

/**
 * The actions a header may lift out of its menu, most deserving first, and rendered in that order
 * right to left from the trigger. Fixed: a report says what it offers, never where it goes.
 */
export const PROMOTABLE_ACTIONS: PromotableActionId[] = ['periods', 'export', 'annotations'];

// Which of them a header can draw. All three now, but a new rank may be added above before it has
// a control of its own.
export const PROMOTED_RENDERERS: PromotableActionId[] = ['periods', 'export', 'annotations'];

// Below this everything stays in the menu: a promoted control has no width to take from a title.
export const NO_PROMOTION_BREAKPOINT = '(max-width: 767px)';

type Offers = (config: Partial<ReportActionsConfig>) => boolean;

const OFFERS: Record<PromotableActionId, Offers> = {
  // An empty list would promote a control that opens onto nothing.
  periods: (config) => !!config.showPeriods && (config.selectablePeriods || []).length > 0,
  // One control covers both halves, so either alone earns it.
  export: (config) => !!(config.showExport || config.showExportAsImageIcon),
  annotations: (config) => !!config.showAnnotations,
};

/**
 * Which promotable actions this report offers, in priority order. Derived from what it published,
 * so a promoted control cannot disagree with the menu entry it replaces.
 */
export function promotableActions(
  config?: Partial<ReportActionsConfig> | null,
): PromotableActionId[] {
  // Nothing can leave a menu that does not render.
  if (!config || !config.showFooter || !config.showFooterIcons) {
    return [];
  }

  return PROMOTABLE_ACTIONS.filter((id) => OFFERS[id](config));
}
