/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { PluginCard } from '../types';
import {
  buildTabs,
  CATEGORY_UNCATEGORISED,
  filterPlugins,
  matchesQuery,
  matchesTab,
  ownerLabel,
  parseMarketplaceDate,
  sortPlugins,
  SORT_ALPHA,
  SORT_DEVELOPER,
  SORT_LAST_UPDATED,
  SORT_NEWEST,
  SORT_POPULAR,
  TAB_ALL,
  TAB_BUNDLES,
  TAB_PREMIUM,
  TAB_THEMES,
  tabFromLegacyPluginType,
} from './pluginGrouping';

function plugin(overrides: Partial<PluginCard> & { name: string }): PluginCard {
  return {
    displayName: overrides.name,
    description: '',
    owner: 'someone',
    category: CATEGORY_UNCATEGORISED,
    coverImage: '',
    isFree: true,
    isPaid: false,
    isTheme: false,
    isInstalled: false,
    isActivated: false,
    isInvalid: false,
    isDownloadable: true,
    canBeUpdated: false,
    hasDownloadLink: true,
    hasExceededLicense: false,
    isMissingLicense: false,
    isEligibleForFreeTrial: false,
    isTrialRequested: false,
    canTrialBeRequested: false,
    missingRequirements: [],
    numDownloads: 0,
    numDownloadsPretty: '0',
    lastUpdated: 'Jan 1, 2020',
    lastUpdatedRaw: '2020-01-01 00:00:00',
    createdDateTime: '2020-01-01 00:00:00',
    ...overrides,
  } as unknown as PluginCard;
}

const names = (plugins: PluginCard[]) => plugins.map((p) => p.name);

describe('Marketplace/pluginGrouping', () => {
  describe('parseMarketplaceDate', () => {
    it('parses the space-separated form the Marketplace actually sends', () => {
      expect(parseMarketplaceDate('2015-11-20 19:16:03'))
        .toBe(Date.UTC(2015, 10, 20, 19, 16, 3));
    });

    it('parses a date without a time', () => {
      expect(parseMarketplaceDate('2015-11-20')).toBe(Date.UTC(2015, 10, 20));
    });

    it('returns null rather than NaN for anything it cannot read', () => {
      expect(parseMarketplaceDate('Jun 8, 2026')).toBeNull();
      expect(parseMarketplaceDate('')).toBeNull();
      expect(parseMarketplaceDate(undefined)).toBeNull();
      expect(parseMarketplaceDate(null)).toBeNull();
      expect(parseMarketplaceDate(12345)).toBeNull();
    });
  });

  describe('sortPlugins', () => {
    it('does not mutate its argument', () => {
      const plugins = [plugin({ name: 'B' }), plugin({ name: 'A' })];
      sortPlugins(plugins, SORT_ALPHA);
      expect(names(plugins)).toEqual(['B', 'A']);
    });

    it('orders by last updated on the raw date, not the display string', () => {
      // display order and raw order disagree: sorting the display string puts Apr ahead of Jun
      const plugins = [
        plugin({ name: 'older', lastUpdated: 'Jun 8, 2024', lastUpdatedRaw: '2024-06-08 00:00:00' }),
        plugin({ name: 'newer', lastUpdated: 'Apr 2, 2026', lastUpdatedRaw: '2026-04-02 00:00:00' }),
      ];
      expect(names(sortPlugins(plugins, SORT_LAST_UPDATED))).toEqual(['newer', 'older']);
    });

    it('sorts a malformed date last instead of scattering it', () => {
      const plugins = [
        plugin({ name: 'broken', lastUpdatedRaw: 'not a date' }),
        plugin({ name: 'fine', lastUpdatedRaw: '2024-06-08 00:00:00' }),
      ];
      expect(names(sortPlugins(plugins, SORT_LAST_UPDATED))).toEqual(['fine', 'broken']);
    });

    it('sorts a null download count last rather than treating it as zero', () => {
      const plugins = [
        plugin({ name: 'unknown', numDownloads: null }),
        plugin({ name: 'none', numDownloads: 0 }),
        plugin({ name: 'many', numDownloads: 500 }),
      ];
      expect(names(sortPlugins(plugins, SORT_POPULAR))).toEqual(['many', 'none', 'unknown']);
    });

    it('leaves paid plugins at the end of the popularity sort, because the API nulls their'
      + ' download count', () => {
      const plugins = [
        plugin({ name: 'Paid', isPaid: true, numDownloads: null }),
        plugin({ name: 'Free', numDownloads: 10 }),
      ];
      expect(names(sortPlugins(plugins, SORT_POPULAR))).toEqual(['Free', 'Paid']);
    });

    it('breaks every tie on the display name so the order is stable', () => {
      const plugins = [
        plugin({ name: 'Zebra', numDownloads: 5 }),
        plugin({ name: 'Alpha', numDownloads: 5 }),
      ];
      expect(names(sortPlugins(plugins, SORT_POPULAR))).toEqual(['Alpha', 'Zebra']);
    });

    it('sorts by newest on createdDateTime', () => {
      const plugins = [
        plugin({ name: 'old', createdDateTime: '2015-01-01 00:00:00' }),
        plugin({ name: 'new', createdDateTime: '2026-01-01 00:00:00' }),
      ];
      expect(names(sortPlugins(plugins, SORT_NEWEST))).toEqual(['new', 'old']);
    });

    it('sorts by developer, crediting Matomo-owned plugins to Matomo', () => {
      const plugins = [
        plugin({ name: 'third', owner: 'zzz' }),
        plugin({ name: 'first', owner: 'piwik' }),
        plugin({ name: 'second', owner: 'matomo-org' }),
      ];
      // piwik and matomo-org both read as "Matomo", so they group together and tie-break by name
      expect(names(sortPlugins(plugins, SORT_DEVELOPER))).toEqual(['first', 'second', 'third']);
    });

    it('falls back to last updated for an unknown sort method', () => {
      const plugins = [
        plugin({ name: 'older', lastUpdatedRaw: '2020-01-01 00:00:00' }),
        plugin({ name: 'newer', lastUpdatedRaw: '2026-01-01 00:00:00' }),
      ];
      expect(names(sortPlugins(plugins, 'nonsense'))).toEqual(['newer', 'older']);
    });
  });

  describe('matchesQuery', () => {
    const p = plugin({
      name: 'Funnels',
      displayName: 'Funnels',
      description: 'Understand where visitors drop off',
      owner: 'InnoCraft',
    });

    it('matches on name, description and owner, case-insensitively', () => {
      expect(matchesQuery(p, 'funnel')).toBe(true);
      expect(matchesQuery(p, 'DROP OFF')).toBe(true);
      expect(matchesQuery(p, 'innocraft')).toBe(true);
    });

    it('treats an empty or whitespace query as no filter', () => {
      expect(matchesQuery(p, '')).toBe(true);
      expect(matchesQuery(p, '   ')).toBe(true);
    });

    it('does not match an unrelated term', () => {
      expect(matchesQuery(p, 'heatmap')).toBe(false);
    });
  });

  describe('matchesTab', () => {
    it('resolves the type tabs from their flags', () => {
      const paid = plugin({ name: 'Paid', isPaid: true });
      const bundle = plugin({ name: 'Bundle', isBundle: true });
      const theme = plugin({ name: 'Theme', isTheme: true });

      expect(matchesTab(paid, TAB_ALL)).toBe(true);
      expect(matchesTab(paid, TAB_PREMIUM)).toBe(true);
      expect(matchesTab(bundle, TAB_BUNDLES)).toBe(true);
      expect(matchesTab(theme, TAB_THEMES)).toBe(true);
      expect(matchesTab(theme, TAB_PREMIUM)).toBe(false);
    });

    it('resolves anything else as a category value', () => {
      const p = plugin({ name: 'p', category: 'insights' });
      expect(matchesTab(p, 'insights')).toBe(true);
      expect(matchesTab(p, 'security')).toBe(false);
    });
  });

  describe('buildTabs', () => {
    it('hides a type tab with no members', () => {
      const tabs = buildTabs([plugin({ name: 'Free' })]);
      expect(tabs.map((t) => t.id)).toEqual([TAB_ALL]);
    });

    it('hides a category tab once its last member goes', () => {
      const withCategory = [
        plugin({ name: 'a', category: 'insights' }),
        plugin({ name: 'b', category: 'security' }),
      ];
      expect(buildTabs(withCategory).map((t) => t.id))
        .toEqual([TAB_ALL, 'insights', 'security']);

      // security delists; its tab must go with it
      expect(buildTabs([withCategory[0]]).map((t) => t.id)).toEqual([TAB_ALL, 'insights']);
    });

    it('never offers uncategorised as a tab, however much of the catalogue carries it', () => {
      const tabs = buildTabs([
        plugin({ name: 'a' }),
        plugin({ name: 'b' }),
        plugin({ name: 'c', category: 'insights' }),
      ]);
      expect(tabs.map((t) => t.id)).toEqual([TAB_ALL, 'insights']);
    });

    it('ignores a missing or empty category', () => {
      const tabs = buildTabs([
        plugin({ name: 'a', category: '' }),
        plugin({ name: 'b', category: undefined }),
      ]);
      expect(tabs.map((t) => t.id)).toEqual([TAB_ALL]);
    });

    it('counts each tab, with all counting the whole catalogue', () => {
      const tabs = buildTabs([
        plugin({ name: 'a', isPaid: true, category: 'insights' }),
        plugin({ name: 'b', isPaid: true }),
        plugin({ name: 'c' }),
      ]);
      expect(tabs).toEqual([
        { id: TAB_ALL, count: 3, isCategory: false },
        { id: TAB_PREMIUM, count: 2, isCategory: false },
        { id: 'insights', count: 1, isCategory: true },
      ]);
    });

    it('orders type tabs first, then categories alphabetically', () => {
      const tabs = buildTabs([
        plugin({ name: 'a', category: 'security' }),
        plugin({ name: 'b', category: 'customisation' }),
        plugin({ name: 't', isTheme: true }),
      ]);
      expect(tabs.map((t) => t.id))
        .toEqual([TAB_ALL, TAB_THEMES, 'customisation', 'security']);
    });
  });

  describe('filterPlugins', () => {
    it('applies the tab and the query together', () => {
      const plugins = [
        plugin({ name: 'Funnels', isPaid: true }),
        plugin({ name: 'FunnelFree' }),
        plugin({ name: 'Heatmaps', isPaid: true }),
      ];
      expect(names(filterPlugins(plugins, TAB_PREMIUM, 'funnel'))).toEqual(['Funnels']);
    });
  });

  describe('tabFromLegacyPluginType', () => {
    it('maps the values CorePluginsAdmin still links in with', () => {
      expect(tabFromLegacyPluginType('themes')).toBe(TAB_THEMES);
      expect(tabFromLegacyPluginType('premium')).toBe(TAB_PREMIUM);
      expect(tabFromLegacyPluginType('plugins')).toBe(TAB_ALL);
    });

    it('returns null for anything else, so the current tab is left alone', () => {
      expect(tabFromLegacyPluginType('')).toBeNull();
      expect(tabFromLegacyPluginType('nonsense')).toBeNull();
    });
  });

  describe('ownerLabel', () => {
    it('credits both Matomo owner names to Matomo', () => {
      expect(ownerLabel(plugin({ name: 'a', owner: 'piwik' }))).toBe('Matomo');
      expect(ownerLabel(plugin({ name: 'a', owner: 'matomo-org' }))).toBe('Matomo');
      expect(ownerLabel(plugin({ name: 'a', owner: 'InnoCraft' }))).toBe('InnoCraft');
    });
  });
});
