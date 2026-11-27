/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

vi.mock('CoreHome', () => ({
  AjaxHelper: { post: vi.fn() },
  ContentBlock: { template: '<div><slot /></div>' },
  NotificationsStore: { show: vi.fn(), scrollToNotification: vi.fn() },
  SiteSelector: {
    props: ['modelValue'],
    emits: ['update:modelValue'],
    template: '<div />',
  },
  translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
}));

vi.mock('CorePluginsAdmin', () => ({
  SaveButton: {
    emits: ['confirm'],
    template: '<button class="saveButton" @click="$emit(\'confirm\')" />',
  },
}));

import { mount, flushPromises } from '@vue/test-utils';
import { AjaxHelper, SiteSelector } from 'CoreHome';
import TrackingFailuresNotificationSettings from './TrackingFailuresNotificationSettings.vue';

const block = '.trackingFailuresNotificationSettings';

function createWrapper(props = {}) {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return mount(TrackingFailuresNotificationSettings as any, {
    props: {
      notificationsEnabled: true,
      excludedSites: [{ id: 1, name: 'Site 1' }],
      ...props,
    },
    global: {
      mocks: {
        translate: (key: string, ...args: string[]) => [key, ...args].join('|'),
      },
    },
  });
}

async function selectSite(wrapper: ReturnType<typeof createWrapper>, site: unknown) {
  wrapper.findComponent(SiteSelector).vm.$emit('update:modelValue', site);
  await wrapper.vm.$nextTick();
}

function siteNames(wrapper: ReturnType<typeof createWrapper>) {
  return wrapper.findAll(`${block}__siteItem`).map((item) => item.text());
}

describe('CoreAdminHome/TrackingFailuresNotificationSettings', () => {
  beforeEach(() => {
    vi.mocked(AjaxHelper.post).mockReset();
  });

  it('does not allow adding a website that is already excluded', async () => {
    const wrapper = createWrapper();
    const add = wrapper.find(`${block}__siteAdd`);

    expect(add.attributes('disabled')).toBeDefined();

    // the selector reports the current website with its ID as a string
    await selectSite(wrapper, { id: '1', name: 'Site 1' });
    expect(add.attributes('disabled')).toBeDefined();

    await selectSite(wrapper, { id: '2', name: 'Site 2' });
    expect(add.attributes('disabled')).toBeUndefined();

    await add.trigger('click');
    expect(siteNames(wrapper)).toEqual(['Site 1', 'Site 2']);
    expect(add.attributes('disabled')).toBeDefined();
  });

  it('removes a website and says when none are excluded', async () => {
    const wrapper = createWrapper();

    const remove = wrapper.find(`${block}__siteRemove`);
    expect(remove.attributes('aria-label'))
      .toEqual('CoreAdminHome_TrackingFailuresRemoveExcludedWebsite|Site 1');

    await remove.trigger('click');

    expect(siteNames(wrapper)).toEqual([]);
    expect(wrapper.find(`${block}__noSites`).exists()).toBe(true);
  });

  it('saves the switch and the website IDs, and shows what the server kept', async () => {
    // website 2 was deleted in the meantime, so the server leaves it out
    vi.mocked(AjaxHelper.post).mockResolvedValue([{ id: 1, name: 'Site 1' }]);

    const wrapper = createWrapper();
    await selectSite(wrapper, { id: '2', name: 'Site 2' });
    await wrapper.find(`${block}__siteAdd`).trigger('click');
    await wrapper.find(`${block}__enabled`).setValue(false);

    await wrapper.find('.saveButton').trigger('click');
    await flushPromises();

    expect(AjaxHelper.post).toHaveBeenCalledWith(
      { module: 'API', method: 'CoreAdminHome.setTrackingFailuresNotificationSettings' },
      { enabled: 0, excludedIdSites: '1,2' },
    );
    expect(siteNames(wrapper)).toEqual(['Site 1']);
    expect(wrapper.emitted('saved')).toEqual([[[1]]]);
  });

  it('keeps the unsaved websites when saving fails', async () => {
    vi.mocked(AjaxHelper.post).mockRejectedValue(new Error('not saved'));

    const wrapper = createWrapper();
    await selectSite(wrapper, { id: 2, name: 'Site 2' });
    await wrapper.find(`${block}__siteAdd`).trigger('click');

    await wrapper.find('.saveButton').trigger('click');
    await flushPromises();

    expect(siteNames(wrapper)).toEqual(['Site 1', 'Site 2']);
    expect(wrapper.emitted('saved')).toBeUndefined();
    expect(wrapper.find(`${block}__siteAdd`).attributes('disabled')).toBeDefined();
  });
});
