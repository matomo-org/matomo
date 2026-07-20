/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

jest.mock('CoreHome', () => ({
  Matomo: {
    helper: {
      normalize: (value: string) => value
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLowerCase(),
    },
  },
  FocusAnywhereButHere: {},
  FocusIf: {},
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const FieldExpandableSelect = require('./FieldExpandableSelect.vue').default;

const availableOptions = [
  {
    group: 'Fruit',
    values: [
      { key: 1, value: 'Apple' },
      { key: 2, value: 'Banana' },
    ],
  },
  {
    group: 'Vegetables',
    values: [
      { key: 3, value: 'Carrot' },
      { key: 4, value: 'Potato' },
    ],
  },
];

function mountSelect(props = {}) {
  return mount(FieldExpandableSelect as any, {
    props: {
      availableOptions,
      ...props,
    },
  });
}

describe('CorePluginsAdmin/FormField/FieldExpandableSelect', () => {
  it('defaults searchOnGroup to false', () => {
    const wrapper = mountSelect();
    expect((wrapper.vm as any).searchOnGroup).toBe(false);
  });

  describe('visibleChildren', () => {
    it('returns only children whose value matches the search term', async () => {
      const wrapper = mountSelect();
      await wrapper.setData({ searchTerm: 'apple' });

      const visible = (wrapper.vm as any).visibleChildren(availableOptions[0]);
      expect(visible.map((v: any) => v.value)).toEqual(['Apple']);
    });

    it('does not reveal children by a group match when searchOnGroup is false', async () => {
      const wrapper = mountSelect({ searchOnGroup: false });
      // "Fruit" matches the group name but none of its values
      await wrapper.setData({ searchTerm: 'fruit' });

      const visible = (wrapper.vm as any).visibleChildren(availableOptions[0]);
      expect(visible).toEqual([]);
    });

    it('reveals all children when the group matches and searchOnGroup is true', async () => {
      const wrapper = mountSelect({ searchOnGroup: true });
      await wrapper.setData({ searchTerm: 'fruit' });

      const visible = (wrapper.vm as any).visibleChildren(availableOptions[0]);
      expect(visible.map((v: any) => v.value)).toEqual(['Apple', 'Banana']);
    });

    it('still filters by value when the group does not match and searchOnGroup is true', async () => {
      const wrapper = mountSelect({ searchOnGroup: true });
      await wrapper.setData({ searchTerm: 'carrot' });

      // group "Fruit" does not match, so it falls back to value filtering
      expect((wrapper.vm as any).visibleChildren(availableOptions[0])).toEqual([]);
      expect(
        (wrapper.vm as any).visibleChildren(availableOptions[1]).map((v: any) => v.value),
      ).toEqual(['Carrot']);
    });

    it('matches the group case-insensitively when searchOnGroup is true', async () => {
      const wrapper = mountSelect({ searchOnGroup: true });
      await wrapper.setData({ searchTerm: 'VEG' });

      const visible = (wrapper.vm as any).visibleChildren(availableOptions[1]);
      expect(visible.map((v: any) => v.value)).toEqual(['Carrot', 'Potato']);
    });
  });
});
