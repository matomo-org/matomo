/*!
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistration('notifications-sw.js')
        .then(function(registration) {
            if (typeof registration === "undefined") {
                Push.Permission.request(function() {
                    navigator.serviceWorker.register('/notifications-sw.js');
                });
            }
        });
}