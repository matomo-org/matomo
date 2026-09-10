/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { PluginCard } from '../types';

/**
 * Filtering, sorting and tab construction for the overview, on the client by design.
 *
 * Only the three queries in `Api\Client::getWarmedOverviewLists()` get the 90 minute cache, and
 * varying query, sort or category misses all three. Moving any of this behind a request parameter
 * turns every tab click into a cold catalogue download, silently.
 */

export const TAB_ALL = 'all';
export const TAB_BUNDLES = 'bundles';
export const TAB_THEMES = 'themes';

/**
 * Everything no category claims. Invented by {@link buildTabs}, not sent by the Marketplace, so
 * that unclassified plugins - most of the catalogue - get a tab of their own.
 */
export const TAB_OTHER = 'other';

/**
 * The legacy singular `category` field's word for unclassified. Dropped if it ever appears in
 * `categories`, or the bar grows an "Uncategorised" tab beside "Other" for the same idea.
 */
export const CATEGORY_UNCATEGORISED = 'uncategorised';

/** Sort methods. The first four match `Marketplace\Input\Sort`; developer is client-side only. */
export const SORT_LAST_UPDATED = 'lastupdated';
export const SORT_POPULAR = 'popular';
export const SORT_NEWEST = 'newest';
export const SORT_ALPHA = 'alpha';
export const SORT_DEVELOPER = 'developer';

/** The type tabs, in display order. Category tabs are appended to these by {@link buildTabs}. */
export const TYPE_TABS = [TAB_ALL, TAB_BUNDLES, TAB_THEMES];

export interface PluginTab {
  id: string;
  /** How many plugins the tab holds; counts overlap, since a plugin can have two categories. */
  count: number;
  /** A category tab takes its label from a translation key; a type tab has a fixed one. */
  isCategory: boolean;
}

/** One row of the overview's section stack: a heading, a row of cards and a "See all". */
export interface PluginSection {
  /** The tab the section links to, and what "See all" writes to the `category` hash parameter. */
  id: string;
  isCategory: boolean;
  /** Every plugin in the section, not only the ones a single row has room for. */
  plugins: PluginCard[];
}

const MATOMO_OWNERS = ['piwik', 'matomo-org'];

/**
 * The Marketplace sends `2015-11-20 19:16:03`; `Date.parse()` only specifies the ISO form, so
 * parse it here. Unparseable values return null, which callers sort last.
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

/**
 * The category slugs a plugin is filed under. An array by design, so a plugin can appear in more
 * than one section; anything malformed reads as unclassified rather than throwing.
 */
export function pluginCategories(plugin: PluginCard): string[] {
  const { categories } = plugin;

  if (!Array.isArray(categories)) {
    return [];
  }

  return categories.filter((slug): slug is string => (
    typeof slug === 'string' && !!slug && slug !== CATEGORY_UNCATEGORISED
  ));
}

/** Whether no category claims this plugin. */
export function isUnclassified(plugin: PluginCard): boolean {
  return pluginCategories(plugin).length === 0;
}

export function matchesTab(plugin: PluginCard, tabId: string): boolean {
  switch (tabId) {
    case TAB_ALL:
      return true;
    case TAB_BUNDLES:
      return !!plugin.isBundle;
    case TAB_THEMES:
      return !!plugin.isTheme;
    case TAB_OTHER:
      return !plugin.isTheme
        && !plugin.isBundle
        && (isUnclassified(plugin) || pluginCategories(plugin).includes(TAB_OTHER));
    default:
      return pluginCategories(plugin).includes(tabId);
  }
}

/**
 * Sorts a copy, never the argument. Every comparison falls back to the display name, so ties and
 * missing values keep a predictable order rather than the fetch's.
 */
export function sortPlugins(plugins: PluginCard[], sort: string): PluginCard[] {
  const byName = (a: PluginCard, b: PluginCard) => (a.displayName || '').localeCompare(
    b.displayName || '',
  );

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
 * The tab list, built from the data rather than declared, so the category half follows whatever
 * slugs arrive in `plugin.categories`. An empty tab is left out: some categories hold as few as
 * five plugins, so one delisting can empty one.
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
    pluginCategories(plugin).forEach((slug) => {
      if (slug === TAB_OTHER) {
        return;
      }
      categoryCounts.set(slug, (categoryCounts.get(slug) ?? 0) + 1);
    });
  });

  [...categoryCounts.keys()].sort().forEach((id) => {
    tabs.push({ id, count: categoryCounts.get(id) as number, isCategory: true });
  });

  const otherCount = plugins.filter((plugin) => matchesTab(plugin, TAB_OTHER)).length;
  if (otherCount > 0) {
    tabs.push({ id: TAB_OTHER, count: otherCount, isCategory: true });
  }

  return tabs;
}

/**
 * The section stack, derived from the tab list so a section and its tab are the same set by
 * construction: "See all" lands on exactly what the row counted. Sections overlap on purpose.
 * Ordering within a section is the caller's, so a row and its category can sort alike.
 */
export function buildSections(plugins: PluginCard[]): PluginSection[] {
  return buildTabs(plugins)
    .filter((tab) => tab.id !== TAB_ALL)
    .map((tab) => ({
      id: tab.id,
      isCategory: tab.isCategory,
      plugins: plugins.filter((plugin) => matchesTab(plugin, tab.id)),
    }));
}

/**
 * Maps the legacy `pluginType` hash parameter onto a tab. Nothing writes it any more, but
 * CorePluginsAdmin's ThemesIntro.vue and PluginsTable.vue still link in with it.
 */
export function tabFromLegacyPluginType(pluginType: string): string|null {
  switch (pluginType) {
    case 'themes':
      return TAB_THEMES;
    case 'plugins':
      return TAB_ALL;
    default:
      return null;
  }
}
