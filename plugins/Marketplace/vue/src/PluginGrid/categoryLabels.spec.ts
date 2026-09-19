/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// Not the shared CoreHome mock: this spec is about the locale categoryLabel() capitalises in, so
// ucfirst() defaults to Turkish here - the locale that makes the dotted capital I - to stand in
// for a reader whose browser is set to it. Passing 'en' is what keeps the key ASCII.
vi.mock('CoreHome', () => ({
  translate: (key: string) => key,
  translateOrDefault: (key: string) => (
    key === 'Marketplace_CategoryInsights' ? 'Insights, translated' : key
  ),
  ucfirst: (value: string, locale?: string) => `${
    value.charAt(0).toLocaleUpperCase(locale || 'tr')}${value.slice(1)}`,
}));

/* eslint-disable import/first */
import { categoryLabel, tabLabel } from './categoryLabels';
import { TAB_ALL, TAB_BUNDLES } from './pluginGrouping';

describe('Marketplace/categoryLabels', () => {
  describe('categoryLabel', () => {
    it('has no label for no category', () => {
      expect(categoryLabel('')).toBe('');
    });

    it('builds the translation key in English, whatever the browser capitalises in', () => {
      expect(categoryLabel('insights')).toBe('Insights, translated');
    });

    it('falls back to an English capitalisation of an untranslated slug', () => {
      expect(categoryLabel('integrations')).toBe('Integrations');
    });
  });

  describe('tabLabel', () => {
    it('gives a type tab its own fixed key', () => {
      expect(tabLabel({ id: TAB_BUNDLES, isCategory: false })).toBe('Marketplace_Bundles');
      expect(tabLabel({ id: TAB_ALL, isCategory: false })).toBe('Marketplace_Home');
    });

    it('labels a category tab the way a chip is labelled', () => {
      expect(tabLabel({ id: 'insights', isCategory: true })).toBe('Insights, translated');
    });
  });
});
