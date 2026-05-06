/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

type PlainObject = Record<string, unknown>;

type MockStore = {
  state: {
    value: {
      isInitialized: boolean;
    };
  };
  getSelectorViewModel: jest.Mock;
  notifyChange: jest.Mock;
  onStarChange: jest.Mock;
  toggleStarredSegmentById: jest.Mock;
};

function createViewModel() {
  return {
    authorizedToCreateSegments: true,
    currentSegmentTitle: 'Café Visits',
    currentSegmentTooltip: 'Currently selected: Café Visits',
    currentSegmentValue: 'countryCode==nz',
    entries: [
      {
        key: 'segment-1',
        type: 'segment',
        classes: 'segmentSelected',
        idsegment: '1',
        definition: 'countryCode==nz',
        label: 'Café Visits',
        tooltip: 'Café Visits',
        showStarButton: true,
        starTitle: 'Star segment',
        starState: '',
        showEditButton: true,
        editTitle: 'Edit segment',
        editState: '',
        showCompareButton: true,
        compareButtonClass: 'segmentAction compareSegment',
        compareTitle: 'Compare segment',
        compareState: '',
      },
    ],
    isExpanded: true,
    isUserAnonymous: false,
    loginUrl: 'index.php?module=Login',
    manageSegmentsUrl: 'index.php?module=SegmentEditor&action=index',
  };
}

const mockStore: MockStore = {
  state: {
    value: {
      isInitialized: true,
    },
  },
  getSelectorViewModel: jest.fn(() => createViewModel()),
  notifyChange: jest.fn(),
  onStarChange: jest.fn(() => jest.fn()),
  toggleStarredSegmentById: jest.fn(),
};

jest.mock('CoreHome', () => ({
  translate: (key: string) => key,
}), { virtual: true });

jest.mock('./SegmentSelector.store', () => ({
  __esModule: true,
  default: mockStore,
}));

// eslint-disable-next-line @typescript-eslint/no-var-requires
const SegmentSelector = require('./SegmentSelector.vue').default;

function mountComponent() {
  const panelContainer = document.createElement('div');
  panelContainer.className = 'segmentListContainer';
  const mountTarget = document.createElement('div');
  panelContainer.appendChild(mountTarget);
  document.body.appendChild(panelContainer);

  const wrapper = mount(SegmentSelector as PlainObject, {
    attachTo: mountTarget,
  });

  return {
    wrapper,
    panelContainer,
  };
}

describe('SegmentEditor/SegmentSelector.vue', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    mockStore.state.value.isInitialized = true;
    mockStore.getSelectorViewModel.mockImplementation(() => createViewModel());
    mockStore.onStarChange.mockImplementation(() => jest.fn());
  });

  afterEach(() => {
    jest.useRealTimers();
    document.body.innerHTML = '';
  });

  it('renders the current segment title and segment entries from the store view model', () => {
    const { wrapper } = mountComponent();

    expect(wrapper.find('.segmentationTitle').text()).toBe('Café Visits');
    expect(wrapper.find('.segname').text()).toBe('Café Visits');
    expect(wrapper.find('.add_new_segment').exists()).toBe(true);
    expect(mockStore.getSelectorViewModel).toHaveBeenCalledWith('');
  });

  it('dispatches a segment selection event when a segment is clicked', async () => {
    const { wrapper, panelContainer } = mountComponent();
    const segmentSelectListener = jest.fn();
    panelContainer.addEventListener('SegmentEditor:select-segment', segmentSelectListener);

    await wrapper.find('.segname').trigger('click');

    expect(segmentSelectListener).toHaveBeenCalledTimes(1);
    expect((segmentSelectListener.mock.calls[0][0] as CustomEvent).detail).toEqual({
      definition: 'countryCode==nz',
    });
  });

  it('debounces search input changes and clears the filter cleanly', async () => {
    jest.useFakeTimers();
    const { wrapper } = mountComponent();

    await wrapper.find('input.segmentFilter').setValue('ca');

    expect((wrapper.vm as PlainObject).searchInput).toBe('ca');
    expect(mockStore.notifyChange).not.toHaveBeenCalled();

    jest.advanceTimersByTime(500);

    expect(mockStore.notifyChange).toHaveBeenCalledTimes(1);

    ((wrapper.vm as unknown) as { clearSearch: () => void }).clearSearch();

    expect((wrapper.vm as PlainObject).searchInput).toBe('');
    expect(mockStore.notifyChange).toHaveBeenCalledTimes(2);
  });

  it('dispatches an add-segment event when the add button is clicked', async () => {
    const { wrapper, panelContainer } = mountComponent();
    const openAddListener = jest.fn();
    panelContainer.addEventListener('SegmentEditor:open-add-segment', openAddListener);

    await wrapper.find('.add_new_segment').trigger('click');

    expect(openAddListener).toHaveBeenCalledTimes(1);
  });

  it('dispatches a toggle-panel event when the title is clicked', async () => {
    const { wrapper, panelContainer } = mountComponent();
    const togglePanelListener = jest.fn();
    panelContainer.addEventListener('SegmentEditor:toggle-panel', togglePanelListener);

    await wrapper.find('a.title').trigger('click');

    expect(togglePanelListener).toHaveBeenCalledTimes(1);
  });
});
