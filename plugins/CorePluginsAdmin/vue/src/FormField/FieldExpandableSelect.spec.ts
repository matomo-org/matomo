/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

vi.mock('CoreHome', () => ({
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
}));

import FieldExpandableSelect from './FieldExpandableSelect.vue';

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
    attachTo: document.body,
    props: {
      availableOptions,
      ...props,
    },
  });
}

// the option list is teleported to the body so that a scrolling ancestor cannot clip it, which
// puts it outside the mounted wrapper
function findInBody(selector: string): HTMLElement {
  const element = document.body.querySelector(selector);

  if (!element) {
    throw new Error(`no element matching ${selector}`);
  }

  return element as HTMLElement;
}

afterEach(() => {
  document.body.innerHTML = '';
});

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

  describe('viewport fitting', () => {
    function mockRect(element: Element, top: number, height = 0) {
      Object.defineProperty(element, 'getBoundingClientRect', {
        value: () => ({
          top, bottom: top + height, left: 0, right: 0, width: 0, height,
        }),
      });
    }

    // The list is positioned from the field, so the fixtures describe a layout that could exist:
    // a field of a real height, and a search box of a known height above the options. The 8px
    // between the field and the list is the gap the component leaves.
    const FIELD_HEIGHT = 30;
    const SEARCH_HEIGHT = 50;

    function layOut(wrapper: ReturnType<typeof mountSelect>, fieldTop: number) {
      mockRect(wrapper.find('.select-wrapper').element, fieldTop, FIELD_HEIGHT);
      mockRect(findInBody('.expandableList'), 0);
      mockRect(findInBody('.firstLevel'), SEARCH_HEIGHT);
    }

    it('clamps the list to the space below the field when enough remains', async () => {
      const wrapper = mountSelect();
      vi.stubGlobal('innerHeight', 800);
      layOut(wrapper, 300);

      await wrapper.find('.select-wrapper').trigger('click');
      await wrapper.vm.$nextTick();

      // 800 - (300 + 30) - 8 - 50 - 16
      expect(findInBody('.expandableList').classList).not.toContain('expandableSelector__list--above');
      expect(findInBody('.firstLevel').style.maxHeight).toBe('396px');
    });

    it('opens above the field when the space below is too small', async () => {
      const wrapper = mountSelect();
      vi.stubGlobal('innerHeight', 400);
      // 4px below and 218px above, so it flips and takes the room above
      layOut(wrapper, 292);

      await wrapper.find('.select-wrapper').trigger('click');
      await wrapper.vm.$nextTick();

      expect(findInBody('.expandableList').classList).toContain('expandableSelector__list--above');
      expect(findInBody('.firstLevel').style.maxHeight).toBe('218px');
    });

    it('keeps a usable minimum below when neither side has room', async () => {
      const wrapper = mountSelect();
      vi.stubGlobal('innerHeight', 300);
      // 96px below and 26px above, so neither side reaches the 150px minimum and below wins
      layOut(wrapper, 100);

      await wrapper.find('.select-wrapper').trigger('click');
      await wrapper.vm.$nextTick();

      expect(findInBody('.expandableList').classList).not.toContain('expandableSelector__list--above');
      expect(findInBody('.firstLevel').style.maxHeight).toBe('150px');
    });
  });
});
