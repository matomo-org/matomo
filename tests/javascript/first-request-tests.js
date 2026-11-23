/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/*global Matomo module test expect equal stop start */

(function () {
    var originalImage, originalSendBeacon, originalXhr, originalSetTimeout, images, beacons, xhrs, tracker;

    module('first tracking request', {
        setup: function () {
            originalImage = window.Image;
            originalSendBeacon = navigator.sendBeacon;
            originalXhr = window.XMLHttpRequest;
            originalSetTimeout = window.setTimeout;
            images = [];
            beacons = [];
            xhrs = [];

            window.Image = function () {
                images.push(this);
            };
            navigator.sendBeacon = function (url, body) {
                beacons.push({url: url, body: body});
                return true;
            };
            window.XMLHttpRequest = function () {
                this.open = function () {};
                this.setRequestHeader = function () {};
                this.send = function (body) {
                    this.body = body;
                    xhrs.push(this);
                };
            };

            tracker = Matomo.getTracker('matomo.php', 1);
            tracker.disableBrowserFeatureDetection();
            tracker.disableCookies();
        },
        teardown: function () {
            // Release pending requests before restoring the real network transports.
            images.forEach(function (image) {
                if (image.onload) {
                    image.onload();
                }
            });
            tracker.unsetPageIsUnloading();
            tracker.disableHeartBeatTimer();
            window.Image = originalImage;
            navigator.sendBeacon = originalSendBeacon;
            window.XMLHttpRequest = originalXhr;
            window.setTimeout = originalSetTimeout;
        }
    });

    test('events wait for the first response when it takes longer than 800ms', function () {
        expect(5);
        var completed = false;
        tracker.trackPageView('Initial page', null, function () {
            completed = true;
        });
        tracker.trackEvent('Cards', 'visible', 'first');
        tracker.trackEvent('Cards', 'visible', 'second');

        stop();
        setTimeout(function () {
            equal(images.length, 1, 'The first request has an observable response');
            equal(beacons.length, 0, 'Events cannot overtake the first request');
            equal(completed, false, 'The pageview callback waits for its response');

            if (images.length) {
                images[0].onload();
                equal(beacons.length, 2, 'Both pending events are sent after the response');
                equal(completed, true, 'The original pageview callback is preserved');
            }
            start();
        }, 1100);
    });

    test('a failed first response releases pending events', function () {
        expect(4);
        var success;
        tracker.trackPageView('Initial page', null, function (result) {
            success = result.success;
        });
        tracker.trackEvent('Cards', 'visible', 'first');

        equal(images.length, 1, 'A response is available for the first request');
        equal(beacons.length, 0, 'The event waits for the first response');
        if (images.length) {
            images[0].onerror();
            equal(success, false, 'The failure reaches the original callback');
            equal(beacons.length, 1, 'The queue is released after a failed response');
        }
    });

    test('an exception in the initial callback does not block pending events', function () {
        expect(2);
        tracker.trackPageView('Initial page', null, function () {
            throw new Error('Callback failed');
        });
        tracker.trackEvent('Cards', 'visible', 'first');

        try {
            images[0].onload();
        } catch (error) {
            equal(error.message, 'Callback failed', 'The original callback error is preserved');
        }
        equal(beacons.length, 1, 'Pending events are released even when the callback throws');
        // The browser dispatches this load callback once; avoid redispatching it in teardown.
        images[0].onload = null;
    });

    test('an exception in a pending callback does not discard later requests', function () {
        expect(3);
        var errors = [];
        tracker.trackPageView('Initial page');
        tracker.trackEvent('Cards', 'visible', 'first', null, null, function () {
            throw new Error('Pending callback failed');
        });
        tracker.trackEvent('Cards', 'visible', 'second');

        window.setTimeout = function (callback, delay) {
            if (delay === 0) {
                errors.push(callback);
                return 0;
            }
            return originalSetTimeout(callback, delay);
        };
        images[0].onload();
        equal(beacons.length, 2, 'The later request is still sent');
        equal(errors.length, 1, 'The callback error is reported asynchronously');
        try {
            errors[0]();
        } catch (error) {
            equal(error.message, 'Pending callback failed', 'The original error is preserved');
        }
    });

    test('the first bulk chunk is acknowledged before later chunks are sent', function () {
        expect(4);
        for (var i = 0; i < 51; i++) {
            tracker.queueRequest('e_c=Cards&e_a=visible&e_n=card' + i);
        }
        tracker.getRequestQueue().sendRequests();

        stop();
        setTimeout(function () {
            equal(xhrs.length, 1, 'Only the first bulk chunk is sent initially');
            equal(beacons.length, 0, 'Later chunks wait for the first chunk');
            if (xhrs.length) {
                equal(JSON.parse(xhrs[0].body).requests.length, 50, 'The normal bulk limit is preserved');
                xhrs[0].readyState = 4;
                xhrs[0].status = 204;
                xhrs[0].onreadystatechange();
                equal(beacons.length, 1, 'The remaining chunk is sent through sendBeacon');
            }
            start();
        }, 100);
    });

    test('heartbeat still starts after the initial request', function () {
        expect(1);
        tracker.trackPageView('Initial page');
        images[0].onload();
        tracker.enableHeartBeatTimer(5);

        stop();
        setTimeout(function () {
            var event = document.createEvent('Event');
            event.initEvent('blur', false, false);
            window.dispatchEvent(event);
            equal(beacons.length, 1, 'The heartbeat remains active after the initial request');
            start();
        }, 5100);
    });

    test('tracking can continue if the first response never arrives', function () {
        expect(2);
        tracker.trackPageView('Initial page');
        tracker.trackEvent('Cards', 'visible', 'first');
        equal(beacons.length, 0, 'The event initially waits');

        stop();
        setTimeout(function () {
            equal(beacons.length, 1, 'The bounded wait releases the event');
            start();
        }, 10100);
    });

    test('pending events respect consent withdrawn before the first response', function () {
        expect(2);
        tracker.requireConsent();
        tracker.setConsentGiven(false);
        tracker.trackPageView('Initial page');
        tracker.trackEvent('Cards', 'visible', 'first');
        tracker.forgetConsentGiven();

        images[0].onload();
        equal(beacons.length, 0, 'Pending tracking does not bypass withdrawn consent');
        tracker.setConsentGiven(false);
        equal(beacons.length, 1, 'The event is sent when consent is given again');
    });

    test('unloading flushes pending events once without waiting for the first response', function () {
        expect(3);
        tracker.trackPageView('Initial page');
        tracker.trackEvent('Cards', 'visible', 'first');

        equal(beacons.length, 0, 'The event initially waits');
        tracker.hook.test._beforeUnloadHandler();
        equal(beacons.length, 1, 'The pending event is sent before unloading');
        if (images.length) {
            images[0].onload();
            equal(beacons.length, 1, 'A late response does not resend the event');
        }
    });
}());
