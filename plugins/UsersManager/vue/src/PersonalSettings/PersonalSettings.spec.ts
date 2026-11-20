/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount, flushPromises } from '@vue/test-utils';
import { AjaxHelper, NotificationsStore } from 'CoreHome';
import PersonalSettings from './PersonalSettings.vue';

vi.hoisted(() => {
  // CoreHome binds the zen mode shortcut on document ready, which fires while the imports are
  // still being resolved, so the Mousetrap global has to exist before them
  (window as unknown as { Mousetrap: unknown }).Mousetrap = { bind: () => undefined };
});

function mountSettings() {
  return mount(PersonalSettings, {
    props: {
      isUsersAdminEnabled: true,
      title: 'Personal settings',
      userLogin: 'someone',
      userEmail: 'someone@example.org',
      currentLanguageCode: 'en',
      languageOptions: { en: 'English', es: 'Español' },
      currentTimeformat: 12,
      timeFormats: { 12: '12h', 24: '24h' },
      themeMode: 'light',
      themeModeOptions: { light: 'Light', dark: 'Dark' },
      defaultReport: '1',
      defaultReportOptions: { 1: 'Site' },
      defaultReportIdSite: 1,
      defaultReportSiteName: 'Site',
      defaultDate: 'day',
      availableDefaultDates: { day: 'Day' },
      timeformat: 12,
    },
    global: {
      mocks: {
        translate: (key: string) => key,
        $sanitize: (value: string) => value,
      },
      stubs: {
        ContentBlock: true,
        SiteSelector: true,
        Field: true,
        Form: true,
        SaveButton: true,
        PasswordConfirmation: true,
      },
    },
  });
}

describe('PersonalSettings', () => {
  const originalHref = window.location.href;

  beforeEach(() => {
    vi.spyOn(AjaxHelper, 'post').mockResolvedValue({} as never);
    vi.spyOn(NotificationsStore, 'show').mockReturnValue('id');
    vi.spyOn(NotificationsStore, 'scrollToNotification').mockImplementation(() => undefined);
  });

  afterEach(() => {
    vi.restoreAllMocks();
    window.history.replaceState(null, '', originalHref);
  });

  it('stops the URL from pinning the language the user just replaced', async () => {
    window.history.replaceState(null, '', '/index.php?module=UsersManager&language=es');

    const wrapper = mountSettings();
    (wrapper.vm as unknown as { doSave: () => void }).doSave();
    await flushPromises();

    expect(window.location.search).toBe('?module=UsersManager');
  });

  it('leaves a URL without a language alone', async () => {
    window.history.replaceState(null, '', '/index.php?module=UsersManager');

    const wrapper = mountSettings();
    (wrapper.vm as unknown as { doSave: () => void }).doSave();
    await flushPromises();

    expect(window.location.search).toBe('?module=UsersManager');
  });
});
