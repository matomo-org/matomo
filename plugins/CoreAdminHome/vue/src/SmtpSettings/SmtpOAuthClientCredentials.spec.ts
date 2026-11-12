/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

vi.mock('CoreHome', () => ({
  AutoClearPassword: {},
  translate: (key: string) => key,
}));

vi.mock('CorePluginsAdmin', () => ({
  Field: {
    props: ['name', 'modelValue'],
    emits: ['update:modelValue'],
    template: '<input :name="name" :value="modelValue"'
      + ' @input="$emit(\'update:modelValue\', $event.target.value)" />',
  },
}));

import SmtpOAuthClientCredentials from './SmtpOAuthClientCredentials.vue';

const storedSettings = {
  tokenUrl: 'https://login.example.test/token',
  clientId: 'the-client-id',
  scope: 'https://mail.example.test/.default',
};

function mountWith(modelValue: Record<string, string>, hasStoredClientSecret = true) {
  return mount(SmtpOAuthClientCredentials, {
    props: { modelValue, hasStoredClientSecret },
    global: { config: { globalProperties: { translate: (key: string) => key } as never } },
  });
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
});
