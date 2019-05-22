self.addEventListener('push', function(event) {
    console.log("Pushing with data " + event);

    const title = 'Matomo';
    const options = {
        body: 'Kia ora.'
        // icon: 'images/icon.png',
        // badge: 'images/badge.png'
    };

    event.waitUntil(self.registration.showNotification(title, options));
});