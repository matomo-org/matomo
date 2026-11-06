/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// reached from the mock factory, which runs before anything declared here would exist
const {
  modalConfirm, ajaxPost, showNotification, closeModal,
} = vi.hoisted(() => ({
  modalConfirm: vi.fn(),
  ajaxPost: vi.fn(() => Promise.resolve(true)),
  showNotification: vi.fn(),
  closeModal: vi.fn(),
}));

// Send request closes the Materialize modal itself
vi.stubGlobal('$', () => ({ closest: () => ({ modal: closeModal }) }));

// Pulled in dynamically: a vi.mock() factory is hoisted above the file's own imports.
vi.mock('CoreHome', async () => ({
  ...(await import('../testCoreHomeMock')).coreHomeMock(),
  Matomo: { helper: { modalConfirm } },
  AjaxHelper: { post: ajaxPost },
  NotificationsStore: { show: showNotification, scrollToNotification: vi.fn() },
}));

/* eslint-disable import/first */
import RequestTrial from './RequestTrial.vue';
import { makePlugin } from '../testMarketplaceFixtures';
import { translateStub } from '../testCoreHomeMock';
import { PluginCard } from '../types';

function mountDialog(plugin: Partial<PluginCard>, arePluginRequestsActive: boolean) {
  return mount(RequestTrial, {
    props: { modelValue: makePlugin(plugin), arePluginRequestsActive },
    global: { mocks: { translate: translateStub } },
  });
}

/** Opens the dialog for the plugin, as a card's button does, and returns its Send request handler. */
async function openDialog(plugin: Partial<PluginCard>) {
  const wrapper = mount(RequestTrial, {
    props: { modelValue: null, arePluginRequestsActive: true },
    global: { mocks: { translate: translateStub } },
  });
  await wrapper.setProps({ modelValue: makePlugin({ displayName: 'Funnels', ...plugin }) });
  const handles = modalConfirm.mock.calls[modalConfirm.mock.calls.length - 1][1];

  return { wrapper, sendRequest: handles.validation as () => void };
}

describe('Marketplace/RequestTrial', () => {
  beforeEach(() => {
    modalConfirm.mockClear();
    closeModal.mockClear();
    ajaxPost.mockClear();
    showNotification.mockClear();
  });

  describe('while plugin requests are not active', () => {
    const confirmText = (isTrialPending: boolean) => mountDialog({ isTrialPending }, false)
      .find('p').text();

    it('warns that super users will be emailed when no request is pending', () => {
      expect(confirmText(false)).toBe('Marketplace_RequestTrialConfirmEmailWarning');
    });

    it('says no further email is sent when a request is already pending', () => {
      expect(confirmText(true)).toBe('Marketplace_RequestTrialConfirmAlreadyRequested');
    });

    it('asks for no message', () => {
      expect(mountDialog({}, false).find('textarea').exists()).toBe(false);
    });
  });

  describe('while plugin requests are active', () => {
    it('asks for a trial of a premium plugin', () => {
      const wrapper = mountDialog({ displayName: 'Funnels', canTrialBeRequested: true }, true);
      expect(wrapper.find('h2').text()).toBe('Marketplace_RequestTrialTitle:Funnels');
      expect(wrapper.find('p').text()).toBe('Marketplace_RequestTrialIntro');
    });

    it('asks for an install of a free plugin', () => {
      const wrapper = mountDialog({ displayName: 'Funnels', canInstallBeRequested: true }, true);
      expect(wrapper.find('h2').text()).toBe('Marketplace_RequestInstallTitle:Funnels');
      expect(wrapper.find('p').text()).toBe('Marketplace_RequestInstallIntro');
    });

    it('offers an optional message, Cancel and Send request', () => {
      const wrapper = mountDialog({}, true);
      expect(wrapper.find('label').text()).toBe('Marketplace_RequestMessageLabel');
      expect(wrapper.find('textarea').attributes('placeholder'))
        .toBe('Marketplace_RequestMessagePlaceholder');
      expect(wrapper.find('input[role="no"]').attributes('value')).toBe('General_Cancel');
      expect(wrapper.find('input[role="validation"]').attributes('value'))
        .toBe('Marketplace_SendRequest');
    });

    it('sends a trial request with the message, then says where to follow it', async () => {
      const { wrapper, sendRequest } = await openDialog({ canTrialBeRequested: true });
      await wrapper.find('textarea').setValue('We need it for the videos');

      sendRequest();
      await Promise.resolve();

      expect(ajaxPost).toHaveBeenCalledWith(
        { module: 'API', method: 'Marketplace.requestTrial' },
        { pluginName: 'Funnels', message: 'We need it for the videos' },
      );
      expect(showNotification.mock.calls[0][0].message).toBe(
        'Marketplace_PluginRequestSent:<a href="?module=Marketplace&action=manageRequests&idSite=1">,</a>',
      );
      expect(wrapper.emitted('trialRequested')).toHaveLength(1);
      expect(closeModal).toHaveBeenCalledWith('close');
    });

    it('sends an install request for a free plugin', async () => {
      const { sendRequest } = await openDialog({ canInstallBeRequested: true });

      sendRequest();

      expect(ajaxPost.mock.calls[0][0])
        .toEqual({ module: 'API', method: 'Marketplace.requestInstall' });
    });

    it('puts the focus in the message box once the dialog is open', async () => {
      await openDialog({});
      const options = modalConfirm.mock.calls[modalConfirm.mock.calls.length - 1][2];
      expect(options.focusSelector).toBe('#requestPluginMessage');
      expect(options.classes).toBe('requestPluginModal');
    });

    it('starts each request with an empty message', async () => {
      const { wrapper } = await openDialog({});
      await wrapper.find('textarea').setValue('An earlier note');

      await wrapper.setProps({ modelValue: null });
      await wrapper.setProps({ modelValue: makePlugin({ name: 'Other' }) });

      expect((wrapper.find('textarea').element as HTMLTextAreaElement).value).toBe('');
    });
  });
});
