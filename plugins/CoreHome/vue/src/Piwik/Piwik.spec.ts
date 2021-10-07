/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import Piwik from './Piwik';

describe('PiwikService', () => {

  describe('#piwik_url', () => {
    it('should contain the piwik url', () => {
      expect(Piwik.piwik_url).toEqual('http://localhost/');
    });
  });

  describe('#updatePeriodParamsFromUrl()', () => {
    const DATE_PERIODS_TO_TEST = [
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

    DATE_PERIODS_TO_TEST.forEach((test) => {
      const date = test.date,
        period = test.period,
        expected = test.expected;

      it(`should parse the period in the URL correctly when date=${date} and period=${period}`, () => {
        delete Piwik.currentDateString;
        delete Piwik.period;
        delete Piwik.startDateString;
        delete Piwik.endDateString;

        history.pushState(null, '', '?date=' + date + '&period=' + period);

        Piwik.updatePeriodParamsFromUrl();

        expect(Piwik.currentDateString).toEqual(expected.currentDateString);
        expect(Piwik.period).toEqual(expected.period);
        expect(Piwik.startDateString).toEqual(expected.startDateString);
        expect(Piwik.endDateString).toEqual(expected.endDateString);
      });

      it('should parse the period in the URL hash correctly when date=' + date + ' and period=' + period, () => {
        delete Piwik.currentDateString;
        delete Piwik.period;
        delete Piwik.startDateString;
        delete Piwik.endDateString;

        history.pushState(null, '', '?someparam=somevalue#?date=' + date + '&period=' + period);

        Piwik.updatePeriodParamsFromUrl();

        expect(Piwik.currentDateString).toEqual(expected.currentDateString);
        expect(Piwik.period).toEqual(expected.period);
        expect(Piwik.startDateString).toEqual(expected.startDateString);
        expect(Piwik.endDateString).toEqual(expected.endDateString);
      });
    });

    it('should not change object values if the current date/period is the same as the URL date/period', () => {
      Piwik.period = 'range';
      Piwik.currentDateString = '2012-01-01,2012-01-02';
      Piwik.startDateString = 'shouldnotchange';
      Piwik.endDateString = 'shouldnotchangeeither';

      history.pushState(null, '', '?someparam=somevalue#?date=' + Piwik.currentDateString + '&period=' + Piwik.period);

      Piwik.updatePeriodParamsFromUrl();

      expect(Piwik.startDateString).toEqual('shouldnotchange');
      expect(Piwik.endDateString).toEqual('shouldnotchangeeither');
    });
  });
});
