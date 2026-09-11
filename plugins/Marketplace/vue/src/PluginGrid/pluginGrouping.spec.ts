/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { PluginCard } from '../types';
import { makePlugin } from '../testMarketplaceFixtures';
import {
  buildSections,
  buildTabs,
  filterPlugins,
  isUnclassified,
  matchesQuery,
  matchesTab,
  ownerLabel,
  pluginCategories,
  parseMarketplaceDate,
  sortPlugins,
  SORT_ALPHA,
  SORT_DEVELOPER,
  SORT_LAST_UPDATED,
  SORT_NEWEST,
  TAB_ALL,
  TAB_BUNDLES,
  TAB_OTHER,
  TAB_THEMES,
  tabFromLegacyPluginType,
} from './pluginGrouping';

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
      const plugins = [makePlugin({ name: 'B' }), makePlugin({ name: 'A' })];
      sortPlugins(plugins, SORT_ALPHA);
      expect(names(plugins)).toEqual(['B', 'A']);
    });

    it('orders by last updated on the raw date, not the display string', () => {
      const plugins = [
        makePlugin({ name: 'older', lastUpdated: 'Jun 8, 2024', lastUpdatedRaw: '2024-06-08 00:00:00' }),
        makePlugin({ name: 'newer', lastUpdated: 'Apr 2, 2026', lastUpdatedRaw: '2026-04-02 00:00:00' }),
      ];
      expect(names(sortPlugins(plugins, SORT_LAST_UPDATED))).toEqual(['newer', 'older']);
    });

    it('sorts a malformed date last instead of scattering it', () => {
      const plugins = [
        makePlugin({ name: 'broken', lastUpdatedRaw: 'not a date' }),
        makePlugin({ name: 'fine', lastUpdatedRaw: '2024-06-08 00:00:00' }),
      ];
      expect(names(sortPlugins(plugins, SORT_LAST_UPDATED))).toEqual(['fine', 'broken']);
    });

    it('sorts by newest on createdDateTime', () => {
      const plugins = [
        makePlugin({ name: 'old', createdDateTime: '2015-01-01 00:00:00' }),
        makePlugin({ name: 'new', createdDateTime: '2026-01-01 00:00:00' }),
      ];
      expect(names(sortPlugins(plugins, SORT_NEWEST))).toEqual(['new', 'old']);
    });

    it('sorts by developer, crediting Matomo-owned plugins to Matomo', () => {
      const plugins = [
        makePlugin({ name: 'third', owner: 'zzz' }),
        makePlugin({ name: 'first', owner: 'piwik' }),
        makePlugin({ name: 'second', owner: 'matomo-org' }),
      ];
      expect(names(sortPlugins(plugins, SORT_DEVELOPER))).toEqual(['first', 'second', 'third']);
    });

    it('falls back to last updated for an unknown sort method', () => {
      const plugins = [
        makePlugin({ name: 'older', lastUpdatedRaw: '2020-01-01 00:00:00' }),
        makePlugin({ name: 'newer', lastUpdatedRaw: '2026-01-01 00:00:00' }),
      ];
      expect(names(sortPlugins(plugins, 'nonsense'))).toEqual(['newer', 'older']);
    });
  });

  describe('matchesQuery', () => {
    const p = makePlugin({
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
      const paid = makePlugin({ name: 'Paid', isPaid: true });
      const bundle = makePlugin({ name: 'Bundle', isBundle: true });
      const theme = makePlugin({ name: 'Theme', isTheme: true });

      expect(matchesTab(paid, TAB_ALL)).toBe(true);
      expect(matchesTab(bundle, TAB_BUNDLES)).toBe(true);
      expect(matchesTab(theme, TAB_THEMES)).toBe(true);
      expect(matchesTab(theme, TAB_BUNDLES)).toBe(false);
    });

    it('resolves anything else as a category the plugin is filed under', () => {
      const p = makePlugin({ name: 'p', categories: ['insights'] });
      expect(matchesTab(p, 'insights')).toBe(true);
      expect(matchesTab(p, 'security')).toBe(false);
    });

    it('matches every category a plugin is filed under, not only the first', () => {
      const p = makePlugin({ name: 'p', categories: ['insights', 'security'] });
      expect(matchesTab(p, 'insights')).toBe(true);
      expect(matchesTab(p, 'security')).toBe(true);
    });

    it('puts an unclassified plugin in Other, but never a theme or a bundle', () => {
      expect(matchesTab(makePlugin({ name: 'a' }), TAB_OTHER)).toBe(true);
      expect(matchesTab(makePlugin({ name: 't', isTheme: true }), TAB_OTHER)).toBe(false);
      expect(matchesTab(makePlugin({ name: 'b', isBundle: true }), TAB_OTHER)).toBe(false);
      expect(matchesTab(makePlugin({ name: 'c', categories: ['insights'] }), TAB_OTHER)).toBe(false);
    });
  });

  describe('buildTabs', () => {
    it('hides a type tab with no members', () => {
      const tabs = buildTabs([makePlugin({ name: 'Free' })]);
      expect(tabs.map((t) => t.id)).toEqual([TAB_ALL, TAB_OTHER]);
    });

    it('hides a category tab once its last member goes', () => {
      const withCategory = [
        makePlugin({ name: 'a', categories: ['insights'] }),
        makePlugin({ name: 'b', categories: ['security'] }),
      ];
      expect(buildTabs(withCategory).map((t) => t.id))
        .toEqual([TAB_ALL, 'insights', 'security']);

      expect(buildTabs([withCategory[0]]).map((t) => t.id)).toEqual([TAB_ALL, 'insights']);
    });

    it('never offers the uncategorised sentinel as a tab of its own', () => {
      const tabs = buildTabs([
        makePlugin({ name: 'a', categories: ['uncategorised'] }),
        makePlugin({ name: 'b' }),
        makePlugin({ name: 'c', categories: ['insights'] }),
      ]);
      expect(tabs.map((t) => t.id)).toEqual([TAB_ALL, 'insights', TAB_OTHER]);
    });

    it('collects an unclassified plugin into Other, last', () => {
      const tabs = buildTabs([
        makePlugin({ name: 'a', categories: [] }),
        makePlugin({ name: 'b', categories: undefined as unknown as string[] }),
      ]);
      expect(tabs.map((t) => t.id)).toEqual([TAB_ALL, TAB_OTHER]);
    });

    it('folds a category slug named other into the same tab', () => {
      const tabs = buildTabs([
        makePlugin({ name: 'a', categories: ['other'] }),
        makePlugin({ name: 'b' }),
      ]);
      expect(tabs.map((t) => t.id)).toEqual([TAB_ALL, TAB_OTHER]);
      expect(tabs.find((t) => t.id === TAB_OTHER)?.count).toBe(2);
    });

    it('counts each tab, with all counting the whole catalogue', () => {
      const tabs = buildTabs([
        makePlugin({ name: 'a', isPaid: true, categories: ['insights'] }),
        makePlugin({ name: 'b', isPaid: true }),
        makePlugin({ name: 'c' }),
      ]);
      expect(tabs).toEqual([
        { id: TAB_ALL, count: 3, isCategory: false },
        { id: 'insights', count: 1, isCategory: true },
        { id: TAB_OTHER, count: 2, isCategory: true },
      ]);
    });

    it('orders type tabs first, then categories alphabetically', () => {
      const tabs = buildTabs([
        makePlugin({ name: 'a', categories: ['security'] }),
        makePlugin({ name: 'b', categories: ['customisation'] }),
        makePlugin({ name: 't', isTheme: true }),
      ]);
      expect(tabs.map((t) => t.id))
        .toEqual([TAB_ALL, TAB_THEMES, 'customisation', 'security']);
    });
  });

  describe('filterPlugins', () => {
    it('applies the tab and the query together', () => {
      const plugins = [
        makePlugin({ name: 'Funnels', categories: ['insights'] }),
        makePlugin({ name: 'FunnelFree' }),
        makePlugin({ name: 'Heatmaps', categories: ['insights'] }),
      ];
      expect(names(filterPlugins(plugins, 'insights', 'funnel'))).toEqual(['Funnels']);
    });
  });

  describe('tabFromLegacyPluginType', () => {
    it('maps the values CorePluginsAdmin still links in with', () => {
      expect(tabFromLegacyPluginType('themes')).toBe(TAB_THEMES);
      expect(tabFromLegacyPluginType('plugins')).toBe(TAB_ALL);
    });

    it('returns null for anything else, so the current tab is left alone', () => {
      expect(tabFromLegacyPluginType('')).toBeNull();
      expect(tabFromLegacyPluginType('nonsense')).toBeNull();
      expect(tabFromLegacyPluginType('premium')).toBeNull();
    });
  });

  describe('ownerLabel', () => {
    it('credits both Matomo owner names to Matomo', () => {
      expect(ownerLabel(makePlugin({ name: 'a', owner: 'piwik' }))).toBe('Matomo');
      expect(ownerLabel(makePlugin({ name: 'a', owner: 'matomo-org' }))).toBe('Matomo');
      expect(ownerLabel(makePlugin({ name: 'a', owner: 'InnoCraft' }))).toBe('InnoCraft');
    });
  });
  describe('pluginCategories', () => {
    it('answers with the slugs a plugin is filed under', () => {
      expect(pluginCategories(makePlugin({ name: 'a', categories: ['insights'] }))).toEqual(['insights']);
    });

    it('reads anything malformed as unclassified rather than throwing', () => {
      const malformed = [
        undefined,
        null,
        'insights',
        {},
      ] as unknown as string[][];

      malformed.forEach((categories) => {
        expect(pluginCategories(makePlugin({ name: 'a', categories }))).toEqual([]);
      });
    });

    it('drops empty values, non-strings and the uncategorised sentinel', () => {
      const categories = ['insights', '', 'uncategorised', 7, null] as unknown as string[];
      expect(pluginCategories(makePlugin({ name: 'a', categories }))).toEqual(['insights']);
    });

    it('reports a plugin no category claims', () => {
      expect(isUnclassified(makePlugin({ name: 'a' }))).toBe(true);
      expect(isUnclassified(makePlugin({ name: 'a', categories: ['uncategorised'] }))).toBe(true);
      expect(isUnclassified(makePlugin({ name: 'a', categories: ['insights'] }))).toBe(false);
    });
  });

  describe('buildSections', () => {
    const catalogue = [
      makePlugin({ name: 'Bundle', isBundle: true }),
      makePlugin({ name: 'Theme', isTheme: true }),
      makePlugin({ name: 'Sec', categories: ['security'] }),
      makePlugin({ name: 'Ins', categories: ['customisation'] }),
      makePlugin({ name: 'Loose' }),
    ];

    it('follows the tab order, without the all tab', () => {
      expect(buildSections(catalogue).map((s) => s.id))
        .toEqual([TAB_BUNDLES, TAB_THEMES, 'customisation', 'security', TAB_OTHER]);
    });

    it('holds every plugin of the section, not only the ones a row shows', () => {
      const many = Array.from({ length: 9 }, (_, i) => makePlugin({
        name: `p${i}`,
        categories: ['insights'],
      }));
      expect(buildSections(many)[0].plugins).toHaveLength(9);
    });

    it('leaves out a section with no members, and answers empty for an empty catalogue', () => {
      expect(buildSections([]).map((s) => s.id)).toEqual([]);
      expect(buildSections([makePlugin({ name: 'a' })]).map((s) => s.id)).toEqual([TAB_OTHER]);
    });

    it('lets a theme filed under a category sit in both sections', () => {
      const sections = buildSections([makePlugin({ name: 't', isTheme: true, categories: ['insights'] })]);
      expect(sections.map((s) => s.id)).toEqual([TAB_THEMES, 'insights']);
      expect(sections.every((s) => names(s.plugins).includes('t'))).toBe(true);
    });

    it('gives every section exactly the plugins its tab filters to', () => {
      buildSections(catalogue).forEach((section) => {
        expect(names(section.plugins)).toEqual(names(filterPlugins(catalogue, section.id, '')));
      });
    });

    it('leaves no plugin out of the stack entirely', () => {
      const sections = buildSections(catalogue);
      catalogue.forEach((candidate) => {
        expect(sections.some((s) => names(s.plugins).includes(candidate.name))).toBe(true);
      });
    });
  });
});
