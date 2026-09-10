/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { translate, translateOrDefault, ucfirst } from 'CoreHome';
import {
  PluginTab,
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

/** The tabs that name a plugin type rather than a category slug, and so have a fixed label. */
const TYPE_TAB_KEYS: Record<string, string> = {
  [TAB_ALL]: 'Marketplace_AllPlugins',
  [TAB_BUNDLES]: 'Marketplace_Bundles',
  [TAB_THEMES]: 'CorePluginsAdmin_Themes',
};

/**
 * The display name for a category slug, or '' for no category. Falls back to the slug itself, so a
 * category with no key yet still reads. translateOrDefault, not translate: an unknown key makes
 * translate() return "The string ... was not loaded in javascript".
 */
export function categoryLabel(slug: string): string {
  if (!slug) {
    return '';
  }

  const key = `Marketplace_Category${ucfirst(slug)}`;
  const label = translateOrDefault(key);

  return label === key ? ucfirst(slug) : label;
}

export function tabLabel(tab: Pick<PluginTab, 'id'|'isCategory'>): string {
  if (!tab.isCategory) {
    return translate(TYPE_TAB_KEYS[tab.id] ?? tab.id);
  }

  return categoryLabel(tab.id);
}
