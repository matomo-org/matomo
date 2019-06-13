self.addEventListener('activate', async function() {
    // Fetch any notifications that have already been generated
    fetchNotifications();

    // Check periodically for new notifications
    this.timerId = setInterval(function() {
        fetchNotifications();
    }, 30 * 1000);  // Timeout is in ms
});

var fetchNotifications = function() {
    fetch('index.php?module=MobileMessaging&action=getBrowserNotifications')
        .then(function(response) {
            if (response.status === 200) {
                displayNotifications(response);
            }
        });
};

var displayNotifications = function(response) {
    response.json().then(function(notifications) {
        notifications.forEach(function(notification) {
                self.registration.showNotification(notification.title, {
                    body: notification.contents
                    // timeout: 30 * 1000  // 30 seconds
                });
        });
    });
};