/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount, flushPromises } from '@vue/test-utils';

vi.mock('CoreHome', () => ({
  ActivityIndicator: { template: '<div></div>' },
  AjaxHelper: { fetch: () => Promise.resolve([]), post: vi.fn() },
  ContentBlock: { template: '<div><slot></slot></div>' },
  NotificationsStore: { show: vi.fn(), scrollToNotification: vi.fn() },
  translate: (key: string) => key,
}));

vi.mock('CorePluginsAdmin', () => ({
  SaveButton: { template: '<button></button>' },
}));

import AIProcessingSettings from './AIProcessingSettings.vue';

async function mountAt(search: string) {
  window.history.replaceState(null, '', `/index.php${search}`);
  const wrapper = mount(AIProcessingSettings, {
    global: { config: { globalProperties: { translate: (key: string) => key } as never } },
  });
  await flushPromises();
  return wrapper;
}

describe('AIProviders/AIProcessingSettings.vue', () => {
  afterEach(() => {
    window.history.replaceState(null, '', '/');
  });

  it('links back to the Matomo page it was opened from', async () => {
    const returnTo = 'index.php?module=CoreHome&action=index&idSite=1#?period=day';
    const wrapper = await mountAt(`?module=AIProviders&action=aiProcessing&returnTo=${encodeURIComponent(returnTo)}`);

    const link = wrapper.find('.ai-processing-back');
    expect(link.attributes('href')).toBe(returnTo);
    expect(link.text()).toBe('AIProviders_BackToPreviousPage');
  });

  it('shows no link without a return target', async () => {
    const wrapper = await mountAt('?module=AIProviders&action=aiProcessing');

    expect(wrapper.find('.ai-processing-back').exists()).toBe(false);
  });

  it.each([
    'https://example.com/index.php',
    '//example.com/index.php',
    'javascript:alert(1)',
  ])('ignores a return target outside this Matomo: %s', async (returnTo) => {
    const wrapper = await mountAt(`?returnTo=${encodeURIComponent(returnTo)}`);

    expect(wrapper.find('.ai-processing-back').exists()).toBe(false);
  });
});
