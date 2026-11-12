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

function getValue(wrapper: ReturnType<typeof mountWith>, name: string) {
  return (wrapper.find(`[name="${name}"]`).element as HTMLInputElement).value;
}

describe('CoreAdminHome/SmtpOAuthClientCredentials.vue', () => {
  it('masks a stored client secret and reports no change', () => {
    const wrapper = mountWith(storedSettings);

    expect(getValue(wrapper, 'mailOAuthClientSecret')).toBe('******');
    expect(wrapper.emitted('update:modelValue')).toBeUndefined();
  });

  it('shows an empty client secret when none is stored', () => {
    const wrapper = mountWith(storedSettings, false);

    expect(getValue(wrapper, 'mailOAuthClientSecret')).toBe('');
  });

  it('reports a client secret once it was entered', async () => {
    const wrapper = mountWith(storedSettings);

    await wrapper.find('[name="mailOAuthClientSecret"]').setValue('a-new-secret');

    expect(wrapper.emitted('update:modelValue')).toEqual([
      [{ ...storedSettings, clientSecret: 'a-new-secret' }],
    ]);
  });

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

    expect(getValue(wrapper, 'mailOAuthClientSecret')).toBe('a-new-secret');
    expect(wrapper.emitted('update:modelValue')).toEqual([
      [{ ...storedSettings, tokenUrl: 'https://another.example.test/token', clientSecret: 'a-new-secret' }],
    ]);
  });

  it('reports the chosen credential type', async () => {
    const wrapper = mountWith(storedSettings);

    await wrapper.find('[name="mailOAuthCredentialType"]').setValue('certificate');

    expect(wrapper.emitted('update:modelValue')).toEqual([
      [{ ...storedSettings, credentialType: 'certificate' }],
    ]);
  });

  it('shows the certificate set up in the config file instead of the client secret', () => {
    const wrapper = mountWith({ ...storedSettings, credentialType: 'certificate' }, true, '/etc/matomo/smtp.crt');

    expect(wrapper.find('[name="mailOAuthClientSecret"]').exists()).toBe(false);
    expect(getCredentialTypeHelp(wrapper))
      .toBe('CoreAdminHome_SmtpOAuthCertificateConfigured /etc/matomo/smtp.crt');
  });

  it('escapes the certificate path, as the status is shown as HTML', () => {
    const wrapper = mountWith({ ...storedSettings, credentialType: 'certificate' }, true, '/etc/<b>.crt');

    expect(getCredentialTypeHelp(wrapper))
      .toBe('CoreAdminHome_SmtpOAuthCertificateConfigured /etc/&lt;b>.crt');
  });

  it('says when no certificate is set up', () => {
    const wrapper = mountWith({ ...storedSettings, credentialType: 'certificate' });

    expect(getCredentialTypeHelp(wrapper)).toBe('CoreAdminHome_SmtpOAuthCertificateNotConfigured');
  });

  it('says the client secret is used for the client secret type', () => {
    const wrapper = mountWith(storedSettings, true, '/etc/matomo/smtp.crt');

    expect(getCredentialTypeHelp(wrapper)).toBe('CoreAdminHome_SmtpOAuthClientSecretUsed');
  });

  it('updates the help when the credential type changes', async () => {
    const wrapper = mountWith(storedSettings);

    await wrapper.setProps({ modelValue: { ...storedSettings, credentialType: 'certificate' } });
    expect(getCredentialTypeHelp(wrapper)).toBe('CoreAdminHome_SmtpOAuthCertificateNotConfigured');

    await wrapper.setProps({ modelValue: storedSettings });
    expect(getCredentialTypeHelp(wrapper)).toBe('CoreAdminHome_SmtpOAuthClientSecretUsed');
  });
});
