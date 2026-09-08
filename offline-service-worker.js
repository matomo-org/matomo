var matomoAnalytics = {initialize: function (options) {
    if ('object' !== typeof options) {
        options = {};
    }

    var maxLimitQueue = options.queueLimit || 50;
    var maxTimeLimit = options.timeLimit || (60 * 60 * 24); // in seconds...
    // same as configured in in tracking_requests_require_authentication_when_custom_timestamp_newer_than

    // Dependency injection: tests pass stubs; production defaults to real globals.
    // Resolved lazily via getters so a test that swaps `options.fetch` after init
    // wouldn't, and so we don't capture undefined when a global is missing.
    var deps = {
        indexedDB: options.indexedDB || (typeof indexedDB !== 'undefined' ? indexedDB : null),
        fetch:     options.fetch     || (typeof fetch     !== 'undefined' ? fetch     : null),
        now:       options.now       || function () { return Date.now(); },
        self:      options.self      || (typeof self      !== 'undefined' ? self      : null),
        navigator: options.navigator || (typeof navigator !== 'undefined' ? navigator : null),
        caches:    options.caches    || (typeof caches    !== 'undefined' ? caches    : null),
    };

    // WHATWG fetch requires its `this` to be the global (WorkerGlobalScope in a
    // service worker). Calling `deps.fetch(...)` detaches it from that receiver
    // and throws "TypeError: Illegal invocation" in real browsers. Always invoke
    // via .call(globalThis-equivalent, ...) so DI stays intact for tests.
    function doFetch(input, init) {
        return deps.fetch.call(deps.self || null, input, init);
    }

    function getQueue()
    {
        return new Promise(function(resolve, reject) {
            // do a thing, possibly async, then...

            if (!deps.indexedDB) {
                reject(new Error('No support for IndexedDB'));
                return;
            }
            var request = deps.indexedDB.open("matomo", 1);

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
        // Returns a promise that resolves only after the cursor walk has
        // exhausted the queue AND every in-flight fetch has settled. The
        // service worker's `sync` handler relies on this so `event.waitUntil`
        // keeps the worker alive through the full replay on slow devices.
        return getQueue().then(function (queue) {
            return new Promise(function (resolveAll) {
                var inFlight = [];

                queue.openCursor().onsuccess = function(event) {
                    var cursor = event.target.result;
                    if (cursor && deps.navigator && deps.navigator.onLine) {
                        cursor.continue();
                        var queueId = cursor.value.id;

                        var secondsQueuedAgo = ((deps.now() - cursor.value.created) / 1000);
                        secondsQueuedAgo = parseInt(secondsQueuedAgo, 10);
                        if (secondsQueuedAgo > maxTimeLimit) {
                            // too old
                            inFlight.push(getQueue().then(function (queue) {
                                queue.delete(queueId);
                            }));
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
                            // Singleton GET-style URL: stamp cdo on the URL.
                            cursor.value.url += '&cdo=' + secondsQueuedAgo;
                        } else if (init.body && init.body.indexOf('"requests"') !== -1) {
                            // Bulk: JSON body of the form {"requests":["?...","?..."],"send_image":0}.
                            // Stamp cdo into each inner request so every event is backdated, not
                            // just the first one (String.prototype.replace with a string needle
                            // replaces only the first occurrence).
                            try {
                                var bulk = JSON.parse(init.body);
                                if (bulk && Array.isArray(bulk.requests)) {
                                    bulk.requests = bulk.requests.map(function (q) {
                                        return q + '&cdo=' + secondsQueuedAgo;
                                    });
                                    init.body = JSON.stringify(bulk);
                                }
                            } catch (e) {
                                // Malformed body — leave it alone rather than corrupt it.
                            }
                        } else if (init.body) {
                            // Form-encoded singleton via body (rare; preserved for compatibility).
                            init.body = init.body.replace('&idsite=', '&cdo=' + secondsQueuedAgo + '&idsite=');
                        }

                        inFlight.push(doFetch(cursor.value.url, init).then(function (response) {
                            console.log('server response', response);
                            if (response.status < 400) {
                                return getQueue().then(function (queue) {
                                    queue.delete(queueId);
                                });
                            }
                        }).catch(function (error) {
                            console.error('Send to Server failed:', error);
                        }));
                    }
                    else {
                        console.log("No more entries!");
                        // Cursor exhausted: wait for every queued fetch/delete
                        // to settle before resolving the outer promise.
                        resolveAll(Promise.allSettled(inFlight));
                    }
                };
            });
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

    if (deps.self && typeof deps.self.addEventListener === 'function') {
        deps.self.addEventListener('sync', function(event) {
            if (event.tag === 'matomoSync') {
                // waitUntil keeps the service worker alive until the full replay
                // (cursor walk + every fetch) completes — on slow phones or large
                // offline queues the worker could otherwise be terminated mid-sync.
                event.waitUntil(syncQueue());
            }
        });

        deps.self.addEventListener('fetch', function (event)  {
            let isOnline = deps.navigator && deps.navigator.onLine;

            let isTrackingRequest = (event.request.url.includes('/matomo.php')
                                || event.request.url.includes('/piwik.php'));
            let isTrackerRequest = event.request.url.endsWith('/matomo.js')
                                || event.request.url.endsWith('/piwik.js');

            if (isTrackerRequest) {
                if (isOnline) {
                    syncQueue();
                }
                deps.caches.open('matomo').then(function(cache) {
                    return cache.match(event.request).then(function (response) {
                        return response || doFetch(event.request).then(function(response) {
                            cache.put(event.request, response.clone());
                            return response;
                        });
                    });
                })
            } else if (isTrackingRequest && isOnline) {
                syncQueue();
                event.respondWith(doFetch(event.request));
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
                    created: deps.now()
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

    // Expose internals so tests can drive them directly. Production callers
    // (matomo.js / piwik.js) ignore the return value.
    return { syncQueue: syncQueue, getQueue: getQueue };
}
};

// Make the module require()-able from Node-based test runners (Jest). The IIFE
// pattern stays intact for the browser SW context, which has no `module`.
if (typeof module !== 'undefined' && module.exports) {
    module.exports = matomoAnalytics;
}