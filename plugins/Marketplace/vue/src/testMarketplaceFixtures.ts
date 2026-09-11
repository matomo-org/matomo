/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { MarketplaceContext, PluginCard } from './types';

/**
 * One plugin fixture for every Marketplace spec, so a new card field has one place to be added.
 * Cast rather than fully populated: the details-only half of `PluginCard` is of no interest here.
 */
export function makePlugin(overrides: Partial<PluginCard> = {}): PluginCard {
  const name = overrides.name ?? 'Funnels';

  return {
    name,
    // sorting and searching both read displayName, so it follows the name unless a spec sets it
    displayName: name,
    description: '',
    owner: 'someone',
    categories: [],
    coverImage: 'https://plugins.matomo.org/img/funnels.png',
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
    priceFrom: null,
    consumer: {},
    licenseStatus: '',
    lastUpdated: 'Jun 8, 2026',
    lastUpdatedRaw: '2026-06-08 00:00:00',
    createdDateTime: '2020-01-01 00:00:00',
    ...overrides,
  } as unknown as PluginCard;
}

/** A catalogue of distinctly named plugins, for a spec that only counts what a grid renders. */
export function makePlugins(count: number): PluginCard[] {
  return Array.from({ length: count }, (_unused, index) => makePlugin({
    name: `plugin${index}`,
    displayName: `Plugin ${index}`,
    description: '',
    owner: 'someone',
  }));
}

/** A super user on a single server who can install anything, which most specs want. */
export const CARD_CONTEXT: MarketplaceContext = {
  isSuperUser: true,
  isPluginsAdminEnabled: true,
  isMultiServerEnvironment: false,
  isValidConsumer: true,
  isAutoUpdatePossible: true,
  activateNonce: 'a',
  deactivateNonce: 'd',
  installNonce: 'i',
  updateNonce: 'u',
};

/**
 * A matchMedia stub driven by a width, so the breakpoint table itself is under test; jsdom has no
 * matchMedia. Returns a resize, which fires `change` as a browser would, and a removal count.
 */
export function stubViewport(width: number): {
  resizeTo: (next: number) => void,
  removed: () => number,
} {
  let current = width;
  let removed = 0;
  const handlers: (() => void)[] = [];

  window.matchMedia = ((query: string) => ({
    get matches() {
      const max = /max-width:\s*(\d+)px/.exec(query);
      return max ? current <= Number(max[1]) : false;
    },
    media: query,
    addEventListener: (_event: string, handler: () => void) => handlers.push(handler),
    removeEventListener: (_event: string, handler: () => void) => {
      // really drops it, as a browser would, so an unsubscribe that misses one still shows up
      handlers.splice(handlers.indexOf(handler), 1);
      removed += 1;
    },
  })) as unknown as typeof window.matchMedia;

  return {
    resizeTo: (next: number) => {
      current = next;
      handlers.forEach((handler) => handler());
    },
    removed: () => removed,
  };
}
