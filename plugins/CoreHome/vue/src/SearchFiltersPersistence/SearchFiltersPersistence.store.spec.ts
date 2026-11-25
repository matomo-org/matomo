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

  // Widget ids come from stored layout data, so an id that happens to match a JavaScript object
  // member name must behave like any other id: isolated to its own entry, returning only what was
  // stored for that exact id and nothing from any other id.
  const objectMemberIds = [
    '__proto__',
    'constructor',
    'prototype',
    'hasOwnProperty',
    'toString',
    'valueOf',
  ];

  it.each(objectMemberIds)(
    'does not surface an entry stored under "%s" when a different id is looked up',
    (memberId) => {
      store.setSearchFilters(memberId, { filter_patternX: { module: 'x' } } as never);

      expect(store.getSearchFilters('filter_patternX')).toEqual({});
    },
  );

  it.each(objectMemberIds)(
    'stores and returns an entry for "%s" as its own data only',
    (memberId) => {
      store.setSearchFilters(memberId, { filter_pattern: 'own' });

      expect(store.getSearchFilters(memberId)).toEqual({ filter_pattern: 'own' });
    },
  );
});
