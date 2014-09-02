/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('piwikApiClient', function () {
    var piwikApi,
        $httpBackend;

    function extendHttpBackendMock($httpBackend) {
        return $httpBackend;
    }

    beforeEach(module('piwikApp.service'));
    beforeEach(inject(function($injector) {
        piwikApi = $injector.get('piwikApi');

        $httpBackend = $injector.get('$httpBackend');

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

    it("should not fail when multiple aborts are issued", function (done) {
        var request = piwikApi.fetch({
            method: "SomePlugin.action"
        }).then(function (response) {
            done(new Error("Aborted request succeeded!"));
        }).catch(function (ex) {
            done(ex);
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
            done(ex);
        }).finally(function () {
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
            done(ex);
        }).finally(function () {
            request1Done = true;
            finishIfBothDone();
        });

        piwikApi.fetch({
            method: "SomePlugin.waitAction"
        }).then(function (response) {
            done(new Error("Aborted request finished (request 2)!"));
        }).catch(function (ex) {
            done(ex);
        }).finally(function () {
            request2Done = true;
            finishIfBothDone();
        });

        piwikApi.abortAll();

        $httpBackend.flush();
    });
});