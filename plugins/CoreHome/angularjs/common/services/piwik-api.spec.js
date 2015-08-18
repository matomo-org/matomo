/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    describe('piwikApiClient', function () {
        var piwikApi,
            $httpBackend;

        if (!window.piwik) window.piwik = {};
        if (!window.piwik.UI) window.piwik.UI = {};
        if (!window.piwik.UI.Notification) {
            window.piwik.UI.Notification = function () {
                this.show = function () {};
                this.scrollToNotification = function () {};
                return this;
            };
        }

        beforeEach(module('piwikApp.service'));
        beforeEach(inject(function($injector) {
            piwikApi = $injector.get('piwikApi');

            $httpBackend = $injector.get('$httpBackend');

            $httpBackend.when('POST', /.*getBulkRequest.*/, /.*errorAction.*/).respond(function (method, url, data, headers) {
                url = url.replace(/date=[^&]+/, "date=");

                var errorResponse = {result: 'error', message: "error message"},
                    successResponse= "Response #2: " + url + " - " + data;

                return [200, [errorResponse, successResponse]];
            });

            $httpBackend.when('POST', /.*getBulkRequest.*/).respond(function (method, url, data, headers) {
                url = url.replace(/date=[^&]+/, "date=");

                var responses = [
                    "Response #1: " + url + " - " + data,
                    "Response #2: " + url + " - " + data
                ];

                return [200, JSON.stringify(responses)];
            });

            $httpBackend.when('POST', /.*/).respond(function (method, url, data, headers) {
                url = url.replace(/date=[^&]+/, "date=");
                return [200, "Request url: " + url];
            });
        }));

        it("should successfully send a request to Piwik when fetch is called", function (done) {
            piwikApi.fetch({
                method: "SomePlugin.action"
            }).then(function (response) {
                expect(response).to.equal("Request url: index.php?date=&format=JSON2&idSite=1&method=SomePlugin.action&module=API&period=day");

                done();
            }).catch(function (ex) {
                done(ex);
            });

            $httpBackend.flush();
        });

        it("should chain multiple then callbacks correctly when a fetch succeeds", function (done) {
            var firstThenDone = false;

            piwikApi.fetch({
                method: "SomePlugin.action"
            }).then(function (response) {
                firstThenDone = true;

                return "newval";
            }).then(function (response) {
                expect(firstThenDone).to.equal(true);
                expect(response).to.equal("newval");

                done();
            }).catch(function (ex) {
                done(ex);
            });

            $httpBackend.flush();
        });

        it("should fail when multiple aborts are issued", function (done) {
            var request = piwikApi.fetch({
                method: "SomePlugin.action"
            }).then(function (response) {
                done(new Error("Aborted request succeeded but should fail!"));
            }).catch(function (ex) {
                done();
            });

            request.abort();
            request.abort();

            $httpBackend.flush();

            request.abort();
        });

        it("should send multiple requests concurrently when fetch is called more than once", function (done) {
            var request1Done, request2Done;

            function finishIfBothDone() {
                if (request1Done && request2Done) {
                    done();
                }
            }

            piwikApi.fetch({
                method: "SomePlugin.action"
            }).then(function (response) {
                expect(response).to.equal("Request url: index.php?date=&format=JSON2&idSite=1&method=SomePlugin.action&module=API&period=day");

                request1Done = true;

                finishIfBothDone();
            }).catch(function (ex) {
                done(ex);
            });

            piwikApi.fetch({
                method: "SomeOtherPlugin.action"
            }).then(function (response) {
                expect(response).to.equal("Request url: index.php?date=&format=JSON2&idSite=1&method=SomeOtherPlugin.action&module=API&period=day");

                request2Done = true;

                finishIfBothDone();
            }).catch(function (ex) {
                done(ex);
            });

            $httpBackend.flush();
        });

        it("should abort individual requests when abort() is called on a promise", function (done) {
            var request1Done, request2Done;

            function finishIfBothDone() {
                if (request1Done && request2Done) {
                    done();
                }
            }

            var request = piwikApi.fetch({
                method: "SomePlugin.waitAction"
            }).then(function (response) {
                done(new Error("Aborted request finished!"));
            }).catch(function (ex) {
                request1Done = true;
                finishIfBothDone();
            });

            piwikApi.fetch({
                method: "SomeOtherPlugin.action"
            }).then(function (response) {
                expect(response).to.equal("Request url: index.php?date=&format=JSON2&idSite=1&method=SomeOtherPlugin.action&module=API&period=day");

                request2Done = true;

                finishIfBothDone();
            }).catch(function (ex) {
                done(ex);
            });

            request.abort();

            $httpBackend.flush();
        });

        it("should abort all requests when abortAll() is called on the piwikApi", function (done) {
            var request1Done, request2Done;

            function finishIfBothDone() {
                if (request1Done && request2Done) {
                    done();
                }
            }

            piwikApi.fetch({
                method: "SomePlugin.waitAction"
            }).then(function (response) {
                done(new Error("Aborted request finished (request 1)!"));
            }).catch(function (ex) {
                request1Done = true;
                finishIfBothDone();
            });

            piwikApi.fetch({
                method: "SomePlugin.waitAction"
            }).then(function (response) {
                done(new Error("Aborted request finished (request 2)!"));
            }).catch(function (ex) {
                request2Done = true;
                finishIfBothDone();
            });

            piwikApi.abortAll();

            $httpBackend.flush();
        });

        it("should perform a bulk request correctly when bulkFetch is called on the piwikApi", function (done) {
            piwikApi.bulkFetch([
                {
                    method: "SomePlugin.action",
                    param: "value"
                },
                {
                    method: "SomeOtherPlugin.action"
                }
            ]).then(function (response) {
                var restOfExpected = "index.php?date=&format=JSON2&idSite=1&method=API.getBulkRequest&" +
                    "module=API&period=day - urls%5B%5D=%3Fmethod%3DSomePlugin.action%26param%3D" +
                    "value&urls%5B%5D=%3Fmethod%3DSomeOtherPlugin.action&token_auth=100bf5eeeed1468f3f9d93750044d3dd";

                expect(response.length).to.equal(2);
                expect(response[0]).to.equal("Response #1: " + restOfExpected);
                expect(response[1]).to.equal("Response #2: " + restOfExpected);

                done();
            }).catch(function (ex) {
                done(ex);
            });

            $httpBackend.flush();
        });

        it("should correctly handle errors in a bulk request response", function (done) {
            piwikApi.bulkFetch([
                {
                    method: "SomePlugin.errorAction"
                },
                {
                    method: "SomeOtherPlugin.whatever"
                }
            ]).then(function (response) {
                done(new Error("promise resolved after bulkFetch request returned an error (response = " + JSON.stringify(response) + ")"));
            }).catch(function (error) {
                expect(error).to.equal("error message");

                done();
            });

            $httpBackend.flush();
        });

        it("shuld correctly handle errors in a bulk request response, regardless of error location", function (done) {
            piwikApi.bulkFetch([
                {
                    method: "SomeOtherPlugin.whatever"
                },
                {
                    method: "SomePlugin.errorAction"
                }
            ]).then(function (response) {
                done(new Error("promise resolved after bulkFetch request returned an error (response = " + JSON.stringify(response) + ")"));
            }).catch(function (error) {
                expect(error).to.equal("error message");

                done();
            });

            $httpBackend.flush();
        });
    });
})();