var token;

self.addEventListener('install', async function() {
    self.token = new URL(location).searchParams.get('token');
    console.log("My token is " + self.token);
});

self.addEventListener('activate', async function() {
    console.log("Service worker activated");
    // Fetch any notifications that have already been generated
    fetchNotifications();

    // Check periodically for new notifications
    this.timerId = setInterval(function() {
        fetchNotifications();
    }, 30 * 1000);  // Timeout is in ms
});

var fetchNotifications = function() {
    console.log("Time");
    fetch('index.php?module=MobileMessaging&action=getBrowserNotifications&token=' + self.token)
        .then(function(response) {
            if (response.status === 200) {
                displayNotifications(response);
            }
        })
        .catch(function(error) {
            console.log(error);
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