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

const mounted: { unmount: () => void }[] = [];

function mountSelect(props = {}) {
  const wrapper = mount(FieldExpandableSelect as any, {
    attachTo: document.body,
    props: {
      availableOptions,
      ...props,
    },
  });

  mounted.push(wrapper);

  return wrapper;
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
  // unmount rather than emptying the body: an open select holds window listeners and an observer,
  // and wiping the markup would leave those attached to elements no test can reach any more
  mounted.splice(0).forEach((wrapper) => wrapper.unmount());
  document.body.innerHTML = '';
  // vitest.config.ts does not set unstubGlobals, so a stubbed innerHeight would otherwise stay in
  // force for every later describe block and make them order-dependent
  vi.unstubAllGlobals();
});

describe('CorePluginsAdmin/FormField/FieldExpandableSelect', () => {
  it('carries the field name onto the teleported list', () => {
    mountSelect({ name: 'selectexpand' });

    expect(findInBody('.expandableSelector__list').dataset.name).toBe('selectexpand');
  });

  it('does not exempt the list from a modal focus trap when the field is not in a modal', async () => {
    const wrapper = mountSelect();
    await wrapper.find('.select-wrapper').trigger('click');

    // marking every list would let one hold focus over an unrelated modal
    expect(findInBody('.expandableSelector__list').hasAttribute('data-matomo-modal-escapee'))
      .toBe(false);
  });

  it('exempts the list from the focus trap when its field is inside a modal', async () => {
    const modal = document.createElement('div');
    modal.className = 'modal';
    document.body.appendChild(modal);

    const wrapper = mount(FieldExpandableSelect as any, {
      attachTo: modal,
      props: { availableOptions },
    });
    mounted.push(wrapper);

    await wrapper.find('.select-wrapper').trigger('click');

    expect(findInBody('.expandableSelector__list').hasAttribute('data-matomo-modal-escapee'))
      .toBe(true);
  });

  it('names the modal it belongs to, so a sibling modal does not share the exemption', async () => {
    const ids = [];

    for (let i = 0; i < 2; i += 1) {
      const modal = document.createElement('div');
      modal.className = 'modal';
      document.body.appendChild(modal);

      const wrapper = mount(FieldExpandableSelect as any, {
        attachTo: modal,
        props: { availableOptions },
      });
      mounted.push(wrapper);

      // eslint-disable-next-line no-await-in-loop
      await wrapper.find('.select-wrapper').trigger('click');
      ids.push(modal.getAttribute('data-matomo-modal-id'));
    }

    expect(ids[0]).toBeTruthy();
    expect(ids[1]).toBeTruthy();
    // a shared or absent token would let a list escape the trap of a modal stacked above its own
    expect(ids[0]).not.toBe(ids[1]);
  });

  /**
   * The two halves of this contract sit in different files, joined only by matching string
   * literals: this component writes data-matomo-modal-id and data-matomo-modal-escapee, and
   * materialize-bc.js reads them. Either side's own spec stays green if the other is renamed,
   * so drive the markup this component really emits through the real shim.
   */
  it('is let through the real shim by the modal it tags, and trapped by any other', async () => {
    const trapped: unknown[] = [];
    const ready: Array<() => void> = [];
    const M = {
      initializeJqueryWrapper: () => {},
      Tabs: {},
      Modal: {
        prototype: {
          _handleFocus(this: unknown, event: { target: unknown }) { trapped.push(event.target); },
        },
      },
    };
    const jq = (() => ({ ready: (cb: () => void) => { ready.push(cb); } })) as any;
    jq.fn = {};
    (globalThis as any).M = M;
    (globalThis as any).$ = jq;

    await import('../../../../CoreHome/javascripts/materialize-bc.js');
    ready.forEach((cb) => cb());

    const modal = document.createElement('div');
    modal.className = 'modal';
    document.body.appendChild(modal);

    const wrapper = mount(FieldExpandableSelect as any, {
      attachTo: modal,
      props: { availableOptions },
    });
    mounted.push(wrapper);
    await wrapper.find('.select-wrapper').trigger('click');

    const search = findInBody('.expandableSelector__list .expandableSearch');

    M.Modal.prototype._handleFocus.call({ el: modal }, { target: search });
    expect(trapped.length).toBe(0);

    const other = document.createElement('div');
    other.setAttribute('data-matomo-modal-id', 'someone-else');
    M.Modal.prototype._handleFocus.call({ el: other }, { target: search });
    expect(trapped.length).toBe(1);

    delete (globalThis as any).M;
    delete (globalThis as any).$;
  });

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
    // configurable so a test can lay the same elements out twice, which is what re-running the
    // fit against a moved field needs
    function mockRect(element: Element, top: number, height = 0) {
      Object.defineProperty(element, 'getBoundingClientRect', {
        configurable: true,
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
      expect(findInBody('.expandableList').style.top).toBe('338px');
      expect(findInBody('.expandableList').style.bottom).toBe('');
      expect(findInBody('.firstLevel').style.maxHeight).toBe('396px');
    });

    it('opens above the field when the space below is too small', async () => {
      const wrapper = mountSelect();
      vi.stubGlobal('innerHeight', 400);
      // 4px below and 218px above, so it flips and takes the room above
      layOut(wrapper, 292);

      await wrapper.find('.select-wrapper').trigger('click');
      await wrapper.vm.$nextTick();

      expect(findInBody('.expandableList').style.bottom).toBe('116px');
      expect(findInBody('.expandableList').style.top).toBe('');
      expect(findInBody('.firstLevel').style.maxHeight).toBe('218px');
    });

    it('keeps a usable minimum below when neither side has room', async () => {
      const wrapper = mountSelect();
      vi.stubGlobal('innerHeight', 300);
      // 96px below and 26px above, so neither side reaches the 150px minimum and below wins
      layOut(wrapper, 100);

      await wrapper.find('.select-wrapper').trigger('click');
      await wrapper.vm.$nextTick();

      expect(findInBody('.expandableList').style.top).toBe('138px');
      expect(findInBody('.expandableList').style.bottom).toBe('');
      expect(findInBody('.firstLevel').style.maxHeight).toBe('150px');
    });

    it('opens above when the room there is more, even below the usable minimum', async () => {
      const wrapper = mountSelect();
      vi.stubGlobal('innerHeight', 300);
      // -4px below and 126px above: neither reaches 150, and above wins. Measured in a browser at
      // this exact layout, opening above leaves the full 150px of options on screen and clips 8px
      // of the search box; opening below would leave 12px of options. The options sit at the
      // bottom of the dropdown, so the side with less room clips the search box, not the list.
      layOut(wrapper, 200);

      await wrapper.find('.select-wrapper').trigger('click');
      await wrapper.vm.$nextTick();

      expect(findInBody('.expandableList').style.bottom).toBe('108px');
      expect(findInBody('.expandableList').style.top).toBe('');
      expect(findInBody('.firstLevel').style.maxHeight).toBe('150px');
    });

    it('still opens above when the room there holds the list within the gutter', async () => {
      const wrapper = mountSelect();
      vi.stubGlobal('innerHeight', 378);
      // 60px below and 140px above. Anchored by its bottom edge, a 150px list puts its top edge at
      // 140 - (150 - 16) = 6px, so it is fully on screen and only eats into the 16px gutter - the
      // same trade the below-the-field branch already makes. Testing spaceAbove against the bare
      // 150 would send this below, where 60px of room clips it far worse.
      layOut(wrapper, 214);

      await wrapper.find('.select-wrapper').trigger('click');
      await wrapper.vm.$nextTick();

      expect(findInBody('.expandableList').style.bottom).toBe('172px');
      expect(findInBody('.expandableList').style.top).toBe('');
      expect(findInBody('.firstLevel').style.maxHeight).toBe('150px');
    });

    it('returns the list below the field once it scrolls back into open space', async () => {
      const wrapper = mountSelect();
      vi.stubGlobal('innerHeight', 400);
      layOut(wrapper, 292);

      await wrapper.find('.select-wrapper').trigger('click');
      await wrapper.vm.$nextTick();
      expect(findInBody('.expandableList').style.bottom).toBe('116px');

      // the field moves back into open space while the list is still open; the branch that picked
      // "above" has to be undone, or the list stays stranded above a field that no longer needs it
      vi.stubGlobal('innerHeight', 800);
      layOut(wrapper, 300);
      (wrapper.vm as any).fitOptionsList();
      await wrapper.vm.$nextTick();

      expect(findInBody('.expandableList').style.top).toBe('338px');
      expect(findInBody('.expandableList').style.bottom).toBe('');
    });
  });

  describe('closing after a press that started inside the list', () => {
    it('clears the press marker on mouseup so a later escape still closes', async () => {
      const wrapper = mountSelect();
      await wrapper.find('.select-wrapper').trigger('click');
      await wrapper.vm.$nextTick();

      // a press inside the teleported list must not read as a click outside the field
      findInBody('.expandableList').dispatchEvent(
        new MouseEvent('mousedown', { bubbles: true }),
      );
      expect((wrapper.vm as any).isMouseDownInsideList).toBe(true);

      // releasing on the field is a release the directive owns, so it never calls blur() and
      // nothing there clears the marker; releasing the press has to
      window.dispatchEvent(new MouseEvent('mouseup', { bubbles: true }));
      expect((wrapper.vm as any).isMouseDownInsideList).toBe(false);

      // escape reaches blur() without a mousedown of its own, and must not be swallowed
      (wrapper.vm as any).onBlur();
      expect((wrapper.vm as any).showSelect).toBe(false);
    });
  });

  describe('holding the list inside the viewport', () => {
    function placeField(wrapper: ReturnType<typeof mountSelect>, left: number, listWidth: number) {
      Object.defineProperty(document.documentElement, 'clientWidth', {
        value: 1440, configurable: true,
      });
      Object.defineProperty(wrapper.find('.select-wrapper').element, 'getBoundingClientRect', {
        value: () => ({ top: 100, bottom: 130, left, right: left, width: 0, height: 30 }),
      });
      Object.defineProperty(findInBody('.expandableList'), 'offsetWidth', {
        value: listWidth, configurable: true,
      });
    }

    it('follows the field when the list fits beside it', async () => {
      const wrapper = mountSelect();
      placeField(wrapper, 200, 300);

      await wrapper.find('.select-wrapper').trigger('click');
      await wrapper.vm.$nextTick();

      expect(findInBody('.expandableList').style.left).toBe('200px');
    });

    it('holds the list off the right edge when the field is close to it', async () => {
      const wrapper = mountSelect();
      placeField(wrapper, 1300, 300);

      await wrapper.find('.select-wrapper').trigger('click');
      await wrapper.vm.$nextTick();

      // 1440 - 16 - 300; aligning with the field would run 176px past the edge, which a list
      // positioned against the viewport cannot be scrolled to
      expect(findInBody('.expandableList').style.left).toBe('1124px');
    });
  });

  describe('closing on a press outside', () => {
    function pressOn(element: HTMLElement) {
      element.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
    }

    async function openSelect() {
      const wrapper = mountSelect();

      await wrapper.find('.select-wrapper').trigger('click');
      await wrapper.vm.$nextTick();

      return wrapper;
    }

    it('stays open when the press started inside the list', async () => {
      const wrapper = await openSelect();

      pressOn(findInBody('.expandableList'));
      (wrapper.vm as any).onBlur();
      await wrapper.vm.$nextTick();

      expect(findInBody('.expandableList').style.display).not.toBe('none');
    });

    it('closes on a press outside once the pointer has been used in the list', async () => {
      const wrapper = await openSelect();

      // a scrollbar drag inside the list: the directive skips its outside-click handler for that,
      // so nothing tells the component the press it recorded is spent
      pressOn(findInBody('.expandableList'));
      pressOn(document.body);
      (wrapper.vm as any).onBlur();
      await wrapper.vm.$nextTick();

      expect(findInBody('.expandableList').style.display).toBe('none');
    });
  });
});
