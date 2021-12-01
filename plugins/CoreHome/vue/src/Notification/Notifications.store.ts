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
  createApp,
} from 'vue';
import NotificationComponent from './Notification.vue';
import translate from '../translate';
import Matomo from '../Matomo/Matomo';

interface Notification {
  /**
   * Only needed for persistent notifications. The id will be sent to the
   * frontend once the user closes the notifications. The notification has to
   * be registered/notified under this name.
   */
  id?: string;

  /**
   * Unique ID generated for the notification so it can be referenced specifically
   * to scroll to.
   */
  notificationInstanceId: string;

  /**
   * Determines which notification group a notification is meant to be displayed
   * in.
   */
  group?: string;

  /**
   * The title of the notification. For instance the plugin name.
   */
  title?: string;

  /**
   * The actual message that will be displayed. Must be set.
   */
  message: string;

  /**
   * Context of the notification: 'info', 'warning', 'success' or 'error'
   */
  context: 'success'|'error'|'info'|'warning';

  /**
   * The type of the notification: Either 'toast' or 'transient'. 'persistent' is valid, but
   * has no effect if only specified client side.
   */
  type: 'toast'|'persistent'|'transient';

  /**
   * If set, the close icon is not displayed.
   */
  noclear?: boolean;

  /**
   * The number of milliseconds before a toast animation disappears.
   */
  toastLength?: number;

  /**
   * Optional style/css dictionary. For instance {'display': 'inline-block'}
   */
  style?: string|Record<string, unknown>;

  /**
   * Optional CSS class to add.
   */
  class?: string;

  /**
   * If true, fades the animation in.
   */
  animate?: boolean;

  /**
   * Where to place the notification. Required if showing a toast.
   */
  placeat?: string|HTMLElement|JQuery;
}

interface NotificationsData {
  notifications: Notification[];
}

class NotificationsStore {
  private privateState: NotificationsData = reactive<NotificationsData>({
    notifications: [],
  });

  private nextNotificationId = 0;

  get state(): DeepReadonly<NotificationsData> {
    return this.privateState;
  }

  appendNotification(notification: Notification): void {
    this.checkMessage(notification.message);

    // remove existing notification before adding
    if (notification.id) {
      this.remove(notification.id);
    }
    this.privateState.notifications.push(notification);
  }

  prependNotification(notification: Notification): void {
    this.checkMessage(notification.message);

    // remove existing notification before adding
    if (notification.id) {
      this.remove(notification.id);
    }
    this.privateState.notifications.unshift(notification);
  }

  /**
   * Removes a previously shown notification having the given notification id.
   */
  remove(id: string): void {
    this.privateState.notifications = this.privateState.notifications.filter(
      (n) => n.id !== id,
    );
  }

  parseNotificationDivs(): void {
    const $notificationNodes = $('[data-role="notification"]');

    const notificationsToShow = [];
    $notificationNodes.each((index, notificationNode) => {
      const $notificationNode = $(notificationNode);
      const attributes = $notificationNode.data();
      const message = $notificationNode.html();

      if (message) {
        notificationsToShow.push({ ...attributes, message, animate: false });
      }

      $notificationNodes.remove();
    });

    notificationsToShow.forEach((n) => this.show(n));
  }

  clearTransientNotifications(): void {
    this.privateState.notifications = this.privateState.notifications.filter(
      (n) => n.type !== 'transient',
    );
  }

  /**
   * Creates a notification and shows it to the user.
   */
  show(notification: Notification): string {
    this.checkMessage(notification.message);

    let addMethod = this.appendNotification;

    let notificationPosition: typeof Notification['placeat'] = '#notificationContainer';
    if (notification.placeat) {
      notificationPosition = notification.placeat;
    } else {
      // If a modal is open, we want to make sure the error message is visible and therefore
      // show it within the opened modal
      const modalSelector = '.modal.open .modal-content';
      const modal = document.querySelector(modalSelector);
      if (modal) {
        if (!modal.querySelector('#modalNotificationContainer')) {
          window.$(modal).prepend('<div id="modalNotificationContainer"/>');
        }

        notificationPosition = `${modalSelector} #modalNotificationContainer`;
        addMethod = this.prependNotification;
      }
    }

    const group = notification.group
      || (notificationPosition ? notificationPosition.toString() : '');

    this.initializeNotificationContainer(notificationPosition, group);

    const notificationInstanceId = (this.nextNotificationId += 1).toString();

    addMethod.call(this, {
      ...notification,
      noclear: !!notification.noclear,
      group,
      notificationId: notification.id,
      notificationInstanceId,
      type: notification.type || 'transient',
    });

    return notificationInstanceId;
  }

  scrollToNotification(notificationInstanceId: string) {
    setTimeout(() => {
      const element = document.querySelector(`[data-notification-instance-id='${notificationInstanceId}']`);
      if (element) {
        Matomo.helper.lazyScrollTo(element, 250);
      }
    });
  }

  /**
   * Shows a notification at a certain point with a quick upwards animation.
   */
  toast(notification: Notification): void {
    this.checkMessage(notification.message);

    const $placeat = $(notification.placeat);
    if (!$placeat.length) {
      throw new Error('A valid selector is required for the placeat option when using Notification.toast().');
    }

    const toastElement = document.createElement('div');
    toastElement.style.position = 'absolute';
    toastElement.style.top = `${$placeat.offset().top}px`;
    toastElement.style.left = `${$placeat.offset().left}px`;
    toastElement.style.zIndex = '1000';
    document.body.appendChild(toastElement);

    const app = createApp({
      render: () => createVNode(NotificationComponent, {
        ...notification,
        notificationId: notification.id,
        type: 'toast',
        onClosed: () => {
          app.unmount();
        },
      }),
    });
    app.config.globalProperties.$sanitize = window.vueSanitize;
    app.config.globalProperties.translate = translate;
    app.mount(toastElement);
  }

  private initializeNotificationContainer(
    notificationPosition: typeof Notification['placeat'],
    group: string,
  ) {
    const $container = window.$(notificationPosition);
    if ($container.children('.notification-group').length) {
      return;
    }

    // avoiding a dependency cycle. won't need to do this when NotificationGroup's do not need
    // to be dynamically initialized.
    const NotificationGroup = (window as any).CoreHome.NotificationGroup; // eslint-disable-line

    const app = createApp({
      template: '<NotificationGroup :group="group"></NotificationGroup>',
      data: () => ({ group }),
    });
    app.config.globalProperties.$sanitize = window.vueSanitize;
    app.config.globalProperties.translate = translate;
    app.component('NotificationGroup', NotificationGroup);
    app.mount($container[0]);
  }

  private checkMessage(message: string) {
    if (!message) {
      throw new Error('No message given, cannot display notification');
    }
  }
}

const instance = new NotificationsStore();
export default instance;

// parse notifications on dom load
$(() => instance.parseNotificationDivs());
