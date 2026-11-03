/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import Notification from './Notification.vue';

vi.mock('../AjaxHelper/AjaxHelper', () => ({
  default: { post: vi.fn() },
}));

describe('CoreHome/Notification', () => {
  function closeNotification(type: string, notificationId: string) {
    const wrapper = mount(Notification, {
      props: { notificationId, type, message: 'Saved' },
      global: {
        mocks: {
          $sanitize: (html: string) => html,
        },
      },
    });

    return wrapper.find('button.close').trigger('click');
  }

  beforeEach(() => {
    vi.mocked(AjaxHelper.post).mockClear();
  });

  it('marks a persistent notification as read when it is closed', async () => {
    await closeNotification('persistent', 'storedNotification');

    expect(AjaxHelper.post).toHaveBeenCalledWith(
      { module: 'CoreHome', action: 'markNotificationAsRead' },
      { notificationId: 'storedNotification' },
      { withTokenInUrl: true },
    );
  });

  // only persistent notifications are kept in the session, the others have nothing to remove there
  it.each(['transient', 'toast'])('does not post the id of a %s notification when it is closed', async (type) => {
    await closeNotification(type, 'ManageGoals.create');

    expect(AjaxHelper.post).not.toHaveBeenCalled();
  });
});
