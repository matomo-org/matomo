/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import nock from 'nock';
import '../Periods/Day';
import '../Periods/Week';
import '../Periods/Month';
import '../Periods/Year';
import '../Periods/Range';
import '../Matomo/Matomo.adapter'; // for $rootScope.$oldEmit
import ComparisonsStore from './Comparisons.store';
import MatomoUrl from '../MatomoUrl/MatomoUrl';

describe('CoreHome/Comparisons.store', () => {
  const DISABLED_PAGES = [
    'MyModule1.disabledPage',
    'MyModule2.disabledPage2',
    'MyModule3.*',
  ];
  let piwikComparisonsService: ComparisonsStore;
  let oldWindowHash: string;
  let nockScope: nock.Scope;
  let oldColorManager: ColorManagerService;

  function wait() {
    return new Promise(resolve => setTimeout(resolve, 0));
  }

  function angularApply() {
    window.angular.element(document).injector().get('$rootScope').$apply();
  }

  async function setHash(search: string) {
    MatomoUrl.updateHash(search);
    angularApply();
    await wait();

    // more than one required for all callbacks to finish
    while (!piwikComparisonsService.state.comparisonsDisabledFor.length) {
      await wait();
    }
  }

  beforeAll(() => {
    nockScope = nock('http://localhost')
      .persist()
      .post('/')
      .query((query) => {
        return query.method === 'API.getPagesComparisonsDisabledFor';
      })
      .reply(200, JSON.stringify(DISABLED_PAGES));
  });
  beforeAll(() => {
    // so piwikHelper.isAngularRenderingThePage will return true
    document.body.innerHTML = document.body.innerHTML + '<div class="reporting-page" />';
  });
  beforeAll(async () => {
    await new Promise<void>((resolve) => {
      window.angular.element(() => {
        window.angular.bootstrap(document, ['piwikApp']);
        resolve();
      });
    });
  });

  beforeEach(() => {
    oldWindowHash = window.location.hash;
  });
  beforeEach(() => {
    oldColorManager = window.piwik.ColorManager;
    window.piwik.ColorManager = {
      getColors(ns: string, colors: string[]) {
        const result: {[key: string]: string} = {};
        colors.forEach((name: string) => {
          result[name] = `${ns}.${name}`;
        });
        return result;
      }
    } as unknown as ColorManagerService;
  });
  beforeEach(() => {
    angularApply(); // necessary for some reason... doesn't work in beforeAll(), just beforeEach()
  });
  beforeEach(() => {
    piwikComparisonsService = new ComparisonsStore();
  });
  afterEach(() => {
    window.piwik.ColorManager = oldColorManager;
    window.location.hash = oldWindowHash;
  });

  afterAll(() => {
    nockScope.done();
  });

  describe('#getComparisons()', () => {
    it('should return all comparisons in URL', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.getComparisons()).toEqual([
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

    it('should return base params if there are no comparisons', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg');

      expect(piwikComparisonsService.getComparisons()).toEqual([
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
    it('should return nothing if comparison is not enabled for the page', async () => {
      await setHash('category=MyModule1&subcategory=disabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.getComparisons()).toEqual([]);
    });
  });

  describe('#removeSegmentComparison()', () => {
    it('should remove an existing segment comparison from the URL', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      piwikComparisonsService.removeSegmentComparison(1);
      angularApply();
      await wait();

      expect(window.location.href).toEqual('http://localhost/#?period=day&date=2018-01-02&segment=abcdefg&category=MyModule1&subcategory=enabledPage&compareDates%5B%5D=2018-03-04&comparePeriods%5B%5D=week&compareSegments%5B%5D=');
    });

    it('should change the base comparison if the first segment is removed', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      piwikComparisonsService.removeSegmentComparison(0);
      angularApply();
      await wait();

      expect(window.location.href).toEqual('http://localhost/#?period=day&date=2018-01-02&segment=comparedsegment&category=MyModule1&subcategory=enabledPage&compareDates%5B%5D=2018-03-04&comparePeriods%5B%5D=week&compareSegments%5B%5D=');
    });
  });

  describe('#addSegmentComparison()', () => {
    it('should add a new segment comparison to the URL', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment');

      piwikComparisonsService.addSegmentComparison({
        segment: 'newsegment',
      });
      angularApply();
      await wait();

      expect(piwikComparisonsService.getComparisons()).toEqual([
        {"params":{"segment":""},"title":"SegmentEditor_DefaultAllVisits","index":0},
        {"params":{"segment":"comparedsegment"},"title":"General_Unknown","index":1},
        {"params":{"segment":"newsegment"},"title":"General_Unknown","index":2},
        {"params":{"date":"2018-01-02","period":"day"},"title":"2018-01-02","index":0},
        {"params":{"date":"2018-03-04","period":"week"},"title":"General_DateRangeFromTo","index":1},
      ]);
    });

    it('should add the all visits segment to the URL', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment');

      piwikComparisonsService.addSegmentComparison({
        segment: '',
      });
      angularApply();
      await wait();

      expect(piwikComparisonsService.getComparisons()).toEqual([
        {"params":{"segment":"abcdefg"},"title":"General_Unknown","index":0},
        {"params":{"segment":"comparedsegment"},"title":"General_Unknown","index":1},
        {"params":{"segment":""},"title":"SegmentEditor_DefaultAllVisits","index":2},
        {"params":{"date":"2018-01-02","period":"day"},"title":"2018-01-02","index":0},
        {"params":{"date":"2018-03-04","period":"week"},"title":"General_DateRangeFromTo","index":1}
      ]);
    });
  });

  describe('#isComparisonEnabled()', () => {
    it('should return true if comparison is enabled for the page', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment');

      expect(piwikComparisonsService.isComparisonEnabled()).toBe(true);
    });

    it('should return false if comparison is disabled for the page', async () => {
      await setHash('category=MyModule2&subcategory=disabledPage2&date=2018-01-02&period=day&segment=&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment');

      expect(piwikComparisonsService.isComparisonEnabled()).toBe(false);
    });

    it('should return false if comparison is disabled for the entire category', async () => {
      await setHash('category=MyModule3&subcategory=enabledPage&date=2018-01-02&period=day&segment=&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment');

      expect(piwikComparisonsService.isComparisonEnabled()).toBe(false);
    });
  });

  describe('#getSegmentComparisons()', () => {
    it('should return the segment comparisons only', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.getSegmentComparisons()).toEqual([
        {"params":{"segment":"abcdefg"},"title":"General_Unknown","index":0},
        {"params":{"segment":"comparedsegment"},"title":"General_Unknown","index":1},
        {"params":{"segment":""},"title":"SegmentEditor_DefaultAllVisits","index":2}
      ]);
    });

    it('should return nothing if comparison is not enabled', async () => {
      await setHash('category=MyModule1&subcategory=disabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.getSegmentComparisons()).toEqual([]);
    });
  });

  describe('#getPeriodComparisons()', () => {
    it('should return the period comparisons only', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.getPeriodComparisons()).toEqual([
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

    it('should return nothing if comparison is not enabled', async () => {
      await setHash('category=MyModule1&subcategory=disabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.getPeriodComparisons()).toEqual([]);
    });
  });

  describe('#getAllComparisonSeries()', () => {
    it('should return all individual comparison serieses', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.getAllComparisonSeries()).toEqual([
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

    it('should return nothing if comparison is not enabled', async () => {
      await setHash('category=MyModule1&subcategory=disabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.getAllComparisonSeries()).toEqual([]);
    });
  });

  describe('#isComparing()', () => {
    it('should return true if there are comparison parameters present', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.isComparing()).toBe(true);
    });

    it('should return true if there are segment comparisons but no period comparisons', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.isComparing()).toBe(true);
    });

    it('should return true if there are period comparisons but no segment comparisons', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week');

      expect(piwikComparisonsService.isComparing()).toBe(true);
    });

    it('should return false if there are no comparison parameters present', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg');

      expect(piwikComparisonsService.isComparing()).toBe(false);

      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day');

      expect(piwikComparisonsService.isComparing()).toBe(false);
    });

    it('should return false if comparison is not enabled', async () => {
      await setHash('category=MyModule1&subcategory=disabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.isComparing()).toBe(false);
    });
  });

  describe('#isComparingPeriods()', () => {
    it('should return true if there are periods being compared', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.isComparingPeriods()).toBe(true);
    });

    it('should return false if there are no periods being compared, just segments', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.isComparingPeriods()).toBe(false);
    });

    it('should return false if there is nothing being compared', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg');

      expect(piwikComparisonsService.isComparingPeriods()).toBe(false);
    });

    it('should return false if comparing is not enabled', async () => {
      await setHash('category=MyModule1&subcategory=disabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.isComparingPeriods()).toBe(false);
    });
  });

  describe('#getIndividualComparisonRowIndices()', () => {
    it('should calculate the segment/period index from the given series index', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.getIndividualComparisonRowIndices(3)).toEqual({
        segmentIndex: 0,
        periodIndex: 1,
      });

      expect(piwikComparisonsService.getIndividualComparisonRowIndices(0)).toEqual({
        segmentIndex: 0,
        periodIndex: 0,
      });
    });
  });

  describe('#getComparisonSeriesIndex()', () => {
    it('should return the comparison series index from the given segment & period indices', async () => {
      await setHash('category=MyModule1&subcategory=enabledPage&date=2018-01-02&period=day&segment=abcdefg&compareDates[]=2018-03-04&comparePeriods[]=week&compareSegments[]=comparedsegment&compareSegments[]=');

      expect(piwikComparisonsService.getComparisonSeriesIndex(1, 1)).toEqual(4);

      expect(piwikComparisonsService.getComparisonSeriesIndex(0, 1)).toEqual(1);
    });
  });
});
