var matomoAnalytics = {initialize: function (options) {
    if ('object' !== typeof options) {
        options = {};
    }

    var maxLimitQueue = options.queueLimit || 50;
    var maxTimeLimit = options.timeLimit || (60 * 60 * 24); // in seconds...
    // same as configured in in tracking_requests_require_authentication_when_custom_timestamp_newer_than

    function getQueue()
    {
        return new Promise(function(resolve, reject) {
            // do a thing, possibly async, then...

            if (!indexedDB) {
                reject(new Error('No support for IndexedDB'));
                return;
            }
            var request = indexedDB.open("matomo", 1);

            request.onerror = function() {
                console.error("Error", request.error);
                reject(new Error(request.error));
            };
            request.onupgradeneeded = function(event) {
                console.log('onupgradeneeded')
                var db = event.target.result;

                if (!db.objectStoreNames.contains('requests')) {
                    db.createObjectStore('requests', {autoIncrement : true, keyPath: 'id'});
                }

            };
            request.onsuccess = function(event) {
                var db = event.target.result;
                let transaction = db.transaction("requests", "readwrite");
                let requests =transaction.objectStore("requests");
                resolve(requests);


            };
        });
    }

    function syncQueue () {
        // check something in indexdb
        return getQueue().then(function (queue) {
            queue.openCursor().onsuccess = function(event) {
                var cursor = event.target.result;
                if (cursor && navigator.onLine) {
                    cursor.continue();
                    var queueId = cursor.value.id;

                    var secondsQueuedAgo = ((Date.now() - cursor.value.created) / 1000);
                    secondsQueuedAgo = parseInt(secondsQueuedAgo, 10);
                    if (secondsQueuedAgo > maxTimeLimit) {
                        // too old
                        getQueue().then(function (queue) {
                            queue.delete(queueId);
                        });
                        return;
                    }

                    console.log("Cursor " + cursor.key);

                    var init = {
                        headers: cursor.value.headers,
                        method: cursor.value.method,
                    }
                    if (cursor.value.body) {
                        init.body = cursor.value.body;
                    }

                    if (cursor.value.url.includes('?')) {
                        cursor.value.url += '&cdo=' + secondsQueuedAgo;
                    } else if (init.body) {
                        // todo test if this actually works for bulk requests
                        init.body = init.body.replace('&idsite=', '&cdo=' + secondsQueuedAgo + '&idsite=');
                    }

                    fetch(cursor.value.url, init).then(function (response) {
                        console.log('server response', response);
                        if (response.status < 400) {
                            getQueue().then(function (queue) {
                                queue.delete(queueId);
                            });
                        }
                    }).catch(function (error) {
                        console.error('Send to Server failed:', error);
                        throw error
                    })
                }
                else {
                    console.log("No more entries!");
                }
            };
        });
    }

    function limitQueueIfNeeded(queue)
    {
        var countRequest = queue.count();
        countRequest.onsuccess = function(event) {
            if (event.result > maxLimitQueue) {
                // we delete only one at a time because of concurrency some other process might delete data too
                queue.openCursor().onsuccess = function(event) {
                    var cursor = event.target.result;
                    if (cursor) {
                        queue.delete(cursor.value.id);
                        limitQueueIfNeeded(queue);
                    }
                }
            }
        }
    }

    self.addEventListener('sync', function(event) {
        if (event.tag === 'matomoSync') {
            syncQueue();
        }
    });

    self.addEventListener('fetch', function (event)  {
        let isOnline = navigator.onLine;

        let isTrackingRequest = (event.request.url.includes('/matomo.php')
                            || event.request.url.includes('/piwik.php'));
        let isTrackerRequest = event.request.url.endsWith('/matomo.js')
                            || event.request.url.endsWith('/piwik.js');

        if (isTrackerRequest) {
            if (isOnline) {
                syncQueue();
            }
            caches.open('matomo').then(function(cache) {
                return cache.match(event.request).then(function (response) {
                    return response || fetch(event.request).then(function(response) {
                        cache.put(event.request, response.clone());
                        return response;
                    });
                });
            })
        } else if (isTrackingRequest && isOnline) {
            syncQueue();
            event.respondWith(fetch(event.request));
        } else if (isTrackingRequest && !isOnline) {

            var headers = {};
            for (const [header, value] of event.request.headers) {
                headers[header] = value;
            }

            let requestInfo = {
                url: event.request.url,
                referrer : event.request.referrer,
                method : event.request.method,
                referrerPolicy : event.request.referrerPolicy,
                headers : headers,
                created: Date.now()
            };
            event.request.text().then(function (postData) {
                requestInfo.body = postData;

                getQueue().then(function (queue) {
                    queue.add(requestInfo);
                    limitQueueIfNeeded(queue);

                    return queue;
                });
            });

        }
    });
}
};