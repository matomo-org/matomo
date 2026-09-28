/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import type { FooterIconGroup } from './DataTableActions.vue';
import type { ReportActionsConfig } from './ReportActions.store';
import type { PromotableActionId } from './reportActions';
import { isBooleanLikeSet } from './DataTableActions.utils';

type Config = Partial<ReportActionsConfig>;

function params(config: Config): Record<string, unknown> {
  return config.clientSideParameters || {};
}

function isTableView(config: Config): boolean {
  return config.viewDataTable === 'table'
    || config.viewDataTable === 'tableAllColumns'
    || config.viewDataTable === 'tableGoals';
}

// A setting the report is not offering may still be applied, which has to stay reachable to be
// undone. This is the gate the configure icon carried before the actions moved into the header.
function offersConfigList(config: Config): boolean {
  const p = params(config);

  return isTableView(config)
    || isBooleanLikeSet(p.flat)
    || isBooleanLikeSet(p.keep_totals_row)
    || isBooleanLikeSet(p.include_aggregate_rows)
    || isBooleanLikeSet(p.show_dimensions)
    || isBooleanLikeSet(p.pivotBy)
    || isBooleanLikeSet(p.enable_filter_excludelowpop)
    || isBooleanLikeSet(p.show_percentage_values);
}

// The dimensions and hierarchy entries need showFlattenTable too, so they add nothing here.
export function hasConfigItems(config: Config): boolean {
  return offersConfigList(config)
    && !!(config.showFlattenTable
      || (!config.isDataTableEmpty && config.showTotalsRow)
      || (!config.isDataTableEmpty && config.reportSupportsPercentageValues)
      || config.showExcludeLowPopulation
      || config.showPivotBySubtable);
}

export function hasActionItems(config: Config, promoted: PromotableActionId[]): boolean {
  const kept = (id: PromotableActionId) => promoted.indexOf(id) === -1;

  return !!((config.showPeriods && kept('periods'))
    || (config.showAnnotations && kept('annotations'))
    || (config.dataTableActions || []).length > 0);
}

export function hasExportItems(config: Config, promoted: PromotableActionId[]): boolean {
  return !!(config.showExport || config.showExportAsImageIcon)
    && promoted.indexOf('export') === -1;
}

// A button with no icon renders nothing, and a group of them would rule off an empty stretch.
export function visibleFooterIconGroups(config: Config): FooterIconGroup[] {
  return (config.footerIcons || [])
    .map((group) => ({ ...group, buttons: group.buttons.filter((icon) => !!icon.icon) }))
    .filter((group) => group.buttons.length > 0);
}

/**
 * Whether the 3-dots menu would hold anything once the promoted actions have left it. The header
 * asks before it draws the trigger: a menu opening onto nothing is worse than no menu at all.
 */
export function menuHoldsAnything(
  config: Config | null | undefined,
  promoted: PromotableActionId[],
): boolean {
  if (!config) {
    return false;
  }

  return hasConfigItems(config)
    || hasActionItems(config, promoted)
    || hasExportItems(config, promoted)
    || visibleFooterIconGroups(config).length > 0;
}
