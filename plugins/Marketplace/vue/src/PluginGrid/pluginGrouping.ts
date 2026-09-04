/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { PluginCard } from '../types';

/**
 * Filtering, sorting and tab construction for the Marketplace overview.
 *
 * All of it runs on the client, deliberately. `Api\Client::getWarmedOverviewLists()` names the
 * only three queries the Marketplace holds for the longer 90 minute timeout, and any request that
 * varies `query`, `sort` or a category is a different cache key that misses all three. So the page
 * fetches the warmed lists once and does the rest here.
 *
 * Do not move any of this back behind a request parameter. Nothing would fail loudly: every tab
 * click would just become a cold catalogue download.
 */

export const TAB_ALL = 'all';
export const TAB_PREMIUM = 'premium';
export const TAB_BUNDLES = 'bundles';
export const TAB_THEMES = 'themes';

/**
 * The Marketplace ships this as a category value but it means "nobody has classified this yet",
 * which is true of most of the catalogue. It is reachable through All plugins and search, and
 * never becomes a tab of its own.
 */
export const CATEGORY_UNCATEGORISED = 'uncategorised';

/** Sort methods. The first four match `Marketplace\Input\Sort`; developer is client-side only. */
export const SORT_LAST_UPDATED = 'lastupdated';
export const SORT_POPULAR = 'popular';
export const SORT_NEWEST = 'newest';
export const SORT_ALPHA = 'alpha';
export const SORT_DEVELOPER = 'developer';

export const SORT_METHODS = [
  SORT_LAST_UPDATED,
  SORT_POPULAR,
  SORT_NEWEST,
  SORT_ALPHA,
  SORT_DEVELOPER,
] as const;

export type SortMethod = typeof SORT_METHODS[number];

/** The type tabs, in display order. Category tabs are appended to these by {@link buildTabs}. */
export const TYPE_TABS = [TAB_ALL, TAB_PREMIUM, TAB_BUNDLES, TAB_THEMES];

export interface PluginTab {
  id: string;
  /** How many plugins the tab holds. `all` counts the whole catalogue. */
  count: number;
  /** A category tab takes its label from a translation key; a type tab has a fixed one. */
  isCategory: boolean;
}

const MATOMO_OWNERS = ['piwik', 'matomo-org'];

/**
 * The Marketplace sends dates as `2015-11-20 19:16:03`. `Date.parse()` is only specified for the
 * ISO form, so parse it ourselves rather than relying on browsers being lenient.
 *
 * Returns `null` for anything unparseable, which callers sort last instead of scattering.
 */
export function parseMarketplaceDate(value: unknown): number|null {
  if (typeof value !== 'string') {
    return null;
  }

  const parts = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}):(\d{2}))?/.exec(value.trim());
  if (!parts) {
    return null;
  }

  const timestamp = Date.UTC(
    Number(parts[1]),
    Number(parts[2]) - 1,
    Number(parts[3]),
    Number(parts[4] ?? 0),
    Number(parts[5] ?? 0),
    Number(parts[6] ?? 0),
  );

  return Number.isNaN(timestamp) ? null : timestamp;
}

/** The name a card credits, so that sorting and display agree on who owns a Matomo plugin. */
export function ownerLabel(plugin: PluginCard): string {
  return MATOMO_OWNERS.includes(plugin.owner) ? 'Matomo' : (plugin.owner || '');
}

export function matchesQuery(plugin: PluginCard, query: string): boolean {
  const needle = (query || '').trim().toLowerCase();
  if (!needle) {
    return true;
  }

  return [plugin.displayName, plugin.name, plugin.description, plugin.owner]
    .some((field) => (field || '').toLowerCase().includes(needle));
}

export function matchesTab(plugin: PluginCard, tabId: string): boolean {
  switch (tabId) {
    case TAB_ALL:
      return true;
    case TAB_PREMIUM:
      return !!plugin.isPaid;
    case TAB_BUNDLES:
      return !!plugin.isBundle;
    case TAB_THEMES:
      return !!plugin.isTheme;
    default:
      return plugin.category === tabId;
  }
}

/**
 * Sorts a copy, never the argument.
 *
 * Every comparison falls back to the display name, so two plugins that tie - and every plugin
 * whose value is missing - keep a stable, predictable order rather than whatever the fetch
 * happened to return.
 */
export function sortPlugins(plugins: PluginCard[], sort: string): PluginCard[] {
  const byName = (a: PluginCard, b: PluginCard) => (a.displayName || '').localeCompare(
    b.displayName || '',
  );

  // a missing value sorts last in every descending comparison, rather than ahead of everything
  const descending = (a: number|null, b: number|null) => {
    if (a === b) {
      return 0;
    }
    if (a === null) {
      return 1;
    }
    if (b === null) {
      return -1;
    }
    return b - a;
  };

  const byDate = (field: 'lastUpdatedRaw'|'createdDateTime') => (
    a: PluginCard,
    b: PluginCard,
  ) => descending(parseMarketplaceDate(a[field]), parseMarketplaceDate(b[field])) || byName(a, b);

  const sorted = [...plugins];

  switch (sort) {
    case SORT_POPULAR:
      // the API nulls numDownloads for every paid plugin, so this is free plugins first by
      // definition - not a bug in the comparison
      return sorted.sort((a, b) => descending(
        typeof a.numDownloads === 'number' ? a.numDownloads : null,
        typeof b.numDownloads === 'number' ? b.numDownloads : null,
      ) || byName(a, b));
    case SORT_NEWEST:
      return sorted.sort(byDate('createdDateTime'));
    case SORT_ALPHA:
      return sorted.sort(byName);
    case SORT_DEVELOPER:
      return sorted.sort((a, b) => ownerLabel(a).localeCompare(ownerLabel(b)) || byName(a, b));
    case SORT_LAST_UPDATED:
    default:
      // lastUpdated is a localised display string by the time it reaches us; lastUpdatedRaw is the
      // value to order on. Sorting the display string looks plausible and is wrong.
      return sorted.sort(byDate('lastUpdatedRaw'));
  }
}

export function filterPlugins(
  plugins: PluginCard[],
  tabId: string,
  query: string,
): PluginCard[] {
  return plugins.filter((plugin) => matchesTab(plugin, tabId) && matchesQuery(plugin, query));
}

/**
 * The tab list, built from the data rather than declared.
 *
 * A tab with no members is left out entirely: the categories the Marketplace ships today hold as
 * few as five plugins, so one delisting is enough to empty one, and a tab that shows an empty grid
 * is worse than a tab that is not there.
 *
 * The category half of this list is interim. When the Marketplace adopts the user-facing taxonomy
 * the only thing that changes here is which values arrive in `plugin.category`.
 */
export function buildTabs(plugins: PluginCard[]): PluginTab[] {
  const tabs: PluginTab[] = [];

  TYPE_TABS.forEach((id) => {
    const count = id === TAB_ALL
      ? plugins.length
      : plugins.filter((plugin) => matchesTab(plugin, id)).length;

    if (count > 0) {
      tabs.push({ id, count, isCategory: false });
    }
  });

  const categoryCounts = new Map<string, number>();
  plugins.forEach((plugin) => {
    const { category } = plugin;
    if (!category || category === CATEGORY_UNCATEGORISED) {
      return;
    }
    categoryCounts.set(category, (categoryCounts.get(category) ?? 0) + 1);
  });

  [...categoryCounts.keys()].sort().forEach((id) => {
    tabs.push({ id, count: categoryCounts.get(id) as number, isCategory: true });
  });

  return tabs;
}

/**
 * Maps the legacy `pluginType` hash parameter onto a tab.
 *
 * `CorePluginsAdmin` still links in with `#?pluginType=themes` from `ThemesIntro.vue` and
 * `PluginsTable.vue`, so reading it has to keep working even though nothing writes it any more.
 */
export function tabFromLegacyPluginType(pluginType: string): string|null {
  switch (pluginType) {
    case 'themes':
      return TAB_THEMES;
    case 'premium':
      return TAB_PREMIUM;
    case 'plugins':
      return TAB_ALL;
    default:
      return null;
  }
}
