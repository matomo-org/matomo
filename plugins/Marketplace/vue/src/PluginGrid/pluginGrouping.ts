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
export const TAB_BUNDLES = 'bundles';
export const TAB_THEMES = 'themes';

/**
 * Everything no category claims.
 *
 * Not a value the Marketplace sends: an unclassified plugin arrives with an empty `categories`,
 * and this is the id {@link buildTabs} invents so those plugins - most of the catalogue - have a
 * tab and a section of their own rather than being reachable only through All plugins and search.
 */
export const TAB_OTHER = 'other';

/**
 * The Marketplace's older singular `category` field used this string to mean "nobody has
 * classified this yet". `categories` says the same thing with an empty array, so this should not
 * appear any more - but if it ever turns up inside the array it has to be dropped, or the tab bar
 * grows an "Uncategorised" tab standing next to "Other" for the same idea.
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
export const TYPE_TABS = [TAB_ALL, TAB_BUNDLES, TAB_THEMES];

export interface PluginTab {
  id: string;
  /**
   * How many plugins the tab holds. `all` counts the whole catalogue.
   *
   * These do not add up to the catalogue: a plugin filed under two categories is counted by both,
   * the same way it appears under both tabs. Nothing renders a count today.
   */
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

/**
 * The category slugs a plugin is filed under.
 *
 * `categories` is an array by design, so belonging to a category is a contains test rather than an
 * equality one and a plugin may appear in more than one section. Anything malformed reads as
 * unclassified rather than throwing: this runs over whatever the Marketplace last sent.
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
      // themes and bundles are left out because they have sections of their own and every one of
      // them is unclassified: without this, Other would be mostly themes. A slug literally named
      // `other` means the same thing as this bucket, so the two merge rather than splitting the
      // plugins across a pair of tabs that share an id - see buildTabs().
      return !plugin.isTheme
        && !plugin.isBundle
        && (isUnclassified(plugin) || pluginCategories(plugin).includes(TAB_OTHER));
    default:
      return pluginCategories(plugin).includes(tabId);
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
 * The category half of this list is data, not a fixed vocabulary. When the Marketplace finishes
 * adopting the user-facing taxonomy the only thing that changes here is which slugs arrive in
 * `plugin.categories`.
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
        // matchesTab() folds this slug into the synthetic bucket below, so counting it here as
        // well would produce two tabs sharing one id
        return;
      }
      categoryCounts.set(slug, (categoryCounts.get(slug) ?? 0) + 1);
    });
  });

  [...categoryCounts.keys()].sort().forEach((id) => {
    tabs.push({ id, count: categoryCounts.get(id) as number, isCategory: true });
  });

  // last, after the named categories: it is where a plugin goes when none of them claims it.
  // isCategory, so it takes its label from Marketplace_CategoryOther like any other category.
  const otherCount = plugins.filter((plugin) => matchesTab(plugin, TAB_OTHER)).length;
  if (otherCount > 0) {
    tabs.push({ id: TAB_OTHER, count: otherCount, isCategory: true });
  }

  return tabs;
}

/**
 * The overview's section stack, derived from the tab list rather than declared beside it.
 *
 * Deriving it is the point. A section and its tab are then the same set by construction, so
 * "See all" lands on exactly the plugins the row was counting and the rule that hides that link
 * when the row already shows everything cannot lie. It also inherits the tab list's order -
 * bundles, themes, categories A-Z, other - and its habit of leaving out an empty group.
 *
 * Sections overlap on purpose: a theme filed under a category appears in both, the same way it
 * already appears under both tabs.
 *
 * Ordering within a section is the caller's, so that the row and the category it links to can be
 * sorted the same way.
 */
export function buildSections(plugins: PluginCard[]): PluginSection[] {
  return buildTabs(plugins)
    // the one tab that is a view of the whole catalogue rather than a slice of it
    .filter((tab) => tab.id !== TAB_ALL)
    .map((tab) => ({
      id: tab.id,
      isCategory: tab.isCategory,
      plugins: plugins.filter((plugin) => matchesTab(plugin, tab.id)),
    }));
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
    case 'plugins':
      return TAB_ALL;
    default:
      // `premium` lands here. The Marketplace no longer has a paid-only view, so an old link
      // falls back to All plugins rather than to a tab that is not there.
      return null;
  }
}
