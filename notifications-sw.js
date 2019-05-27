//self.addEventListener('install', function() {
    this.timerId = setInterval(function() {
        console.log("Time");
        var ajaxHandler = new ajaxHelper();
        ajaxHandler.addParams({'module': 'ScheduledReports', 'action': 'getNotifications'}, 'GET');
        ajaxHandler.setCallback(function(response) {
            console.log(response);
            angular.forEach(response, function(notification) {
                Push.create("Matomo", {
                    body: notification.title,
                    link: notification.link
                });
            });
        });
        ajaxHandler.setErrorCallback(function(response) {
            console.log(response);
        });
        ajaxHandler.send();
    }, 60 * 1000);  // Timeout is in ms
    console.log("Registered the interval timer");
//});

self.addEventListener('notificationclick', function(event) {
    console.log("Clicked on notification " + event);
    // debugger;
    // if (event.notification.data.link) {
    //     var origin = event.notification.data.origin;
    //     var link = event.notification.data.link;
    //     var href = origin.substring(0,origin.indexOf("/",8))+"/";
    //     if(link[0]==="/") {
    //         link = link.length > 1 ? link.substring(1, link.length) : "";
    //     }
    //     var full_url = href + link;
    //     clients.openWindow(full_url);
    //     event.notification.close();
    // }
});