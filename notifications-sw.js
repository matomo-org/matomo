//self.importScripts('plugins/Morpheus/javascripts/ajaxHelper.js');

self.addEventListener('activate', async function() {
    console.log("Service worker activated");
// this.timerId = setInterval(function() {
//     console.log("Time");
//
//     // var ajaxHandler = new ajaxHelper();
//     // ajaxHandler.addParams({'module': 'MobileMessaging', 'action': 'getBrowserNotifications'}, 'GET');
//     // ajaxHandler.setCallback(function(response) {
//     //     angular.forEach(response, function(notification) {
//     //         Push.create(notification.title, {
//     //             body: notification.contents,
//     //             link: notification.link,
//     //             timeout: 30 * 1000  // 30 seconds
//     //         });
//     //     });
//     // });
//     // ajaxHandler.setErrorCallback(function(response) {
//     //     console.log(response);
//     // });
//     // ajaxHandler.send();
// }, 30 * 1000);  // Timeout is in ms
});

