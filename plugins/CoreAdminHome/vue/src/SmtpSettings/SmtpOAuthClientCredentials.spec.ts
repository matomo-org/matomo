/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

vi.mock('CoreHome', () => ({
  AutoClearPassword: {},
  Matomo: { helper: { htmlEntities: (value: string) => value.replace(/</g, '&lt;') } },
  translate: (key: string, ...params: string[]) => [key, ...params].join(' '),
}));

vi.mock('CorePluginsAdmin', () => ({
  Field: {
    props: ['name', 'modelValue', 'inlineHelp'],
    emits: ['update:modelValue'],
    template: '<input :name="name" :value="modelValue" :data-help="inlineHelp"'
      + ' @input="$emit(\'update:modelValue\', $event.target.value)" />',
  },
}));

import SmtpOAuthClientCredentials from './SmtpOAuthClientCredentials.vue';

const storedSettings = {
  tokenUrl: 'https://login.example.test/token',
  clientId: 'the-client-id',
  scope: 'https://mail.example.test/.default',
  credentialType: 'secret',
};

function mountWith(modelValue: Record<string, string>, hasStoredClientSecret = true, certificatePath = '') {
  return mount(SmtpOAuthClientCredentials, {
    props: { modelValue, hasStoredClientSecret, certificatePath },
    global: { config: { globalProperties: { translate: (key: string) => key } as never } },
  });
}

function getCredentialTypeHelp(wrapper: ReturnType<typeof mountWith>) {
  return wrapper.find('[name="mailOAuthCredentialType"]').attributes('data-help');
}

describe('CoreAdminHome/SmtpOAuthClientCredentials.vue', () => {
  it.each([
    ['mailOAuthTokenUrl', 'tokenUrl'],
    ['mailOAuthClientId', 'clientId'],
    ['mailOAuthScope', 'scope'],
  ])('drops the stored client secret when %s changes', async (name, setting) => {
    const wrapper = mountWith(storedSettings);

    await wrapper.find(`[name="${name}"]`).setValue('changed');

    expect(wrapper.emitted('update:modelValue')).toEqual([
      [{ ...storedSettings, [setting]: 'changed', clientSecret: '' }],
    ]);
  });

  it('keeps an entered client secret when another setting changes', async () => {
    const wrapper = mountWith({ ...storedSettings, clientSecret: 'a-new-secret' });

    await wrapper.find('[name="mailOAuthTokenUrl"]').setValue('https://another.example.test/token');

    expect(wrapper.emitted('update:modelValue')).toEqual([
      [{ ...storedSettings, tokenUrl: 'https://another.example.test/token', clientSecret: 'a-new-secret' }],
    ]);
  });

  it.each([
    ['secret', '/etc/matomo/smtp.crt', 'CoreAdminHome_SmtpOAuthClientSecretUsed'],
    ['certificate', '/etc/matomo/smtp.crt', 'CoreAdminHome_SmtpOAuthCertificateConfigured /etc/matomo/smtp.crt'],
    ['certificate', '/etc/<b>.crt', 'CoreAdminHome_SmtpOAuthCertificateConfigured /etc/&lt;b>.crt'],
    ['certificate', '', 'CoreAdminHome_SmtpOAuthCertificateNotConfigured'],
  ])('shows the help for the %s type with the certificate %j', (credentialType, certificatePath, expectedHelp) => {
    const wrapper = mountWith({ ...storedSettings, credentialType }, true, certificatePath);

    expect(wrapper.find('[name="mailOAuthClientSecret"]').exists()).toBe(credentialType === 'secret');
    expect(getCredentialTypeHelp(wrapper)).toBe(expectedHelp);
  });

  it('updates the help when the credential type changes', async () => {
    const wrapper = mountWith(storedSettings);

    await wrapper.setProps({ modelValue: { ...storedSettings, credentialType: 'certificate' } });
    expect(getCredentialTypeHelp(wrapper)).toBe('CoreAdminHome_SmtpOAuthCertificateNotConfigured');

    await wrapper.setProps({ modelValue: storedSettings });
    expect(getCredentialTypeHelp(wrapper)).toBe('CoreAdminHome_SmtpOAuthClientSecretUsed');
  });
});
