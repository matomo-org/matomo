/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount, VueWrapper } from '@vue/test-utils';

// Pulled in dynamically: a vi.mock() factory is hoisted above the file's own imports.
vi.mock('CoreHome', async () => (await import('../testCoreHomeMock')).coreHomeMock());

/* eslint-disable import/first */
import SortMenu from './SortMenu.vue';
import {
  SORT_ALPHA,
  SORT_DEVELOPER,
  SORT_LAST_UPDATED,
  SORT_NEWEST,
} from '../PluginGrid/pluginGrouping';
import { translateStub } from '../testCoreHomeMock';

/** Attached to the document, so focus and the document-level listeners behave as in a page. */
function mountMenu(modelValue: string = SORT_LAST_UPDATED) {
  return mount(SortMenu, {
    props: { modelValue },
    attachTo: document.body,
    global: { mocks: { translate: translateStub } },
  });
}

const itemLabels = (wrapper: VueWrapper) => wrapper.findAll('.sortMenu__item').map((i) => i.text());

async function open(wrapper: VueWrapper) {
  await wrapper.find('.sortMenu__trigger').trigger('click');
  return wrapper;
}

describe('Marketplace/SortMenu', () => {
  afterEach(() => {
    document.body.innerHTML = '';
  });

  describe('the trigger', () => {
    it('names the sort that is in force', () => {
      expect(mountMenu(SORT_ALPHA).find('.sortMenu__trigger').text())
        .toBe('Marketplace_SortBy: Marketplace_SortByAlpha');
    });

    it('falls back to the first option for a sort it does not know', () => {
      expect(mountMenu('popularity').find('.sortMenu__trigger').text())
        .toBe('Marketplace_SortBy: Marketplace_SortByLastUpdated');
    });

    it('starts collapsed, and says so', () => {
      const wrapper = mountMenu();
      expect(wrapper.find('.sortMenu__menu').exists()).toBe(false);
      expect(wrapper.find('.sortMenu__trigger').attributes('aria-expanded')).toBe('false');
    });

    it('opens and closes on click', async () => {
      const wrapper = await open(mountMenu());
      expect(wrapper.find('.sortMenu__trigger').attributes('aria-expanded')).toBe('true');
      expect(itemLabels(wrapper)).toEqual([
        'Marketplace_SortByLastUpdated',
        'Marketplace_SortByNewest',
        'Marketplace_SortByAlpha',
        'Marketplace_Developer',
      ]);

      await wrapper.find('.sortMenu__trigger').trigger('click');
      expect(wrapper.find('.sortMenu__menu').exists()).toBe(false);
    });
  });

  describe('choosing a sort', () => {
    it('emits the option that was clicked and closes the menu', async () => {
      const wrapper = await open(mountMenu());
      await wrapper.findAll('.sortMenu__item')[1].trigger('click');

      expect(wrapper.emitted('update:modelValue')).toEqual([[SORT_NEWEST]]);
      expect(wrapper.find('.sortMenu__menu').exists()).toBe(false);
    });

    it('marks only the sort in force, and checks it', async () => {
      const wrapper = await open(mountMenu(SORT_DEVELOPER));
      const current = wrapper.findAll('.sortMenu__item')
        .filter((item) => item.attributes('aria-current') === 'true');

      expect(current.map((item) => item.text())).toEqual(['Marketplace_Developer']);
      expect(wrapper.findAll('.sortMenu__check')).toHaveLength(1);
    });

    it('emits even for the sort already in force, so the menu closes the same way', async () => {
      const wrapper = await open(mountMenu(SORT_LAST_UPDATED));
      await wrapper.findAll('.sortMenu__item')[0].trigger('click');

      expect(wrapper.emitted('update:modelValue')).toEqual([[SORT_LAST_UPDATED]]);
    });
  });

  describe('dismissing the menu', () => {
    it('closes on a click outside itself', async () => {
      const wrapper = await open(mountMenu());

      document.dispatchEvent(new MouseEvent('mousedown'));
      await wrapper.vm.$nextTick();

      expect(wrapper.find('.sortMenu__menu').exists()).toBe(false);
    });

    it('stays open for a click inside itself', async () => {
      const wrapper = await open(mountMenu());

      const inside = new MouseEvent('mousedown', { bubbles: true });
      wrapper.find('.sortMenu__menu').element.dispatchEvent(inside);
      await wrapper.vm.$nextTick();

      expect(wrapper.find('.sortMenu__menu').exists()).toBe(true);
    });

    it('closes on Escape and hands focus back to the trigger', async () => {
      const wrapper = await open(mountMenu());

      document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
      await wrapper.vm.$nextTick();

      expect(wrapper.find('.sortMenu__menu').exists()).toBe(false);
      expect(document.activeElement).toBe(wrapper.find('.sortMenu__trigger').element);
    });

    it('ignores a key that is not Escape', async () => {
      const wrapper = await open(mountMenu());

      document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter' }));
      await wrapper.vm.$nextTick();

      expect(wrapper.find('.sortMenu__menu').exists()).toBe(true);
    });

    it('drops its document listeners when it goes away', async () => {
      const removeEventListener = vi.spyOn(document, 'removeEventListener');
      const wrapper = await open(mountMenu());

      wrapper.unmount();

      const events = removeEventListener.mock.calls.map(([event]) => event);
      expect(events).toContain('mousedown');
      expect(events).toContain('keydown');
      removeEventListener.mockRestore();
    });
  });
});
