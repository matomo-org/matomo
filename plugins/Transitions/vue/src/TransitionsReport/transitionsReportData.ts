/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Turns a loaded Piwik_Transitions_Model into the render-ready shape the report's components
 * consume, and resolves the API's exception names to displayable text.
 *
 * Pure: nothing here touches the request, the loading state or any ref, which is why it sits
 * beside useTransitionsData rather than inside it -- the shaping can then be exercised directly
 * instead of only through a mounted component.
 */

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
 * The groups of each side, in render order, plus the two terminal metrics (direct entries, exits)
 * that have no detail rows.
 */
const TRANSITIONS_GROUPS: GroupDefinition[] = [
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
 */
function shortenUrl(url: string, removeDomain = false): string {
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

export function resolveError(errorName: string, actionName: string): TransitionsError {
  // In development mode the API appends a stack trace to the exception message, so match on the
  // leading exception name rather than on the whole message.
  const [name] = errorName.split(/\s/, 1);
  const keys = ERROR_TRANSLATIONS[errorName] ?? ERROR_TRANSLATIONS[name];

  if (!keys) {
    // An exception we have no translation for, so the name is all there is to show. The back link
    // still has to read as a link: every translated error uses Transitions_ErrorBack for it.
    return { title: errorName, message: '', backLabel: translate('Transitions_ErrorBack') };
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
export function buildPageviewsTooltip(
  model: TransitionsModel,
  totalNbPageviews: number|false,
): string {
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
function splitInlineLabel(inlineKey: string): { before: string; after: string } {
  const parts = translate(inlineKey, VALUE_MARKER).split(VALUE_MARKER);
  return { before: parts[0] ?? '', after: parts[1] ?? '' };
}

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
  shareLabel: string,
): TransitionsRowData {
  return {
    key: group.name,
    kind: 'summary',
    icon: group.icon,
    label: title,
    fullLabel: '',
    countLabel: translate(group.countKey, NumberFormatter.formatNumber(value)),
    percentage: shareLabel,
    share,
    opensGroup: group.canExpand ? group.name : undefined,
    isOthers: false,
  };
}

export function buildReport(
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
    // Formatted once, so the summary row's pill and the card's tooltip cannot round the same
    // share to two different numbers. The model rounds to whole percent above 10% and to one
    // decimal below it, which is also what the API's own detail-row percentages use.
    const shareLabel = model.getPercentage(metricName(group), true) as string;
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
      canExpand: group.canExpand,
      countLabel: translate(group.countKey, NumberFormatter.formatNumber(value)),
      rows: buildRows(model, group, actionType, share),
      summaryRow: buildSummaryRow(group, title, value, share, shareLabel),
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
      // A metric at zero has no share to explain, so it gets no tooltip -- the legacy renderer
      // left one off there too.
      tooltip: value > 0 ? translate('Transitions_XOfAllPageviews', shareLabel) : '',
      canExpand: group.canExpand,
    });
  });

  return {
    actionName,
    title: actionType === 'url' ? shortenUrl(actionName, true) : actionName,
    titleUrl: actionType === 'url' ? actionName : undefined,
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

/**
 * Seeds the group titles the API response does not carry, so getGroupTitle() can resolve them.
 */
export function seedGroupTitles(model: TransitionsModel) {
  TRANSITIONS_GROUPS.forEach((group) => {
    model.groupTitles[group.name] = translate(group.titleKey);
  });
}
