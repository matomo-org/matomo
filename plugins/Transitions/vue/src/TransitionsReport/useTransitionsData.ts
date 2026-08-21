/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { onBeforeUnmount, ref } from 'vue';
import { translate, Matomo, NumberFormatter } from 'CoreHome';
import {
  TransitionsError,
  TransitionsGroupData,
  TransitionsMetricData,
  TransitionsReportData,
  TransitionsRowData,
  TransitionsSide,
} from './types';

/** Value placeholder marker, so a metric label can be split around its value without markup. */
const VALUE_MARKER = '\u0001';

interface GroupDefinition {
  name: string;
  side: TransitionsSide;
  titleKey: string;
  /** Inline label carrying a %s placeholder for the formatted metric value. */
  inlineKey: string;
  /** How the group's own count is phrased; everything but downloads and outlinks is pageviews. */
  countKey: string;
  /** Morpheus icon class for the group's rows. */
  icon: string;
  /** Direct entries and exits are single totals with no detail rows behind them. */
  canExpand: boolean;
}

/**
 * The groups of each side, in render order. Mirrors Piwik_Transitions' leftGroups/rightGroups plus
 * the two terminal metrics (direct entries, exits) that have no detail rows.
 */
export const TRANSITIONS_GROUPS: GroupDefinition[] = [
  {
    name: 'previousPages',
    side: 'incoming',
    titleKey: 'Transitions_FromPreviousPages',
    inlineKey: 'Transitions_FromPreviousPagesInline',
    countKey: 'Transitions_NumPageviews',
    icon: 'icon-document',
    canExpand: true,
  },
  {
    name: 'previousSiteSearches',
    side: 'incoming',
    titleKey: 'Transitions_FromPreviousSiteSearches',
    inlineKey: 'Transitions_FromPreviousSiteSearchesInline',
    countKey: 'Transitions_NumPageviews',
    icon: 'icon-search',
    canExpand: true,
  },
  {
    name: 'searchEngines',
    side: 'incoming',
    titleKey: 'Transitions_FromSearchEngines',
    inlineKey: 'Referrers_TypeSearchEngines',
    countKey: 'Transitions_NumPageviews',
    icon: 'icon-search',
    canExpand: true,
  },
  {
    name: 'socialNetworks',
    side: 'incoming',
    titleKey: 'Transitions_FromSocialNetworks',
    inlineKey: 'Referrers_TypeSocialNetworks',
    countKey: 'Transitions_NumPageviews',
    icon: 'icon-users',
    canExpand: true,
  },
  {
    name: 'aiAssistants',
    side: 'incoming',
    titleKey: 'Transitions_FromAIAssistants',
    inlineKey: 'Referrers_TypeAIAssistants',
    countKey: 'Transitions_NumPageviews',
    icon: 'icon-ai-assistants',
    canExpand: true,
  },
  {
    name: 'websites',
    side: 'incoming',
    titleKey: 'Transitions_FromWebsites',
    inlineKey: 'Referrers_TypeWebsites',
    countKey: 'Transitions_NumPageviews',
    icon: 'icon-outlink',
    canExpand: true,
  },
  {
    name: 'campaigns',
    side: 'incoming',
    titleKey: 'Transitions_FromCampaigns',
    inlineKey: 'Referrers_TypeCampaigns',
    countKey: 'Transitions_NumPageviews',
    icon: 'icon-reporting-referer',
    canExpand: true,
  },
  {
    name: 'directEntries',
    side: 'incoming',
    titleKey: 'Transitions_DirectEntries',
    inlineKey: 'Referrers_TypeDirectEntries',
    countKey: 'Transitions_NumPageviews',
    icon: 'icon-sign-in',
    canExpand: false,
  },
  {
    name: 'followingPages',
    side: 'outgoing',
    titleKey: 'Transitions_ToFollowingPages',
    inlineKey: 'Transitions_ToFollowingPagesInline',
    countKey: 'Transitions_NumPageviews',
    icon: 'icon-document',
    canExpand: true,
  },
  {
    name: 'followingSiteSearches',
    side: 'outgoing',
    titleKey: 'Transitions_ToFollowingSiteSearches',
    inlineKey: 'Transitions_ToFollowingSiteSearchesInline',
    countKey: 'Transitions_NumPageviews',
    icon: 'icon-search',
    canExpand: true,
  },
  {
    name: 'downloads',
    side: 'outgoing',
    titleKey: 'General_Downloads',
    inlineKey: 'Transitions_NumDownloads',
    countKey: 'Transitions_NumDownloads',
    icon: 'icon-download',
    canExpand: true,
  },
  {
    name: 'outlinks',
    side: 'outgoing',
    titleKey: 'General_Outlinks',
    inlineKey: 'Transitions_NumOutlinks',
    countKey: 'Transitions_NumOutlinks',
    icon: 'icon-outlink',
    canExpand: true,
  },
  {
    name: 'exits',
    side: 'outgoing',
    titleKey: 'General_ColumnExits',
    inlineKey: 'Transitions_ExitsInline',
    countKey: 'Transitions_NumPageviews',
    icon: 'icon-sign-out',
    canExpand: false,
  },
];

/** The API method behind the report, i.e. the one whose failure is the report's own failure. */
const REPORT_API_METHOD = 'Transitions.getTransitionsForAction';

/** Exception names Transitions.getTransitionsForAction throws, mapped to their translation keys. */
const ERROR_TRANSLATIONS: Record<string, { title: string; details: string }> = {
  NoDataForAction: {
    title: 'Transitions_NoDataForAction',
    details: 'Transitions_NoDataForActionDetails',
  },
  PeriodNotAllowed: {
    title: 'Transitions_PeriodNotAllowed',
    details: 'Transitions_PeriodNotAllowedDetails',
  },
};

/**
 * Removes protocol, www and trailing slashes from a URL; with `removeDomain` the domain goes too.
 * Ported verbatim from Piwik_Transitions_Util.shortenUrl.
 */
export function shortenUrl(url: string, removeDomain = false): string {
  if (url === 'Others') {
    return url;
  }

  const urlBackup = url;
  let shortened = url.replace(/http(s)?:\/\/(www\.)?/, '');

  if (urlBackup === shortened) {
    return shortened;
  }

  if (removeDomain) {
    shortened = shortened.replace(/[^/]*/, '');
    if (shortened === '/') {
      shortened = urlBackup;
    }
  }

  return shortened.replace(/\/$/, '');
}

/** The model exposes group totals as `<group>NbTransitions`, but direct entries/exits as-is. */
function metricName(group: GroupDefinition): string {
  return group.canExpand ? `${group.name}NbTransitions` : group.name;
}

function resolveError(errorName: string, actionName: string): TransitionsError {
  // In development mode the API appends a stack trace to the exception message, so match on the
  // leading exception name rather than on the whole message.
  const [name] = errorName.split(/\s/, 1);
  const keys = ERROR_TRANSLATIONS[errorName] ?? ERROR_TRANSLATIONS[name];

  if (!keys) {
    // An exception we have no translation for; show it as-is, like the legacy renderer did.
    return { title: errorName, message: '', backLabel: '<<<' };
  }

  const subject = `<span>${Matomo.helper.addBreakpointsToUrl(actionName)}</span>`;

  return {
    title: translate(keys.title, subject),
    message: translate(keys.details),
    backLabel: translate('Transitions_ErrorBack'),
  };
}

/**
 * The share-of-all-pageviews tooltip. Empty until the site total is known, since the share is the
 * whole point of it.
 */
function buildPageviewsTooltip(model: TransitionsModel, totalNbPageviews: number|false): string {
  if (!totalNbPageviews) {
    return '';
  }

  const shareOfAll = NumberFormatter.formatPercent(
    Math.round((model.pageviews / totalNbPageviews) * 1000) / 10,
  );

  return `${translate(
    'Transitions_ShareOfAllPageviews',
    NumberFormatter.formatNumber(model.pageviews),
    shareOfAll,
  )}\n${translate('General_DateRange')} ${model.date}`;
}

/** Splits an inline label around its value, so the value can be emphasised without markup. */
export function splitInlineLabel(inlineKey: string): { before: string; after: string } {
  const parts = translate(inlineKey, VALUE_MARKER).split(VALUE_MARKER);
  return { before: parts[0] ?? '', after: parts[1] ?? '' };
}

/**
 * Loads and shapes the data for one action.
 *
 * The request and the parsing stay in the legacy Piwik_Transitions_Model/Ajax pair so the wire
 * contract is unchanged; this composable only drives them, turns the result into render-ready
 * view data, and owns the loading/error state.
 *
 * Requests are last-request-wins: a response is dropped when a newer load has started or the
 * component has since unmounted, so rapid report or type switching cannot paint stale data.
 */
export function useTransitionsData() {
  const isLoading = ref(false);
  const error = ref<TransitionsError|null>(null);
  const report = ref<TransitionsReportData|null>(null);

  let requestId = 0;
  let disposed = false;

  onBeforeUnmount(() => {
    disposed = true;
  });

  const isCurrent = (id: number) => !disposed && id === requestId;

  /** The detail rows of a group, shown while it is the open group on its side. */
  function buildRows(
    model: TransitionsModel,
    group: GroupDefinition,
    actionType: string,
    groupShare: number,
  ): TransitionsRowData[] {
    if (!group.canExpand) {
      return [];
    }

    const details = model.getDetailsForGroup(group.name) || [];

    return details.map((detail, index) => {
      const rawLabel = (typeof detail.url !== 'undefined' ? detail.url : detail.label) ?? '';
      const isOthers = rawLabel === 'Others';

      const isInternalPage = group.name === 'previousPages' || group.name === 'followingPages';
      const isDownload = group.name === 'downloads';
      const isOutlink = group.name === 'outlinks' || group.name === 'websites';

      // How much of the URL the label keeps, and whether the row links out, are two separate
      // questions: a download keeps only its path but still opens in a new tab.
      const shortenWithDomain = (actionType === 'url' && isInternalPage) || isDownload;
      const shortenKeepingDomain = isOutlink;
      const linksOut = isOutlink || isDownload;

      let label = rawLabel;
      if (shortenWithDomain) {
        label = shortenUrl(rawLabel, true);
      } else if (shortenKeepingDomain) {
        label = shortenUrl(rawLabel);
      }

      return {
        key: `${group.name}-${index}`,
        kind: 'action' as const,
        icon: group.icon,
        label,
        fullLabel: label === rawLabel || isOthers ? '' : rawLabel,
        countLabel: translate(
          group.countKey,
          NumberFormatter.formatNumber(detail.referrals),
        ),
        percentage: NumberFormatter.formatPercent(detail.percentage),
        share: (detail.percentage / 100) * groupShare,
        externalUrl: !isOthers && linksOut ? rawLabel : undefined,
        transitionUrl: !isOthers && isInternalPage ? rawLabel : undefined,
        isOthers,
      };
    });
  }

  /** The single row that stands for a whole group while some other group is the open one. */
  function buildSummaryRow(
    group: GroupDefinition,
    title: string,
    value: number,
    share: number,
  ): TransitionsRowData {
    return {
      key: group.name,
      kind: 'summary',
      icon: group.icon,
      label: title,
      fullLabel: '',
      countLabel: translate(group.countKey, NumberFormatter.formatNumber(value)),
      percentage: NumberFormatter.formatPercent(Math.round(share * 1000) / 10),
      share,
      opensGroup: group.canExpand ? group.name : undefined,
      isOthers: false,
    };
  }

  function buildReport(
    model: TransitionsModel,
    actionType: string,
    actionName: string,
  ): TransitionsReportData {
    const groups: TransitionsGroupData[] = [];
    const metrics: TransitionsMetricData[] = [];

    let incomingTotal = 0;
    let outgoingTotal = 0;

    TRANSITIONS_GROUPS.forEach((group) => {
      const value = (model[metricName(group)] as number) || 0;
      const share = model.getPercentage(metricName(group)) as number;
      const title = model.getGroupTitle(group.name);

      if (group.side === 'incoming') {
        incomingTotal += value;
      } else {
        outgoingTotal += value;
      }

      groups.push({
        name: group.name,
        side: group.side,
        title,
        nbTransitions: value,
        share,
        canExpand: group.canExpand,
        countLabel: translate(group.countKey, NumberFormatter.formatNumber(value)),
        rows: buildRows(model, group, actionType, share),
        summaryRow: buildSummaryRow(group, title, value, share),
      });

      const { before, after } = splitInlineLabel(group.inlineKey);
      metrics.push({
        key: group.name,
        groupName: group.name,
        side: group.side,
        labelBefore: before,
        labelAfter: after,
        value,
        valueLabel: NumberFormatter.formatNumber(value),
        tooltip: translate(
          'Transitions_XOfAllPageviews',
          model.getPercentage(metricName(group), true) as string,
        ),
        canExpand: group.canExpand,
      });
    });

    return {
      actionType,
      actionName,
      title: actionType === 'url' ? shortenUrl(actionName, true) : actionName,
      titleUrl: actionType === 'url' ? actionName : undefined,
      date: model.date,
      pageviews: model.pageviews,
      loops: model.loops,
      loopsLabel: translate(
        'Transitions_LoopsInline',
        NumberFormatter.formatNumber(model.loops),
      ),
      loopsTooltip: translate(
        'Transitions_XOfAllPageviews',
        model.getPercentage('loops', true) as string,
      ),
      pageviewsLabel: translate(
        'Transitions_NumPageviews',
        NumberFormatter.formatNumber(model.pageviews),
      ),
      pageviewsTooltip: buildPageviewsTooltip(model, model.getTotalNbPageviews()),
      incomingTotal,
      outgoingTotal,
      groups,
      metrics,
    };
  }

  /** Seeds the group titles the API response does not carry, so getGroupTitle() can resolve them. */
  function seedGroupTitles(model: TransitionsModel) {
    TRANSITIONS_GROUPS.forEach((group) => {
      model.groupTitles[group.name] = translate(group.titleKey);
    });
  }

  function load(actionType: string, actionName: string, overrideParams: Record<string, string>) {
    requestId += 1;
    const id = requestId;

    isLoading.value = true;
    error.value = null;

    const ajax = new window.Piwik_Transitions_Ajax();
    ajax.setErrorCallback((errorName, params) => {
      // The model drives the site's total pageviews through this same instance, so the callback
      // also sees failures of that request. Only a failure of the report itself should replace
      // the report with an error; a missing total just leaves its tooltip empty.
      if (params.method !== REPORT_API_METHOD || !isCurrent(id)) {
        return;
      }

      isLoading.value = false;
      report.value = null;
      error.value = resolveError(errorName, actionName);
    });

    const model = new window.Piwik_Transitions_Model(ajax);
    seedGroupTitles(model);

    model.loadData(actionType, actionName, overrideParams, () => {
      if (!isCurrent(id)) {
        return;
      }

      isLoading.value = false;
      error.value = null;
      report.value = buildReport(model, actionType, actionName);

      // Fired once per page load in parallel with the first report, so on that report the total
      // is still in flight above. Fill the tooltip in when it lands, the way the legacy renderer's
      // tooltip callback did by being evaluated at hover time.
      model.whenTotalNbPageviewsLoaded((totalNbPageviews) => {
        if (!isCurrent(id) || !report.value) {
          return;
        }

        report.value = {
          ...report.value,
          pageviewsTooltip: buildPageviewsTooltip(model, totalNbPageviews),
        };
      });

      Matomo.postEvent('Transitions.dataChanged', { actionType, actionName });
    });
  }

  return {
    isLoading,
    error,
    report,
    load,
  };
}
