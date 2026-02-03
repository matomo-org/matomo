/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Periods from '../Periods/Periods';
import { translate } from '../translate';

const { piwik, broadcast, piwikHelper } = window;

type ComparisonsStoreLike = {
  getSegmentComparisons: () => Array<{params: { segment: string }, title: string, index: number}>;
};

type ReportingMenuStoreLike = {
  findSubcategory: (categoryId: string, subcategoryId: string) => {
    category?: { name?: string };
    subcategory?: { name?: string };
  };
  fetchMenuItems: () => Promise<unknown>;
};

type MatomoWindow = Window & {
  CoreHome?: {
    ComparisonStoreInstance?: ComparisonsStoreLike,
    ReportingMenuStore?: ReportingMenuStoreLike,
  }
}

piwik.helper = piwikHelper;
piwik.broadcast = broadcast;

function getReportingMenuStore(): ReportingMenuStoreLike|undefined {
  const { CoreHome } = window as MatomoWindow;
  return CoreHome?.ReportingMenuStore;
}

function getComparisonsStore(): ComparisonsStoreLike|undefined {
  const { CoreHome } = window as MatomoWindow;
  return CoreHome?.ComparisonStoreInstance;
}

function getActiveSegmentLabel(segment?: string): string|undefined {
  if (typeof segment !== 'string') {
    return undefined;
  }

  const trimmedSegment = segment.trim();
  const comparisonsStore = getComparisonsStore();

  if (comparisonsStore) {
    const comparisons = comparisonsStore.getSegmentComparisons();
    if (!trimmedSegment && comparisons.length) {
      return comparisons[0].title;
    }

    const found = comparisons.find(
      (comparison) => comparison.params.segment === segment,
    );
    if (found) {
      return found.title;
    }
  }

  if (!trimmedSegment) {
    return translate('SegmentEditor_DefaultAllVisits');
  }

  const segmentationTitle = document.querySelector('.segmentEditorPanel .segmentationTitle');
  const fallbackName = segmentationTitle?.textContent?.trim();
  if (fallbackName) {
    return fallbackName;
  }

  return translate('SegmentEditor_CustomSegment');
}

piwik.updateTitle = async function updateTitle(
  date: string,
  period: string,
  category: string,
  subcategory: string,
  segment?: string,
) {
  let categoryName = '';
  let subcategoryName = '';
  let dateString = '';
  if (period !== '' && date !== '') {
    dateString = Periods.parse(period, date).getPrettyString();
  }
  const titleSuffix = `${translate('CoreHome_WebAnalyticsReports')} - Matomo`;
  const store = getReportingMenuStore();
  if (store && category && subcategory) {
    let found = store.findSubcategory(category, subcategory);
    if (!found.category) {
      await store.fetchMenuItems();
      found = store.findSubcategory(category, subcategory);
    }
    categoryName = found?.category?.name ?? '';
    subcategoryName = found?.subcategory?.name ?? '';
    if (categoryName === subcategoryName) {
      subcategoryName = '';
    }
    categoryName = piwikHelper.htmlEntities(categoryName);
    subcategoryName = piwikHelper.htmlEntities(subcategoryName);

    // Try to get the correct title by combining the category and subcategory names
    const categorySubcategoryString = categoryName
      ? `${categoryName}  ${subcategoryName ? `> ${subcategoryName}` : ''}` : '';
    const segmentLabel = getActiveSegmentLabel(segment);
    const segmentString = segmentLabel ? piwikHelper.htmlEntities(segmentLabel) : '';
    document.title = [piwik.siteName, dateString, categorySubcategoryString,
      segmentString, titleSuffix].filter(Boolean).join(' - ');
  }
};

piwik.hasUserCapability = function hasUserCapability(capability: string) {
  return Array.isArray(piwik.userCapabilities)
    && piwik.userCapabilities.indexOf(capability) !== -1;
};

piwik.on = function addMatomoEventListener(eventName: string, listener: WrappedEventListener) {
  function listenerWrapper(evt: Event) {
    listener(...(evt as CustomEvent<any[]>).detail); // eslint-disable-line
  }

  listener.wrapper = listenerWrapper;

  window.addEventListener(eventName, listenerWrapper);
};

piwik.off = function removeMatomoEventListener(eventName: string, listener: WrappedEventListener) {
  if (listener.wrapper) {
    window.removeEventListener(eventName, listener.wrapper);
  }
};

piwik.postEvent = function postMatomoEvent(
  eventName: string,
  ...args: any[] // eslint-disable-line
): void {
  const event = new CustomEvent(eventName, { detail: args });
  window.dispatchEvent(event);
};

const Matomo = piwik;
export default Matomo;
