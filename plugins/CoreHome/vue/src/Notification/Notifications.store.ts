/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  DeepReadonly,
  reactive,
  createVNode,
  render,
} from 'vue';

interface Notification {
  id?: string;
  group?: string;
  // TODO: shouldn't need this since the title can be specified within
  //       HTML of the node that uses the directive.
  title?: string;
  message: string;
  context: 'success'|'error'|'info'|'warning';
  type: 'toast'|'persistent'|'transient';
  noclear?: boolean;
  toastLength?: number;
  style?: string;
  animate?: boolean;
}

interface NotificationsData {
  notifications: Notification[];
}

class NotificationsStore {
  private privateState: NotificationsData = reactive<NotificationsData>({
    notifications: [],
  });

  get state(): DeepReadonly<NotificationsData> {
    return this.privateState;
  }

  appendNotification(notification: Notification): void {
    // remove existing notification before adding
    if (notification.id) {
      this.removeNotification(notification.id);
    }
    this.privateState.notifications.push(notification);
  }

  prependNotification(notification: Notification): void {
    // remove existing notification before adding
    if (notification.id) {
      this.removeNotification(notification.id);
    }
    this.privateState.notifications.unshift(notification);
  }

  removeNotification(id: string): void {
    this.privateState.notifications = this.privateState.notifications.filter((n) => n.id !== id);
  }

  parseNotificationDivs(): void {
    const UI = window.require('piwik/UI');

    const $notificationNodes = $('[data-role="notification"]');

    $notificationNodes.each((index, notificationNode) => {
      const $notificationNode = $(notificationNode);
      const attributes = $notificationNode.data();
      const message = $notificationNode.html();

      if (message) {
        const notification = new UI.Notification();
        attributes.animate = false;
        notification.show(message, attributes);
      }

      $notificationNodes.remove();
    });
  }

  clearTransientNotifications(): void {
    this.privateState.notifications = this.privateState.notifications.filter((n) => n.type !== 'transient');
  }

  toast(notification: Notification): void {
    const toastElement = document.createElement('div');
    document.body.appendChild(toastElement);

    // TODO: make sure this gets unmounted
    const toastVNode = createVNode(
      Notification,
      {
        ...notification,
        onClosed: () => {
          render(null, toastElement);
        },
      },
      null,
    );

    render(toastVNode, toastElement);
  }
}

const instance = new NotificationsStore();
export default instance;

// parse notifications on dom load
$(() => instance.parseNotificationDivs());
