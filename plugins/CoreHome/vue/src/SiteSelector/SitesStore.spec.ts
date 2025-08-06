/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { reactive } from 'vue';
import SitesStore from './SitesStore';
import AjaxHelper from '../AjaxHelper/AjaxHelper';

type MethodArgsArray = [
  (boolean | undefined)?,
  (number[] | undefined)?,
  (boolean | undefined)?,
  (string[] | undefined)?,
];

function getPossibleFilterArgCombinations(): MethodArgsArray[] {
  const onlySitesWithAdminAccessVals = [undefined, true, false];
  const excludedSitesVals = [undefined, [], [1], [1, 2], [3, 4, 1]];
  const onlySitesWithAtLeastWriteAccessVals = [undefined, true, false];
  const siteTypesToExcludeVals = [undefined, [], ['rollup'], ['rollup', 'intranet'], ['intranet', 'website', 'rollup']];

  const results = [] as MethodArgsArray[];
  onlySitesWithAdminAccessVals.forEach((onlySitesWithAdminAccess?: boolean) => {
    excludedSitesVals.forEach((excludedSites?: number[]) => {
      onlySitesWithAtLeastWriteAccessVals.forEach((onlySitesWithAtLeastWriteAccess?: boolean) => {
        siteTypesToExcludeVals.forEach((siteTypesToExclude?: string[]) => {
          results.push([
            onlySitesWithAdminAccess,
            excludedSites,
            onlySitesWithAtLeastWriteAccess,
            siteTypesToExclude,
          ]);
        });
      });
    });
  });

  return results;
}

describe('CoreHome/SitesStore', () => {
  const testingSitesStore: typeof SitesStore = SitesStore;
  const defaultSites = [
    {
      idsite: 1,
      name: 'Site1',
      type: 'website',
    },
    {
      idsite: 2,
      name: 'Site2',
      type: 'intranet',
    },
    {
      idsite: 3,
      name: 'Site3',
      type: 'rollup',
    },
    {
      idsite: 4,
      name: 'Site4',
      type: 'website',
    },
  ];
  const writeSites = defaultSites.slice(0, 2);
  const adminSites = defaultSites.slice(2);

  function resetTestingSitesStoreState() {
    (testingSitesStore as any).state = reactive({
      initialSites: [],
      isInitialized: false,
    });
    (testingSitesStore as any).stateFiltered = reactive({
      initialSites: [],
      isInitialized: false,
      excludedSites: [],
      onlySitesWithAdminAccess: false,
      onlySitesWithAtLeastWriteAccess: false,
      siteTypesToExclude: [],
    });
  }

  beforeEach(() => {
    // Mock AjaxHelper.fetch to return a collection of sites based on the provided filters
    jest.spyOn(AjaxHelper, 'fetch').mockImplementation((params) => {
      if (params.method !== 'SitesManager.getSitesWithMinimumAccess') {
        return Promise.resolve([]);
      }

      let sitesFiltered = defaultSites;
      if (params.permission === 'write') {
        sitesFiltered = writeSites;
      }

      if (params.permission === 'admin') {
        sitesFiltered = adminSites;
      }

      if (params.sitesToExclude.length > 0) {
        sitesFiltered = sitesFiltered.filter(
          (site) => !params.sitesToExclude.includes(site.idsite),
        );
      }

      if (params.siteTypesToExclude.length > 0) {
        sitesFiltered = sitesFiltered.filter(
          (site) => !params.siteTypesToExclude.includes(site.type),
        );
      }

      return Promise.resolve(sitesFiltered);
    });
  });
  afterEach(() => {
    // Reset the state of the store after each testcase
    resetTestingSitesStoreState();
  });

  describe('#isFiltered()', () => {
    getPossibleFilterArgCombinations().forEach((args: MethodArgsArray) => {
      const expected = !(
        args.every((arg) => typeof arg === 'undefined'
          || arg === false || (Array.isArray(arg) && arg.length === 0))
      );
      it(`should return '${expected}' for params: ${JSON.stringify(args)}`, async () => {
        expect(testingSitesStore.isFiltered(...args)).toEqual(expected);
      });
    });
  });

  describe('#matchesCurrentFilteredState()', () => {
    beforeEach(async () => {
      await testingSitesStore.loadInitialSites();
    });

    getPossibleFilterArgCombinations().forEach((args: MethodArgsArray) => {
      const isNotFiltered = !testingSitesStore.isFiltered(...args);
      const cases = [
        [
          `should return '${isNotFiltered}' when store initialised: 'true' and params: ${JSON.stringify(args)}`,
          isNotFiltered,
          false,
          args,
        ],
        [
          `should return '${isNotFiltered}' when store initialised: 'false' and params: ${JSON.stringify(args)}`,
          isNotFiltered,
          true,
          args,
        ],
        [
          `should return 'true' when filtered state and filters match. Params: ${JSON.stringify(args)}`,
          true,
          null,
          args,
        ],
      ] as [
        string,
        boolean,
        boolean | null,
        MethodArgsArray
      ][];
      it.each(cases)('%s', async (_label, expected, resetState, params) => {
        if (resetState) {
          resetTestingSitesStoreState();
        }
        if (resetState === null) {
          testingSitesStore.loadInitialSites(...params).then(() => {
            expect(testingSitesStore.matchesCurrentFilteredState(...params)).toEqual(expected);
          });
        }
      });
    });
  });

  describe('#loadInitialSites()', () => {
    it('should return the default initial sites', async () => {
      const sitesResponse = await testingSitesStore.loadInitialSites();
      expect(sitesResponse).toEqual(defaultSites);
    });

    it('should return the admin sites', async () => {
      expect(await testingSitesStore.loadInitialSites()).toEqual(defaultSites);
      const sitesResponse = await testingSitesStore.loadInitialSites(
        true,
      );
      expect(sitesResponse).toEqual(adminSites);
    });

    it('should return the write sites', async () => {
      expect(await testingSitesStore.loadInitialSites()).toEqual(defaultSites);
      const sitesResponse = await testingSitesStore.loadInitialSites(
        false,
        [],
        true,
      );
      expect(sitesResponse).toEqual(writeSites);
    });

    it('should return sites excluding site 2', async () => {
      expect(await testingSitesStore.loadInitialSites()).toEqual(defaultSites);
      const sitesResponse = await testingSitesStore.loadInitialSites(
        false,
        [2],
      );
      expect(sitesResponse).toEqual([
        ...defaultSites.slice(0, 1),
        ...defaultSites.slice(2),
      ]);
    });

    it('should return sites excluding sites 1 and 4', async () => {
      expect(await testingSitesStore.loadInitialSites()).toEqual(defaultSites);
      const sitesResponse = await testingSitesStore.loadInitialSites(
        false,
        [1, 4],
      );
      expect(sitesResponse).toEqual(defaultSites.slice(1, 3));
    });

    it('should return sites excluding type website', async () => {
      expect(await testingSitesStore.loadInitialSites()).toEqual(defaultSites);
      const sitesResponse = await testingSitesStore.loadInitialSites(
        false,
        [],
        false,
        ['website'],
      );
      expect(sitesResponse).toEqual(defaultSites.slice(1, 3));
    });

    it('should return sites excluding type rollup', async () => {
      expect(await testingSitesStore.loadInitialSites()).toEqual(defaultSites);
      const sitesResponse = await testingSitesStore.loadInitialSites(
        false,
        [],
        false,
        ['rollup'],
      );
      expect(sitesResponse).toEqual([
        ...defaultSites.slice(0, 2),
        ...defaultSites.slice(3),
      ]);
    });

    it('should return sites excluding type rollup and intranet', async () => {
      expect(await testingSitesStore.loadInitialSites()).toEqual(defaultSites);
      const sitesResponse = await testingSitesStore.loadInitialSites(
        false,
        [],
        false,
        ['rollup', 'intranet'],
      );
      expect(sitesResponse).toEqual([
        ...defaultSites.slice(0, 1),
        ...defaultSites.slice(3),
      ]);
    });

    it(
      'should return admin sites when both admin and write are enabled',
      async () => {
        expect(await testingSitesStore.loadInitialSites()).toEqual(defaultSites);
        const sitesResponse = await testingSitesStore.loadInitialSites(
          true,
          [],
          true,
        );
        expect(sitesResponse).toEqual(adminSites);
      },
    );

    it(
      'should return sites filtered by permission and excluded types',
      async () => {
        expect(await testingSitesStore.loadInitialSites()).toEqual(defaultSites);
        const sitesResponse = await testingSitesStore.loadInitialSites(
          false,
          [],
          true,
          ['intranet'],
        );
        expect(sitesResponse).toEqual(writeSites.slice(0, 1));
      },
    );

    it(
      'should return sites filtered by permission, excluded sites, and excluded types',
      async () => {
        expect(await testingSitesStore.loadInitialSites()).toEqual(defaultSites);
        const sitesResponse = await testingSitesStore.loadInitialSites(
          false,
          [1],
          true,
          ['intranet'],
        );
        expect(sitesResponse).toEqual([]);
      },
    );
  });
});
