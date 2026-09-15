/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { PluginCard } from '../types';
import { makePlugin } from '../testMarketplaceFixtures';
import {
  buildPromoSections,
  buildSections,
  buildTabs,
  filterPlugins,
  isOwned,
  isUnclassified,
  matchesQuery,
  matchesTab,
  ownerLabel,
  pluginCategories,
  pluginPromotions,
  parseMarketplaceDate,
  SECTION_BESTSELLING,
  SECTION_FEATURED,
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

    it('covers keywords, the way the Marketplace\'s own query search does', () => {
      const ldap = makePlugin({
        name: 'LoginLdap',
        displayName: 'LoginLdap',
        description: '',
        keywords: ['login', 'authentication', 'kerberos'],
      });

      expect(matchesQuery(ldap, 'kerberos')).toBe(true);
      expect(matchesQuery(ldap, 'sso')).toBe(false);
    });

    it('survives a plugin whose keywords are missing or malformed', () => {
      const odd = makePlugin({ name: 'odd', keywords: undefined as unknown as string[] });
      expect(matchesQuery(odd, 'odd')).toBe(true);
      expect(matchesQuery(odd, 'nothing')).toBe(false);
    });

    it('matches the owner as the card credits it, not only as the API spells it', () => {
      const own = makePlugin({
        name: 'own',
        displayName: 'own',
        description: '',
        owner: 'piwik',
      });

      expect(matchesQuery(own, 'matomo')).toBe(true);
      expect(matchesQuery(own, 'piwik')).toBe(true);
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

    it('orders the category tabs by their label rather than by their slug', () => {
      const labels: Record<string, string> = { security: 'Analyse', insights: 'Zebra' };
      const tabs = buildTabs(
        [
          makePlugin({ name: 'a', categories: ['insights'] }),
          makePlugin({ name: 'b', categories: ['security'] }),
        ],
        (tab) => labels[tab.id] ?? tab.id,
      );

      expect(tabs.map((t) => t.id)).toEqual([TAB_ALL, 'security', 'insights']);
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

  describe('pluginPromotions', () => {
    it('reads the positions the Marketplace sent', () => {
      const plugin = makePlugin({ promotions: { featured: 0, bestselling: 2 } });
      expect(pluginPromotions(plugin)).toEqual({ featured: 0, bestselling: 2 });
    });

    it('answers empty for a plugin in no list', () => {
      expect(pluginPromotions(makePlugin({ promotions: {} }))).toEqual({});
    });

    it('answers empty rather than throwing for a response from before the field existed', () => {
      expect(pluginPromotions(makePlugin({ promotions: undefined }))).toEqual({});
      expect(pluginPromotions(makePlugin({ promotions: null as never }))).toEqual({});
      expect(pluginPromotions(makePlugin({ promotions: [] as never }))).toEqual({});
    });

    it('drops an entry whose position is not a number, since the rows order on it', () => {
      const plugin = makePlugin({
        promotions: { featured: '1', bestselling: null, newest: NaN, '': 0 } as never,
      });
      expect(pluginPromotions(plugin)).toEqual({});
    });
  });

  describe('isOwned', () => {
    it('counts an installed plugin and a licensed one, whatever the licence says', () => {
      expect(isOwned(makePlugin({ isInstalled: true }))).toBe(true);
      expect(isOwned(makePlugin({ licenseStatus: 'Active' }))).toBe(true);
      expect(isOwned(makePlugin({ licenseStatus: 'Cancelled' }))).toBe(true);
    });

    it('does not count a requested trial, which is not a licence', () => {
      expect(isOwned(makePlugin({ isTrialRequested: true }))).toBe(false);
      expect(isOwned(makePlugin())).toBe(false);
    });
  });

  describe('buildPromoSections', () => {
    const featured = (name: string, position: number, overrides = {}) => makePlugin({
      name,
      promotions: { featured: position },
      ...overrides,
    });

    const enoughFeatured = () => [
      featured('d', 3),
      featured('b', 1),
      featured('a', 0),
      featured('c', 2),
    ];

    it('puts Featured before Best selling', () => {
      const plugins = [
        ...enoughFeatured(),
        makePlugin({ name: 'e', promotions: { bestselling: 0 } }),
      ];

      expect(buildPromoSections(plugins).map((s) => s.id))
        .toEqual([SECTION_FEATURED, SECTION_BESTSELLING]);
    });

    it('orders a row by the position the Marketplace gave it, not by name or date', () => {
      expect(names(buildPromoSections(enoughFeatured())[0].plugins))
        .toEqual(['a', 'b', 'c', 'd']);
    });

    it('falls back to the display name when two plugins share a position', () => {
      const plugins = [
        featured('d', 1), featured('c', 1), featured('b', 0), featured('a', 0),
      ];
      expect(names(buildPromoSections(plugins)[0].plugins)).toEqual(['a', 'b', 'c', 'd']);
    });

    it('marks the rows as having no tab, so their See all expands in place', () => {
      expect(buildPromoSections(enoughFeatured())[0])
        .toMatchObject({ hasTab: false, isCategory: false });
    });

    it('leaves out of Featured what the reader already has', () => {
      const plugins = [
        ...enoughFeatured(),
        featured('installed', 4, { isInstalled: true }),
        featured('licensed', 5, { licenseStatus: 'Active' }),
      ];

      expect(names(buildPromoSections(plugins)[0].plugins)).toEqual(['a', 'b', 'c', 'd']);
    });

    it('hides Featured entirely once too few are left to fill it', () => {
      const plugins = [...enoughFeatured(), featured('e', 4)];
      const owned = plugins.map((plugin, index) => (index < 2
        ? { ...plugin, isInstalled: true }
        : plugin));

      // three left is one row of stragglers at the top of the page, so the row goes rather than
      // shrinks - the threshold counts what is left after the reader's own plugins are dropped
      expect(buildPromoSections(owned).map((s) => s.id)).toEqual([]);
    });

    it('keeps Best selling whole, so the reader can see they already have the popular ones', () => {
      const plugins = [
        makePlugin({ name: 'a', promotions: { bestselling: 0 }, isInstalled: true }),
        makePlugin({ name: 'b', promotions: { bestselling: 1 }, licenseStatus: 'Cancelled' }),
      ];

      const sections = buildPromoSections(plugins);

      expect(sections.map((s) => s.id)).toEqual([SECTION_BESTSELLING]);
      expect(names(sections[0].plugins)).toEqual(['a', 'b']);
    });

    it('shows Best selling from a single plugin, unlike Featured', () => {
      const plugins = [makePlugin({ name: 'a', promotions: { bestselling: 0 } })];
      expect(buildPromoSections(plugins).map((s) => s.id)).toEqual([SECTION_BESTSELLING]);
    });

    it('answers empty for a catalogue with nothing promoted', () => {
      expect(buildPromoSections([makePlugin({ name: 'a' })])).toEqual([]);
      expect(buildPromoSections([])).toEqual([]);
    });

    it('lets one plugin lead both rows', () => {
      const plugins = [
        ...enoughFeatured(),
        makePlugin({ name: 'z', promotions: { featured: 4, bestselling: 0 } }),
      ];

      const sections = buildPromoSections(plugins);

      expect(names(sections[0].plugins)).toEqual(['a', 'b', 'c', 'd', 'z']);
      expect(names(sections[1].plugins)).toEqual(['z']);
    });

    it('does not mutate the catalogue it was given', () => {
      const plugins = enoughFeatured();
      buildPromoSections(plugins);
      expect(names(plugins)).toEqual(['d', 'b', 'a', 'c']);
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
