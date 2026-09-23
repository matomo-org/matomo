/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { translate, translateOrDefault, ucfirst } from 'CoreHome';
import {
  PluginTab,
  SECTION_BESTSELLING,
  SECTION_FEATURED,
  TAB_ALL,
  TAB_BUNDLES,
  TAB_THEMES,
} from './pluginGrouping';

/**
 * Labels for the tab bar, the card chips and the section headings, which all name the same things.
 *
 * Beside `pluginGrouping.ts` rather than inside it: that module imports nothing from `CoreHome`,
 * and its spec relies on loading it without mocking one.
 */

/**
 * The sections that name a plugin type or a promotion rather than a category slug, and so have a
 * fixed label. The promoted two have no tab, but their headings are resolved the same way.
 */
const TYPE_TAB_KEYS: Record<string, string> = {
  [TAB_ALL]: 'Marketplace_Home',
  [TAB_BUNDLES]: 'Marketplace_Bundles',
  [TAB_THEMES]: 'CorePluginsAdmin_Themes',
  [SECTION_FEATURED]: 'Marketplace_Featured',
  [SECTION_BESTSELLING]: 'Marketplace_BestSelling',
};

/**
 * The display name for a category slug, or '' for no category. Falls back to the slug itself, so a
 * category with no key yet still reads. translateOrDefault, not translate: an unknown key makes
 * translate() return "The string ... was not loaded in javascript".
 *
 * Capitalised as English, not in the reader's locale: the slug is an ASCII identifier and half of
 * what it builds is a translation key. Left to the browser's locale, 'insights' capitalises to
 * 'Insights' everywhere except Turkish and Azerbaijani, where the i takes a dot - so those two
 * would look up Marketplace_Categoryİnsights, a key nobody wrote, miss, and fall back to a
 * spelling of the slug no other reader sees.
 */
export function categoryLabel(slug: string): string {
  if (!slug) {
    return '';
  }

  const name = ucfirst(slug, 'en');
  const key = `Marketplace_Category${name}`;
  const label = translateOrDefault(key);

  return label === key ? name : label;
}

export function tabLabel(tab: Pick<PluginTab, 'id'|'isCategory'>): string {
  if (!tab.isCategory) {
    return translate(TYPE_TAB_KEYS[tab.id] ?? tab.id);
  }

  return categoryLabel(tab.id);
}
