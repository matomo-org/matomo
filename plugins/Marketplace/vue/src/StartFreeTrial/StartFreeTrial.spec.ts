/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { flushPromises, mount } from '@vue/test-utils';

// the component reads window.$ when it is imported, so the stub has to exist before that
const { ajaxPost } = vi.hoisted(() => {
  const jQueryStub = { modal: () => jQueryStub };
  vi.stubGlobal('$', () => jQueryStub);

  return { ajaxPost: vi.fn(() => Promise.resolve(true)) };
});

// Pulled in dynamically: a vi.mock() factory is hoisted above the file's own imports.
vi.mock('CoreHome', async () => ({
  ...(await import('../testCoreHomeMock')).coreHomeMock(),
  AjaxHelper: { post: ajaxPost },
  Matomo: { helper: { htmlDecode: (text: string) => text } },
  NotificationsStore: { show: vi.fn(), scrollToNotification: vi.fn() },
  externalLink: () => '<a>',
}));

vi.mock('CorePluginsAdmin', () => ({ Field: { template: '<div/>' } }));

/* eslint-disable import/first */
import StartFreeTrial from './StartFreeTrial.vue';
import { translateStub } from '../testCoreHomeMock';

async function startTrial(props: Record<string, unknown> = {}) {
  const wrapper = mount(StartFreeTrial, {
    props: { modelValue: null, isValidConsumer: true, ...props },
    global: { mocks: { translate: translateStub, $sanitize: (value: string) => value } },
  });
  await wrapper.setProps({ modelValue: { name: 'Funnels', displayName: 'Funnels' } });
  await flushPromises();

  return wrapper;
}

describe('Marketplace/StartFreeTrial', () => {
  beforeEach(() => {
    ajaxPost.mockClear();
  });

  it('starts the trial with Marketplace.startFreeTrial by default', async () => {
    await startTrial();

    expect(ajaxPost).toHaveBeenCalledWith(
      { module: 'API', method: 'Marketplace.startFreeTrial' },
      { pluginName: 'Funnels' },
      { createErrorNotification: false },
    );
  });

  it('starts the trial with the given API method', async () => {
    await startTrial({ apiMethod: 'Marketplace.approvePluginTrial' });

    expect(ajaxPost).toHaveBeenCalledWith(
      { module: 'API', method: 'Marketplace.approvePluginTrial' },
      { pluginName: 'Funnels' },
      { createErrorNotification: false },
    );
  });
});
