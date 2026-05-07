/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { SavedSegment } from '../types';

type SegmentSelectorStoreModule = {
  default: {
    init: (config: Record<string, unknown>) => void;
    state: {
      value: {
        currentSegment: string;
        isInitialized: boolean;
        loginUrl: string;
        manageSegmentsUrl: string;
        segmentAccess: string;
      };
    };
    getSelectorViewModel: (searchValue: string) => {
      currentSegmentTitle: string;
      entries: Array<{
        key: string;
        type: string;
        classes?: string;
        label: string;
      }>;
    };
    getSegmentFromId: (idSegment?: string | number | null) => SavedSegment | null;
    setAvailableSegments: (segments: SavedSegment[]) => void;
  };
};

const mockTranslate = jest.fn((key: string, params?: unknown[]) => {
  if (key === 'SegmentEditor_CurrentlySelectedSegment') {
    return `Currently selected: ${params?.[0] || ''}`;
  }

  if (key === 'General_MaximumNumberOfSegmentsComparedIs') {
    return `Maximum: ${params?.[0] || ''}`;
  }

  return key;
});

const mockComparisonsStore = {
  getSegmentComparisons: jest.fn(() => []),
  isComparisonEnabled: jest.fn(() => false),
};

function createTranslations() {
  return {
    SegmentEditor_DefaultAllVisits: 'All visits',
    General_DefaultAppended: '(default)',
    General_SearchNoResults: 'No results',
    General_CanEditGlobalSegment: 'Can edit global',
    General_CanNotEditGlobalSegment: 'Cannot edit global',
    General_CanUnstarGlobalSegment: 'Can unstar global',
    General_CanNotUnstarGlobalSegment: 'Cannot unstar global',
    General_StarredByYou: 'Starred by you',
  };
}

function createSegments(): SavedSegment[] {
  return [
    {
      idsegment: 1,
      name: 'Caf&eacute; Visits',
      definition: 'countryCode==nz',
      login: 'alice',
      enable_all_users: 1,
      starred: '1',
    },
    {
      idsegment: 2,
      name: 'Mobile Visits',
      definition: 'deviceType==mobile',
      login: 'alice',
      enable_all_users: 1,
      starred: 0,
    },
    {
      idsegment: 3,
      name: 'unicode журнал 中文',
      definition: 'browserCode==ff',
      login: 'alice',
      enable_all_users: 1,
      starred: 0,
    },
  ];
}

function createConfig(overrides: Record<string, unknown> = {}) {
  return {
    availableSegments: createSegments(),
    currentSegment: 'countryCode==nz',
    isUserAnonymous: false,
    loginUrl: 'index.php?module=Login',
    manageSegmentsUrl: 'index.php?module=SegmentEditor&action=index',
    segmentAccess: 'write',
    translations: createTranslations(),
    userContext: {
      isAnonymous: false,
      hasSuperUserAccess: false,
      login: 'alice',
    },
    ...overrides,
  };
}

function loadFreshStore() {
  jest.resetModules();
  jest.doMock('CoreHome', () => ({
    ComparisonsStoreInstance: mockComparisonsStore,
    translate: mockTranslate,
  }), { virtual: true });

  const module = require('./SegmentSelector.store') as SegmentSelectorStoreModule;
  return module.default;
}

describe('SegmentEditor/SegmentSelector.store', () => {
  beforeEach(() => {
    jest.clearAllMocks();

    (window as unknown as {
      piwikHelper: {
        htmlDecode: (value: string) => string;
        normalize: (value: string) => string;
      };
    }).piwikHelper = {
      htmlDecode: (value: string) => value.replace('&eacute;', 'é'),
      normalize: (value: string) => value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase(),
    };

    (window as unknown as {
      piwik: {
        config: {
          data_comparison_segment_limit: number;
        };
      };
    }).piwik = {
      config: {
        data_comparison_segment_limit: 2,
      },
    };
  });

  it('populates the main state on init', () => {
    const store = loadFreshStore();
    const config = createConfig();

    store.init(config);

    expect(store.state.value.isInitialized).toBe(true);
    expect(store.state.value.currentSegment).toBe('countryCode==nz');
    expect(store.state.value.loginUrl).toBe('index.php?module=Login');
    expect(store.state.value.manageSegmentsUrl).toBe('index.php?module=SegmentEditor&action=index');
    expect(store.state.value.segmentAccess).toBe('write');
  });

  it('builds a base view model without mutating source segment starred values', () => {
    const store = loadFreshStore();
    const segments = createSegments();

    store.init(createConfig({ availableSegments: segments }));

    const viewModel = store.getSelectorViewModel('');
    const savedSegmentEntry = viewModel.entries.find((entry) => entry.key === 'segment-1');

    expect(viewModel.currentSegmentTitle).toBe('Café Visits');
    expect(viewModel.entries.map((entry) => entry.label)).toEqual(expect.arrayContaining([
      'All visits (default)',
      'Café Visits',
      'Mobile Visits',
    ]));
    expect(savedSegmentEntry?.classes).toContain('segmentStarred');
    expect(segments[0].starred).toBe('1');
  });

  it('normalizes rebuild-time available segments without mutating the source segments', () => {
    const store = loadFreshStore();
    const segments = createSegments();

    store.init(createConfig());
    store.setAvailableSegments(segments);

    expect(store.getSegmentFromId(1)?.starred).toBe(true);
    expect(store.getSegmentFromId(2)?.starred).toBe(false);
    expect(store.getSegmentFromId(3)?.starred).toBe(false);

    expect(segments[0].starred).toBe('1');
    expect(segments[1].starred).toBe(0);
    expect(segments[2].starred).toBe(0);
  });

  it('filters entries case- and diacritic-insensitively and shows no-results when needed', () => {
    const store = loadFreshStore();

    store.init(createConfig());

    const matchingViewModel = store.getSelectorViewModel('CAFE');
    const noResultsViewModel = store.getSelectorViewModel('tablet');

    expect(matchingViewModel.entries.map((entry) => entry.label)).toEqual(expect.arrayContaining([
      'Café Visits',
    ]));
    expect(noResultsViewModel.entries.map((entry) => entry.type)).toContain('no-results');
    expect(noResultsViewModel.entries.map((entry) => entry.label)).toContain('No results');
  });

  it('matches Cyrillic and Chinese segment names without transliteration', () => {
    const store = loadFreshStore();

    store.init(createConfig());

    const cyrillicViewModel = store.getSelectorViewModel('ЖУРНАЛ');
    const chineseViewModel = store.getSelectorViewModel('中文');
    const transliteratedViewModel = store.getSelectorViewModel('zhongwen');

    expect(cyrillicViewModel.entries.map((entry) => entry.label)).toEqual(expect.arrayContaining([
      'unicode журнал 中文',
    ]));
    expect(chineseViewModel.entries.map((entry) => entry.label)).toEqual(expect.arrayContaining([
      'unicode журнал 中文',
    ]));
    expect(transliteratedViewModel.entries.map((entry) => entry.type)).toContain('no-results');
    expect(transliteratedViewModel.entries.map((entry) => entry.label)).toContain('No results');
  });
});
