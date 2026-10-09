/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { SearchFiltersPersistenceStore } from './SearchFiltersPersistence.store';

describe('CoreHome/SearchFiltersPersistence.store', () => {
  let store: SearchFiltersPersistenceStore;

  beforeEach(() => {
    // A fresh store per case, so every assertion starts from the same precondition.
    store = new SearchFiltersPersistenceStore();
  });

  it('returns stored filters for the same widget id (positive control)', () => {
    store.setSearchFilters('widgetReferrersgetWebsites', { filter_pattern: 'foo' });

    expect(store.getSearchFilters('widgetReferrersgetWebsites')).toEqual({ filter_pattern: 'foo' });
  });

  it('returns no filters for a widget id that stored nothing', () => {
    store.setSearchFilters('widgetA', { filter_pattern: 'foo' });

    expect(store.getSearchFilters('widgetB')).toEqual({});
  });

  it('ignores an empty widget id on write', () => {
    store.setSearchFilters('', { filter_pattern: 'foo' });

    expect(store.getSearchFilters('')).toEqual({});
    expect(store.getSearchFilters('widgetA')).toEqual({});
  });

  it('clears every widget\'s filters on reset', () => {
    store.setSearchFilters('widgetA', { filter_pattern: 'foo' });
    store.resetSearchFilters();

    expect(store.getSearchFilters('widgetA')).toEqual({});
  });

  // The defect: a widget id that collides with an object internal must not leak another widget's
  // filters, pollute the prototype, or return anything but what was stored for that exact id.
  const reservedIds = [
    '__proto__',
    'constructor',
    'prototype',
    'hasOwnProperty',
    'toString',
    'valueOf',
  ];

  it.each(reservedIds)(
    'returns no filters for reserved id "%s" that stored nothing',
    (reservedId) => {
      // Mirror the reported chain: widget A stores filters that carry a key matching widget B's id.
      store.setSearchFilters(reservedId, { filter_patternX: { module: 'API', method: 'x' } } as never);

      // Widget B asks for its own filters and must get nothing back.
      expect(store.getSearchFilters('filter_patternX')).toEqual({});
    },
  );

  it.each(reservedIds)(
    'does not replace the backing object prototype when id is "%s"',
    (reservedId) => {
      store.setSearchFilters(reservedId, { injected: true } as never);

      // Object.prototype and plain-object prototypes must be untouched by the write.
      expect(Object.getPrototypeOf({})).toBe(Object.prototype);
      expect(({} as Record<string, unknown>).injected).toBeUndefined();
    },
  );

  it.each(reservedIds)(
    'still stores and returns an entry for reserved id "%s" as its own data',
    (reservedId) => {
      store.setSearchFilters(reservedId, { filter_pattern: 'own' });

      expect(store.getSearchFilters(reservedId)).toEqual({ filter_pattern: 'own' });
    },
  );
});
