this.timerId = setInterval(function() {
    console.log("Time");
    var ajaxHandler = new ajaxHelper();
    ajaxHandler.addParams({'module': 'MobileMessaging', 'action': 'getNotifications'}, 'GET');
    ajaxHandler.setCallback(function(response) {
        console.log(response);
        angular.forEach(response, function(notification) {
            Push.create("Matomo", {
                body: notification.title
            });
        });
    });
    ajaxHandler.setErrorCallback(function(response) {
        console.log(response);
    });
    ajaxHandler.send();
}, 60 * 1000);  // Timeout is in ms
console.log("Registered the interval timer");
