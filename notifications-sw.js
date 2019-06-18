/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
var token;

self.addEventListener('install', async function() {
    self.token = new URL(location).searchParams.get('token');
});

self.addEventListener('activate', async function() {
    // Fetch any notifications that have already been generated
    fetchNotifications();

    // Check periodically for new notifications
    this.timerId = setInterval(function() {
        fetchNotifications();
    }, 30 * 1000);  // Timeout is in ms
});

var fetchNotifications = function() {
    fetch('index.php?module=API&action=MobileMessaging.getBrowserNotifications&format=JSON&token=' + self.token, 
        {credentials: "omit"}
    ).then(function(response) {
        if (response.ok || response.redirected) {
            response.json.then(displayNotifications);
        } else {
            console.error("Failed to get notifications");
            response.text().then(console.error);
        }
    });
};

var displayNotifications = function(notifications) {
    if (notifications.constructor !== Array) {
        console.error("Unexpected response from server: ");
        console.error(notifications);
    }

    notifications.forEach(function(notification) {
        self.registration.showNotification(notification.title, {
            body: notification.contents
            // timeout: 30 * 1000  // 30 seconds
        });
    });
};