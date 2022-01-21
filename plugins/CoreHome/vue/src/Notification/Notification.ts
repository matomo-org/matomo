/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

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
  notificationInstanceId?: string;

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
   *
   * 'help' is only used by ReportingMenu.vue.
   */
  type: 'toast'|'persistent'|'transient'|'help';

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

  /**
   * If true, the notification will be displayed before others currently displayed.
   */
  prepend?: boolean;
}

export default Notification;
