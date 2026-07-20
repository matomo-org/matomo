/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

const mockStore = {
  toggleStarredSegmentById: jest.fn(),
};

jest.mock('../SegmentSelector/SegmentSelector.store', () => ({
  __esModule: true,
  default: mockStore,
}));

// eslint-disable-next-line @typescript-eslint/no-var-requires
const StarButton = require('./StarButton.vue').default;

function createSegment(overrides: Record<string, unknown> = {}) {
  return {
    key: 'segment-1',
    type: 'segment',
    idsegment: '1',
    label: 'My segment',
    tooltip: 'My segment',
    isStarred: false,
    starTitle: 'Star segment',
    starState: '',
    ...overrides,
  };
}

describe('SegmentEditor/StarButton.vue', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('renders the button with the segment metadata and an unfilled star icon', () => {
    const wrapper = mount(StarButton, {
      props: { segment: createSegment() },
    });

    const button = wrapper.find('button');
    expect(button.classes()).toContain('starSegment');
    expect(button.attributes('data-star')).toBe('1');
    expect(button.attributes('title')).toBe('Star segment');
    expect(button.attributes('data-state')).toBe('');
    expect(wrapper.find('path').attributes('fill')).toBe('none');
  });

  it('renders a filled star icon when the segment is starred', () => {
    const wrapper = mount(StarButton, {
      props: { segment: createSegment({ isStarred: true }) },
    });

    expect(wrapper.find('path').attributes('fill')).toBe('currentColor');
  });

  it('toggles the starred state through the store when clicked', async () => {
    const wrapper = mount(StarButton, {
      props: { segment: createSegment({ idsegment: '42' }) },
    });

    await wrapper.find('button').trigger('click');

    expect(mockStore.toggleStarredSegmentById).toHaveBeenCalledTimes(1);
    expect(mockStore.toggleStarredSegmentById).toHaveBeenCalledWith('42');
  });

  it('does not call the store when the star state is disabled', async () => {
    const wrapper = mount(StarButton, {
      props: { segment: createSegment({ starState: 'disabled' }) },
    });

    await wrapper.find('button').trigger('click');

    expect(mockStore.toggleStarredSegmentById).not.toHaveBeenCalled();
  });

  it('does not call the store when the segment has no id', async () => {
    const wrapper = mount(StarButton, {
      props: { segment: createSegment({ idsegment: '' }) },
    });

    await wrapper.find('button').trigger('click');

    expect(mockStore.toggleStarredSegmentById).not.toHaveBeenCalled();
  });
});
