/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    describe('piwikComparisonsService', function () {
        var DISABLED_PAGES = [
            'MyModule1.disabledPage',
            'MyModule2.disabledPage2',
            'MyModule3.*',
        ];
        var piwikComparisonsService;
        var $httpBackend;
        var $browser;
        var $rootScope;
        var $location;

        beforeEach(module('piwikApp.service', 'ngMockE2E'));
        beforeEach(module(function($provide) {
            var loc = {};
            Object.defineProperty(loc, 'href', {
                get: function () {
                    return $browser.url();
                },
            });
            Object.defineProperty(loc, 'search', {
                get: function () {
                    return $browser.url().split('?')[1];
                },
            });

            $provide.value('$window', {
                location: loc,
            });
        }));
        beforeEach(inject(function($injector) {
            window.piwik.ColorManager = {
                getColors: function () {
                    return [];
                }
            };

            piwik_translations = {
                'SegmentEditor_DefaultAllVisits': 'SegmentEditor_DefaultAllVisits',
                'Intl_PeriodDay': 'Intl_PeriodDay',
                'General_Unknown': 'General_Unknown',
                'General_DateRangeFromTo': 'General_DateRangeFromTo',
            };

            $httpBackend = $injector.get('$httpBackend');
            $browser = $injector.get('$browser');
            $rootScope = $injector.get('$rootScope');
            $location = $injector.get('$location');
            piwikComparisonsService = $injector.get('piwikComparisonsService');
        }));
        beforeEach(function () {
            $httpBackend.whenPOST(function (url) {
                return /API\.getPagesComparisonsDisabledFor/.test(url);
            }).respond(DISABLED_PAGES);
        });

        describe('#getComparisons()', function () {
            it.only('should return all comparisons in URL', function () {
                $location.search('module=MyModule1&action=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment');
                $rootScope.$apply();

                expect(piwikComparisonsService.getComparisons()).to.deep.equal([
                    {
                        params: {
                            segment: 'abcdefg',
                        },
                        title: 'General_Unknown',
                        index: 0,
                    },
                    {
                        params: {
                            segment: 'comparedsegment',
                        },
                        title: 'General_Unknown',
                        index: 1,
                    },
                    {
                        params: {
                            date: '2018-01-02',
                            period: 'day'
                        },
                        title: '2018-01-02',
                        index: 0,
                    },
                    {
                        params: {
                            date: '2018-03-04',
                            period: 'week'
                        },
                        title: 'General_DateRangeFromTo',
                        index: 1,
                    }
                ]);
            });
        });

        describe('#removeComparison()', function () {
            // TODO
        });

        describe('#addComparison()', function () {
            // TODO
        });

        describe('#isComparisonEnabled()', function () {
            // TODO
        });

        describe('#getSegmentComparisons()', function () {
            // TODO
        });

        describe('#getPeriodComparisons()', function () {
            // TODO
        });

        describe('#getAllComparisonSeries()', function () {
            // TODO
        });

        describe('#isComparing()', function () {
            // TODO
        });

        describe('#isComparingPeriods()', function () {
            // TODO
        });

        describe('#getIndividualComparisonRowIndices()', function () {
            // TODO
        });

        describe('#getComparisonSeriesIndex()', function () {
            // TODO
        });
    });
})();