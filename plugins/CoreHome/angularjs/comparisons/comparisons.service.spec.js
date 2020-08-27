/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
        var $rootScope;
        var $location;
        var oldInjectorFn;
        var $window;

        beforeEach(module('piwikApp.service'));
        beforeEach(module(function($provide) {
            var loc = {};
            Object.defineProperty(loc, 'href', {
                get: function () {
                    return $location.absUrl();
                },
                set: function (v) {
                    $location.url(v);
                },
            });
            Object.defineProperty(loc, 'search', {
                get: function () {
                    return '?' + $location.absUrl().split('?')[1];
                },
            });

            $window = {
                location: loc,
            };
            $provide.value('$window', $window);
        }));
        beforeEach(inject(function($injector) {
            oldInjectorFn = angular.element.prototype.injector;
            angular.element.prototype.injector = function () { return $injector; };

            window.piwik.ColorManager = {
                getColors: function (ns, colors) {
                    var result = {};
                    colors.forEach(function (name) {
                        result[name] = ns + '.' + name;
                    });
                    return result;
                }
            };

            piwik_translations = {
                'SegmentEditor_DefaultAllVisits': 'SegmentEditor_DefaultAllVisits',
                'Intl_PeriodDay': 'Intl_PeriodDay',
                'General_Unknown': 'General_Unknown',
                'General_DateRangeFromTo': 'General_DateRangeFromTo',
            };

            $rootScope = $injector.get('$rootScope');
            $location = $injector.get('$location');
            $httpBackend = $injector.get('$httpBackend');
        }));
        beforeEach(inject(function($injector) {
            $httpBackend.whenPOST(function (url) {
                return /API\.getPagesComparisonsDisabledFor/.test(url);
            }).respond(function () {
                return [200, DISABLED_PAGES];
            });

            piwikComparisonsService = $injector.get('piwikComparisonsService');

            $httpBackend.flush();
        }));
        afterEach(function () {
            angular.element.prototype.injector = oldInjectorFn;
        });
        afterEach (function () {
            $httpBackend.verifyNoOutstandingExpectation ();
            $httpBackend.verifyNoOutstandingRequest ();
        });

        describe('#getComparisons()', function () {
            it('should return all comparisons in URL', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
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
                            segment: '',
                        },
                        title: 'SegmentEditor_DefaultAllVisits',
                        index: 2,
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
                    },
                ]);
            });

            it('should return base params if there are no comparisons', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg');
                $rootScope.$apply();

                expect(piwikComparisonsService.getComparisons()).to.deep.equal([
                    {
                        params: {
                            segment: 'abcdefg'
                        },
                        title: 'General_Unknown',
                        index: 0,
                    },
                    {
                        params: {
                            date: '2018-01-02',
                            period: 'day'
                        },
                        title: '2018-01-02',
                        index: 0,
                    },
                ]);
            });

            it('should return nothing if comparison is not enabled for the page', function () {
                $location.search('category=MyModule1&subcategory=disabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.getComparisons()).to.deep.equal([]);
            });
        });

        describe('#removeSegmentComparison()', function () {
            it('should remove an existing segment comparison from the URL', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                piwikComparisonsService.removeSegmentComparison(1);

                expect($location.url()).to.equal('?category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates%5B%5D=2018-03-04&comparePeriods%5B%5D=week&compareSegments%5B%5D=comparedsegment&compareSegments%5B%5D=&updated=1#%3Fdate=2012-01-01,2012-01-02&period=range&comparePeriods%255B%255D=week&compareDates%255B%255D=2018-03-04');
            });

            it('should change the base comparison if the first segment is removed', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                piwikComparisonsService.removeSegmentComparison(0);

                expect($location.url()).to.equal('?category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=comparedsegment&compareDates%5B%5D=2018-03-04&comparePeriods%5B%5D=week&compareSegments%5B%5D=comparedsegment&compareSegments%5B%5D=&updated=1#%3Fdate=2012-01-01,2012-01-02&period=range&segment=comparedsegment&comparePeriods%255B%255D=week&compareDates%255B%255D=2018-03-04');
            });
        });

        describe('#addSegmentComparison()', function () {
            it('should add a new segment comparison to the URL', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment');
                $rootScope.$apply();

                piwikComparisonsService.addSegmentComparison({
                    segment: 'newsegment',
                });

                expect(piwikComparisonsService.getComparisons()).to.deep.equal([
                    {"params":{"segment":""},"title":"SegmentEditor_DefaultAllVisits","index":0},
                    {"params":{"segment":"comparedsegment"},"title":"General_Unknown","index":1},
                    {"params":{"date":"2018-01-02","period":"day"},"title":"2018-01-02","index":0},
                    {"params":{"date":"2018-03-04","period":"week"},"title":"General_DateRangeFromTo","index":1},
                ]);
            });

            it('should add the all visits segment to the URL', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment');
                $rootScope.$apply();

                piwikComparisonsService.addSegmentComparison({
                    segment: '',
                });

                expect(piwikComparisonsService.getComparisons()).to.deep.equal([
                    {"params":{"segment":"abcdefg"},"title":"General_Unknown","index":0},
                    {"params":{"segment":"comparedsegment"},"title":"General_Unknown","index":1},
                    {"params":{"date":"2018-01-02","period":"day"},"title":"2018-01-02","index":0},
                    {"params":{"date":"2018-03-04","period":"week"},"title":"General_DateRangeFromTo","index":1}
                ]);
            });

            it('should add a new period comparison to the URL', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=&compareSegments[]=comparedsegment');
                $rootScope.$apply();

                piwikComparisonsService.addSegmentComparison({
                    period: 'month',
                    date: '2018-02-03',
                });

                expect(piwikComparisonsService.getComparisons()).to.deep.equal([
                    {"params":{"segment":""},"title":"SegmentEditor_DefaultAllVisits","index":0},
                    {"params":{"segment":"comparedsegment"},"title":"General_Unknown","index":1},
                    {"params":{"date":"2018-01-02","period":"day"},"title":"2018-01-02","index":0},
                ]);
            });

            it('should add another period comparison to the URL if one is already there', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment');
                $rootScope.$apply();

                piwikComparisonsService.addSegmentComparison({
                    period: 'year',
                    date: '2018-02-03',
                });

                expect(piwikComparisonsService.getComparisons()).to.deep.equal([
                    {"params":{"segment":""},"title":"SegmentEditor_DefaultAllVisits","index":0},
                    {"params":{"segment":"comparedsegment"},"title":"General_Unknown","index":1},
                    {"params":{"date":"2018-01-02","period":"day"},"title":"2018-01-02","index":0},
                    {"params":{"date":"2018-03-04","period":"week"},"title":"General_DateRangeFromTo","index":1},
                ]);
            });
        });

        describe('#isComparisonEnabled()', function () {
            it('should return true if comparison is enabled for the page', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparisonEnabled()).to.be.true;
            });

            it('should return false if comparison is disabled for the page', function () {
                $location.search('category=MyModule2&subcategory=disabledPage2&date=2018-01-02&period=day&segment=&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparisonEnabled()).to.be.false;
            });

            it('should return false if comparison is disabled for the entire category', function () {
                $location.search('category=MyModule3&subcategory=enabledPage&date=2018-01-02&period=day&segment=&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparisonEnabled()).to.be.false;
            });
        });

        describe('#getSegmentComparisons()', function () {
            it('should return the segment comparisons only', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.getSegmentComparisons()).to.be.deep.equal([
                    {"params":{"segment":"abcdefg"},"title":"General_Unknown","index":0},
                    {"params":{"segment":"comparedsegment"},"title":"General_Unknown","index":1},
                    {"params":{"segment":""},"title":"SegmentEditor_DefaultAllVisits","index":2}
                ]);
            });

            it('should return nothing if comparison is not enabled', function () {
                $location.search('category=MyModule1&subcategory=disabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.getSegmentComparisons()).to.be.deep.equal([]);
            });
        });

        describe('#getPeriodComparisons()', function () {
            it('should return the period comparisons only', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.getPeriodComparisons()).to.be.deep.equal([
                    {
                        params: {
                            date: '2018-01-02',
                            period: 'day',
                        },
                        title: '2018-01-02',
                        index: 0,
                    },
                    {
                        params: {
                            date: '2018-03-04',
                            period: 'week',
                        },
                        title: 'General_DateRangeFromTo',
                        index: 1,
                    },
                ]);
            });

            it('should return nothing if comparison is not enabled', function () {
                $location.search('category=MyModule1&subcategory=disabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.getPeriodComparisons()).to.be.deep.equal([]);
            });
        });

        describe('#getAllComparisonSeries()', function () {
            it('should return all individual comparison serieses', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.getAllComparisonSeries()).to.be.deep.equal([
                    {
                        index: 0,
                        params: {
                            segment: 'abcdefg',
                            date: '2018-01-02',
                            period: 'day',
                        },
                        color: 'comparison-series-color.series0',
                    },
                    {
                        "index":1,
                        "params": {
                            "segment":"comparedsegment",
                            "date":"2018-01-02",
                            "period":"day"
                        },
                        color: 'comparison-series-color.series1',
                    },
                    {
                        "index":2,
                        "params": {
                            "segment":"",
                            "date":"2018-01-02",
                            "period":"day"
                        },
                        color: 'comparison-series-color.series2',
                    },
                    {
                        "index":3,
                        "params": {
                            "segment":"abcdefg",
                            "date":"2018-03-04",
                            "period":"week"
                        },
                        color: 'comparison-series-color.series3',
                    },
                    {
                        "index":4,
                        "params": {
                            "segment":"comparedsegment",
                            "date":"2018-03-04",
                            "period":"week"
                        },
                        color: 'comparison-series-color.series4',
                    },
                    {
                        "index":5,
                        "params": {
                            "segment":"",
                            "date":"2018-03-04",
                            "period":"week"
                        },
                        color: 'comparison-series-color.series5',
                    },
                ]);
            });

            it('should return nothing if comparison is not enabled', function () {
                $location.search('category=MyModule1&subcategory=disabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.getAllComparisonSeries()).to.be.deep.equal([]);
            });
        });

        describe('#isComparing()', function () {
            it('should return true if there are comparison parameters present', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparing()).to.be.true;
            });

            it('should return true if there are segment comparisons but no period comparisons', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparing()).to.be.true;
            });

            it('should return true if there are period comparisons but no segment comparisons', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparing()).to.be.true;
            });

            it('should return false if there are no comparison parameters present', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparing()).to.be.false;

                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparing()).to.be.false;
            });

            it('should return false if comparison is not enabled', function () {
                $location.search('category=MyModule1&subcategory=disabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparing()).to.be.false;
            });
        });

        describe('#isComparingPeriods()', function () {
            it('should return true if there are periods being compared', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparingPeriods()).to.be.true;
            });

            it('should return false if there are no periods being compared, just segments', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparingPeriods()).to.be.false;
            });

            it('should return false if there is nothing being compared', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparingPeriods()).to.be.false;
            });

            it('should return false if comparing is not enabled', function () {
                $location.search('category=MyModule1&subcategory=disabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.isComparingPeriods()).to.be.false;
            });
        });

        describe('#getIndividualComparisonRowIndices()', function () {
            it('should calculate the segment/period index from the given series index', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.getIndividualComparisonRowIndices(3)).to.be.deep.equal({
                    segmentIndex: 0,
                    periodIndex: 1,
                });

                expect(piwikComparisonsService.getIndividualComparisonRowIndices(0)).to.be.deep.equal({
                    segmentIndex: 0,
                    periodIndex: 0,
                });
            });
        });

        describe('#getComparisonSeriesIndex()', function () {
            it('should return the comparison series index from the given segment & period indices', function () {
                $location.search('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');
                $rootScope.$apply();

                expect(piwikComparisonsService.getComparisonSeriesIndex(1, 1)).to.be.deep.equal(4);

                expect(piwikComparisonsService.getComparisonSeriesIndex(0, 1)).to.be.deep.equal(1);
            });
        });
    });
})();