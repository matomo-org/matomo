/*!
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Polls everyone. If they don't have any reports they'll never get any notifications. If they do have reports of the
 * right type, the behaviour depends on the browser permission status for notifications:
 * - Allowed (they've previously agreed to receive notifications from Matomo) - they'll see the notification.
 * - Ask (they've not yet been asked to receive notifications in this particular browser, probably because they
 * set up the reports in a different browser/device) - they'll be prompted to allow notifications, and then they'll
 * see the notification.
 * - Blocked (they've previously been asked and said no, or they have their browser settings screwed down so that
 * we're not allowed to ask, or on some browsers if Matomo was not loaded over HTTPS) - nothing happens.
 */
//startPolling();
