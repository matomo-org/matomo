/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import { defineComponent } from 'vue';

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
        isStarred: false,
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

function createViewModelForSearch(searchValue = '') {
  const viewModel = createViewModel();

  if (searchValue === 'ca') {
    return {
      ...viewModel,
      entries: viewModel.entries.filter((entry) => entry.label === 'Café Visits'),
    };
  }

  if (searchValue) {
    return {
      ...viewModel,
      entries: [],
    };
  }

  return {
    ...viewModel,
    entries: [
      ...viewModel.entries,
      {
        key: 'segment-2',
        type: 'segment',
        classes: '',
        idsegment: '2',
        definition: 'deviceType==mobile',
        label: 'Mobile Visits',
        tooltip: 'Mobile Visits',
        showStarButton: true,
        isStarred: false,
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
  };
}

function createAnonymousViewModel() {
  return {
    ...createViewModel(),
    authorizedToCreateSegments: false,
    entries: createViewModel().entries.map((entry) => ({
      ...entry,
      showStarButton: false,
    })),
    isUserAnonymous: true,
  };
}

function createLoggedInReadOnlyViewModel() {
  return {
    ...createViewModel(),
    authorizedToCreateSegments: false,
    isUserAnonymous: false,
  };
}

const mockStore: MockStore = {
  state: {
    value: {
      isInitialized: true,
    },
  },
  getSelectorViewModel: jest.fn((searchValue = '') => createViewModelForSearch(searchValue)),
  notifyChange: jest.fn(),
  onStarChange: jest.fn(() => jest.fn()),
  toggleStarredSegmentById: jest.fn(),
};

jest.mock('CoreHome', () => ({
  SearchInput: defineComponent({
    name: 'SearchInput',
    props: {
      modelValue: {
        type: String,
        required: true,
      },
      showClear: {
        type: Boolean,
        default: false,
      },
    },
    emits: ['update:modelValue'],
    template: `
      <div class="searchInputContainer">
        <input
          class="searchInputField"
          :value="modelValue"
          @input="$emit('update:modelValue', $event.target.value)"
        />
        <button
          v-if="showClear && modelValue"
          type="button"
          class="searchInputClear"
          @click="$emit('update:modelValue', '')"
        />
      </div>
    `,
  }),
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
    mockStore.getSelectorViewModel.mockImplementation((searchValue = '') => createViewModelForSearch(searchValue));
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
    expect(wrapper.find('.manage_segment_btn').exists()).toBe(true);
    expect(wrapper.find('.starSegment svg').exists()).toBe(true);
    expect(wrapper.find('.compareSegment svg').exists()).toBe(true);
    expect(mockStore.getSelectorViewModel).toHaveBeenCalledWith('');
  });

  it('passes the starred state through to the star icon fill', () => {
    const starredViewModel = createViewModel();
    starredViewModel.entries[0].isStarred = true;
    mockStore.getSelectorViewModel.mockImplementation(() => starredViewModel);

    const { wrapper } = mountComponent();

    expect(wrapper.find('.starSegment path').attributes('fill')).toBe('currentColor');
  });

  it('passes the compare state through to the compare icon fill', async () => {
    const activeViewModel = createViewModel();
    activeViewModel.entries[0].compareState = 'active';
    mockStore.getSelectorViewModel.mockImplementation(() => activeViewModel);

    const { wrapper } = mountComponent();

    expect(wrapper.find('.compareSegment').attributes('data-state')).toBe('active');
    expect(wrapper.findAll('.compareSegment path').every((path) => path.attributes('fill') === 'currentColor')).toBe(true);
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

    await wrapper.find('input.searchInputField').setValue('ca');

    expect((wrapper.vm as PlainObject).searchInput).toBe('ca');
    expect(mockStore.notifyChange).not.toHaveBeenCalled();

    jest.advanceTimersByTime(500);
    await wrapper.vm.$nextTick();

    expect(mockStore.notifyChange).toHaveBeenCalledTimes(1);
    expect((wrapper.vm as PlainObject).debouncedSearchInput).toBe('ca');
    expect(mockStore.getSelectorViewModel).toHaveBeenLastCalledWith('ca');
    expect(wrapper.findAll('.segname')).toHaveLength(1);
    expect(wrapper.find('.segname').text()).toBe('Café Visits');

    await wrapper.find('.searchInputClear').trigger('click');
    await wrapper.vm.$nextTick();

    expect((wrapper.vm as PlainObject).searchInput).toBe('');
    expect((wrapper.vm as PlainObject).debouncedSearchInput).toBe('');
    expect(mockStore.notifyChange).toHaveBeenCalledTimes(2);
    expect(mockStore.getSelectorViewModel).toHaveBeenLastCalledWith('');
    expect(wrapper.findAll('.segname')).toHaveLength(2);
    expect(wrapper.findAll('.segname').map((node) => node.text())).toEqual(['Café Visits', 'Mobile Visits']);
  });

  it('dispatches an add-segment event when the add button is clicked', async () => {
    const { wrapper, panelContainer } = mountComponent();
    const openAddListener = jest.fn();
    panelContainer.addEventListener('SegmentEditor:open-add-segment', openAddListener);

    await wrapper.find('.add_new_segment').trigger('click');

    expect(openAddListener).toHaveBeenCalledTimes(1);
  });

  it('renders a button-styled sign in link for anonymous users', () => {
    mockStore.getSelectorViewModel.mockImplementation(() => createAnonymousViewModel());

    const { wrapper } = mountComponent();

    const signInLink = wrapper.find('.sign_in_segment_btn');
    expect(signInLink.exists()).toBe(true);
    expect(signInLink.attributes('href')).toBe('index.php?module=Login');
    expect(signInLink.text()).toBe('Login_LogIn');
    expect(wrapper.find('.add_new_segment').exists()).toBe(false);
    expect(wrapper.find('.manage_segment_btn').exists()).toBe(false);
    expect(wrapper.find('.starSegment').exists()).toBe(false);
  });

  it('shows no footer actions or sign in prompt for logged-in users without write access', () => {
    mockStore.getSelectorViewModel.mockImplementation(() => createLoggedInReadOnlyViewModel());

    const { wrapper } = mountComponent();

    expect(wrapper.find('.add_new_segment').exists()).toBe(false);
    expect(wrapper.find('.manage_segment_btn').exists()).toBe(false);
    expect(wrapper.find('.sign_in_segment_btn').exists()).toBe(false);
    expect(wrapper.find('.youMustBeLoggedIn').exists()).toBe(false);
    expect(wrapper.find('.starSegment').exists()).toBe(true);
  });

  it('dispatches a toggle-panel event when the title is clicked', async () => {
    const { wrapper, panelContainer } = mountComponent();
    const togglePanelListener = jest.fn();
    panelContainer.addEventListener('SegmentEditor:toggle-panel', togglePanelListener);

    await wrapper.find('a.title').trigger('click');

    expect(togglePanelListener).toHaveBeenCalledTimes(1);
  });
});
