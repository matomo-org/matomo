/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    describe('piwikService', function() {
        var piwikService;

        beforeEach(module('piwikApp.service'));
        beforeEach(inject(function($injector) {
            piwikService = $injector.get('piwik');
        }));

        describe('#piwikService', function() {

            it('should be the same as piwik global var', function() {
                piwik.should.equal(piwikService);
            });

            it('should mixin broadcast', function() {
                expect(piwikService.broadcast).to.be.an('object');
            });

            it('should mixin piwikHelper', function() {
                expect(piwikService.helper).to.be.an('object');
            });
        });

        describe('#piwik_url', function() {

            it('should contain the piwik url', function() {
                expect(piwikService.piwik_url).to.eql('http://localhost/');
            });
        });

        describe('#updatePeriodParamsFromUrl()', function() {
            DATE_PERIODS_TO_TEST = [
                {
                    date: '2012-01-02',
                    period: 'day',
                    expected: {
                        currentDateString: '2012-01-02',
                        period: 'day',
                        startDateString: '2012-01-02',
                        endDateString: '2012-01-02'
                    }
                },
                {
                    date: '2012-01-02',
                    period: 'week',
                    expected: {
                        currentDateString: '2012-01-02',
                        period: 'week',
                        startDateString: '2012-01-02',
                        endDateString: '2012-01-08'
                    }
                },
                {
                    date: '2012-01-02',
                    period: 'month',
                    expected: {
                        currentDateString: '2012-01-02',
                        period: 'month',
                        startDateString: '2012-01-01',
                        endDateString: '2012-01-31'
                    }
                },
                {
                    date: '2012-01-02',
                    period: 'year',
                    expected: {
                        currentDateString: '2012-01-02',
                        period: 'year',
                        startDateString: '2012-01-01',
                        endDateString: '2012-12-31'
                    }
                },
                {
                    date: '2012-01-02,2012-02-03',
                    period: 'range',
                    expected: {
                        currentDateString: '2012-01-02,2012-02-03',
                        period: 'range',
                        startDateString: '2012-01-02',
                        endDateString: '2012-02-03'
                    }
                },
                // invalid
                {
                    date: '2012-01-02',
                    period: 'range',
                    expected: {
                        currentDateString: undefined,
                        period: undefined,
                        startDateString: undefined,
                        endDateString: undefined
                    }
                },
                {
                    date: 'sldfjkdslkfj',
                    period: 'month',
                    expected: {
                        currentDateString: undefined,
                        period: undefined,
                        startDateString: undefined,
                        endDateString: undefined
                    }
                },
                {
                    date: '2012-01-02',
                    period: 'sflkjdslkfj',
                    expected: {
                        currentDateString: undefined,
                        period: undefined,
                        startDateString: undefined,
                        endDateString: undefined
                    }
                }
            ];

            DATE_PERIODS_TO_TEST.forEach(function (test) {
                var date = test.date,
                    period = test.period,
                    expected = test.expected;

                it('should parse the period in the URL correctly when date=' + date + ' and period=' + period, function () {
                    delete piwikService.currentDateString;
                    delete piwikService.period;
                    delete piwikService.startDateString;
                    delete piwikService.endDateString;

                    history.pushState(null, null, '?date=' + date + '&period=' + period);

                    piwikService.updatePeriodParamsFromUrl();

                    expect(piwikService.currentDateString).to.equal(expected.currentDateString);
                    expect(piwikService.period).to.equal(expected.period);
                    expect(piwikService.startDateString).to.equal(expected.startDateString);
                    expect(piwikService.endDateString).to.equal(expected.endDateString);
                });

                it('should parse the period in the URL hash correctly when date=' + date + ' and period=' + period, function () {
                    delete piwikService.currentDateString;
                    delete piwikService.period;
                    delete piwikService.startDateString;
                    delete piwikService.endDateString;

                    history.pushState(null, null, '?someparam=somevalue#?date=' + date + '&period=' + period);

                    piwikService.updatePeriodParamsFromUrl();

                    expect(piwikService.currentDateString).to.equal(expected.currentDateString);
                    expect(piwikService.period).to.equal(expected.period);
                    expect(piwikService.startDateString).to.equal(expected.startDateString);
                    expect(piwikService.endDateString).to.equal(expected.endDateString);
                });
            });

            it('should not change object values if the current date/period is the same as the URL date/period', function () {
                piwik.period = 'range';
                piwik.currentDateString = '2012-01-01,2012-01-02';
                piwik.startDateString = 'shouldnotchange';
                piwik.endDateString = 'shouldnotchangeeither';

                history.pushState(null, null, '?someparam=somevalue#?date=' + piwik.currentDateString + '&period=' + piwik.period);

                piwikService.updatePeriodParamsFromUrl();

                expect(piwikService.startDateString).to.equal('shouldnotchange');
                expect(piwikService.endDateString).to.equal('shouldnotchangeeither');
            });
        });
    });
})();