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
 * The two promoted rows at the top of the overview. Not tabs and not categories: the Marketplace
 * chooses what is in them and in what order, and "See all" opens a list of its own rather than a
 * tab - see {@link buildPromoSections} and {@link promotedPlugins}.
 */
export const SECTION_FEATURED = 'featured';
export const SECTION_BESTSELLING = 'bestselling';

/** The promoted sections, in display order. Featured leads, Best selling follows. */
export const PROMO_SECTIONS = [SECTION_FEATURED, SECTION_BESTSELLING];

/**
 * Featured is the first thing on the page, so a row of one or two reads as an empty Marketplace
 * rather than as a short list. Below this many it is left out entirely; every other section shows
 * from one plugin up.
 */
export const FEATURED_MIN_PLUGINS = 4;

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

/** Sort methods. The first three match `Marketplace\Input\Sort`; developer is client-side only. */
export const SORT_LAST_UPDATED = 'lastupdated';
export const SORT_NEWEST = 'newest';
export const SORT_ALPHA = 'alpha';
export const SORT_DEVELOPER = 'developer';

/**
 * The tabs that are not categories. Themes is one of them but does not lead the bar with the
 * others: {@link buildTabs} files it after the category run, beside Other.
 */
export const TYPE_TABS = [TAB_ALL, TAB_BUNDLES, TAB_THEMES];

/** The type tabs that lead the bar, in display order. Category tabs follow them. */
const LEADING_TYPE_TABS = [TAB_ALL, TAB_BUNDLES];

/**
 * The tabs that close the bar, in display order, after the categories. Neither is a category a
 * plugin can carry: Themes is a kind of plugin and Other is what no category claimed, so both
 * would break the alphabetical run they now follow.
 */
const TRAILING_TABS = [TAB_THEMES, TAB_OTHER];

export interface PluginTab {
  id: string;
  /** How many plugins the tab holds; counts overlap, since a plugin can have two categories. */
  count: number;
  /** A category tab takes its label from a translation key; a type tab has a fixed one. */
  isCategory: boolean;
}

/** One row of the overview's section stack: a heading, a row of cards and a "See all". */
export interface PluginSection {
  /**
   * What the section lists: a tab id for the stack, a promotion slug for the promoted rows. Which
   * of the two "See all" opens is {@link isPromoSection}'s answer rather than a field here.
   */
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

/**
 * Whether a plugin is credited to Matomo, on its card and on its page. A bundle always is, whoever
 * the Marketplace names as its owner: a bundle is Matomo's own packaging of Matomo's plugins, and
 * it is sold as such.
 */
export function isByMatomo(plugin: PluginCard): boolean {
  return !!plugin.isBundle || ownerLabel(plugin) === 'Matomo';
}

/**
 * The same fields the Marketplace's own `plugins?query=` search covers, plus the owner as the card
 * credits it - "Matomo" has to find a piwik-owned plugin, since that is the only name on screen.
 */
export function matchesQuery(plugin: PluginCard, query: string): boolean {
  const needle = (query || '').trim().toLowerCase();
  if (!needle) {
    return true;
  }

  const fields = [
    plugin.displayName,
    plugin.name,
    plugin.description,
    plugin.owner,
    ownerLabel(plugin),
    ...(Array.isArray(plugin.keywords) ? plugin.keywords : []),
  ];

  return fields.some((field) => (typeof field === 'string' ? field : '')
    .toLowerCase()
    .includes(needle));
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

/**
 * The promotion lists this plugin appears in, as slug -> position. Anything malformed reads as no
 * promotion rather than throwing: a bad position would otherwise decide where a card sits.
 */
export function pluginPromotions(plugin: PluginCard): Record<string, number> {
  const { promotions } = plugin;

  if (!promotions || typeof promotions !== 'object' || Array.isArray(promotions)) {
    return {};
  }

  const positions: Record<string, number> = {};

  Object.keys(promotions).forEach((slug) => {
    const position = promotions[slug];

    if (slug && typeof position === 'number' && Number.isFinite(position)) {
      positions[slug] = position;
    }
  });

  return positions;
}

/**
 * Whether the reader already has this plugin, by any route: installed, or covered by a license
 * whatever its state. A cancelled or expired license still means they have seen the plugin and
 * decided, so Featured - which is there to introduce plugins - passes over it. A requested trial
 * is not a licence and does not count.
 *
 * Only Featured filters on this. Best selling deliberately shows what the reader owns, so that
 * owning the popular ones is visible rather than inferred from an absence.
 */
export function isOwned(plugin: PluginCard): boolean {
  return !!plugin.isInstalled || !!plugin.licenseStatus;
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

/**
 * Bundles by seat tier, smallest first, so the Team, Business and Enterprise ladder reads in the
 * order it is priced in rather than alphabetically.
 *
 * A bundle whose tier carries no number - `bundleSeats` is unset, see `Plugins::addBundleSeats()` -
 * sorts last: "Unlimited users" is the top of the ladder, and a bundle whose variations disagree
 * on a tier has no place on it. Ties fall back to the display name, as every other sort does.
 */
export function sortBundles(plugins: PluginCard[]): PluginCard[] {
  return [...plugins].sort((a, b) => {
    const seatsA = typeof a.bundleSeats === 'number' ? a.bundleSeats : Infinity;
    const seatsB = typeof b.bundleSeats === 'number' ? b.bundleSeats : Infinity;

    return (seatsA === seatsB ? 0 : seatsA - seatsB)
      || (a.displayName || '').localeCompare(b.displayName || '');
  });
}

/**
 * How one tab's list is ordered. Bundles have an order of their own - see {@link sortBundles} -
 * and ignore the sort control; every other tab is {@link sortPlugins}.
 */
export function sortTabPlugins(
  plugins: PluginCard[],
  sort: string,
  tabId: string,
): PluginCard[] {
  return tabId === TAB_BUNDLES ? sortBundles(plugins) : sortPlugins(plugins, sort);
}

export function filterPlugins(
  plugins: PluginCard[],
  tabId: string,
  query: string,
): PluginCard[] {
  return plugins.filter((plugin) => matchesTab(plugin, tabId) && matchesQuery(plugin, query));
}

/** How a tab is named on screen. Ordering the category tabs is the only thing this is used for. */
export type TabLabeller = (tab: Pick<PluginTab, 'id'|'isCategory'>) => string;

/** Falls back to the slug, so a caller with no translations still gets a stable order. */
const slugAsLabel: TabLabeller = (tab) => tab.id;

/**
 * The tab list, built from the data rather than declared, so the category half follows whatever
 * slugs arrive in `plugin.categories`. An empty tab is left out: some categories hold as few as
 * five plugins, so one delisting can empty one.
 *
 * `labelFor` is a parameter rather than an import so that this module stays free of `CoreHome` -
 * see the note in `categoryLabels.ts`.
 */
export function buildTabs(plugins: PluginCard[], labelFor: TabLabeller = slugAsLabel): PluginTab[] {
  const tabs: PluginTab[] = [];

  LEADING_TYPE_TABS.forEach((id) => {
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

  // Ordered by the label rather than the slug: the slug is always English, so any other locale
  // would otherwise get a bar sorted by words its reader never sees.
  [...categoryCounts.keys()]
    .sort((a, b) => labelFor({ id: a, isCategory: true })
      .localeCompare(labelFor({ id: b, isCategory: true })))
    .forEach((id) => {
      tabs.push({ id, count: categoryCounts.get(id) as number, isCategory: true });
    });

  TRAILING_TABS.forEach((id) => {
    const count = plugins.filter((plugin) => matchesTab(plugin, id)).length;

    if (count > 0) {
      tabs.push({ id, count, isCategory: !TYPE_TABS.includes(id) });
    }
  });

  return tabs;
}

/**
 * The section stack, derived from the tab list so a section and its tab are the same set by
 * construction: "See all" lands on exactly what the row counted. Sections overlap on purpose.
 * Ordering within a section is the caller's, so a row and its category can sort alike.
 */
export function buildSections(
  plugins: PluginCard[],
  labelFor: TabLabeller = slugAsLabel,
): PluginSection[] {
  return buildTabs(plugins, labelFor)
    .filter((tab) => tab.id !== TAB_ALL)
    .map((tab) => ({
      id: tab.id,
      isCategory: tab.isCategory,
      plugins: plugins.filter((plugin) => matchesTab(plugin, tab.id)),
    }));
}

/** Whether a section is one of the promoted rows rather than a tab's contents. */
export function isPromoSection(sectionId: string): boolean {
  return PROMO_SECTIONS.includes(sectionId);
}

/**
 * Everything one promotion holds, in the Marketplace's order: the row shows the first cards of
 * this and its "See all" view shows all of them, so both are the same list cut in two places.
 *
 * Ordering is the Marketplace's position rather than the page's sort - promoting a plugin is
 * pointless if the reader's sort can move it to the bottom of the row - and ties fall back to the
 * display name so a duplicated position cannot reorder itself between renders.
 */
export function promotedPlugins(plugins: PluginCard[], sectionId: string): PluginCard[] {
  return plugins
    .filter((plugin) => sectionId in pluginPromotions(plugin))
    .filter((plugin) => sectionId !== SECTION_FEATURED || !isOwned(plugin))
    .sort((a, b) => (pluginPromotions(a)[sectionId] - pluginPromotions(b)[sectionId])
      || (a.displayName || '').localeCompare(b.displayName || ''));
}

/**
 * The promoted rows, in front of the stack {@link buildSections} derives from the tab bar.
 *
 * Kept apart from that one on purpose: those sections are a tab's contents by construction, and
 * these have no tab, so their "See all" opens the promotion's own list - see
 * {@link promotedPlugins}, which is what both the row and that list are cut from.
 *
 * A row the reader has nothing to gain from is left out: Featured hides what they already own, and
 * then hides itself unless {@link FEATURED_MIN_PLUGINS} plugins are left to show.
 */
export function buildPromoSections(plugins: PluginCard[]): PluginSection[] {
  const sections: PluginSection[] = [];

  PROMO_SECTIONS.forEach((id) => {
    const promoted = promotedPlugins(plugins, id);
    const minimum = id === SECTION_FEATURED ? FEATURED_MIN_PLUGINS : 1;

    if (promoted.length >= minimum) {
      sections.push({
        id, isCategory: false, plugins: promoted,
      });
    }
  });

  return sections;
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
