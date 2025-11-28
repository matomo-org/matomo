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

piwik.helper = piwikHelper;
piwik.broadcast = broadcast;

async function getReportingMenuStore(): Promise<ReportingMenuStoreLike|undefined> {
  const coreHome = (window as unknown as {
    CoreHome?: { ReportingMenuStore?: ReportingMenuStoreLike };
  }).CoreHome;

  return coreHome?.ReportingMenuStore;
}

function getComparisonsStore(): ComparisonsStoreLike|undefined {
  const coreHome = (window as unknown as
    { CoreHome?: { ComparisonsStoreInstance?: ComparisonsStoreLike } }).CoreHome;
  return coreHome?.ComparisonsStoreInstance;
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
  c: string,
  s: string,
  segment?: string,
) {
  let categoryName = '';
  let subcategoryName = '';
  let dateString = '';
  if (period !== '' && date !== '') {
    dateString = `${Periods.parse(period, date).getPrettyString()} `;
  }
  const titleSuffix = `${translate('CoreHome_WebAnalyticsReports')} - Matomo`;
  const store = await getReportingMenuStore();
  if (store && c && s) {
    const categryId = c;
    const subcategoryId = s;
    let found = store.findSubcategory(categryId, subcategoryId);
    if (!found.category) {
      await store.fetchMenuItems();
      found = store.findSubcategory(categryId, subcategoryId);
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
    const segmentString = segmentLabel ? `${piwikHelper.htmlEntities(segmentLabel)}` : '';
    document.title = `${piwik.siteName} - ${dateString} - ${categorySubcategoryString} - ${segmentString} - ${titleSuffix}`;
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
