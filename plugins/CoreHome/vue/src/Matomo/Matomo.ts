/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Periods from '../Periods/Periods';
import { translate } from '../translate';

let originalTitle: string;

const { piwik, broadcast, piwikHelper } = window;

type ComparisonsStoreLike = {
  getSegmentComparisons: () => Array<{params: { segment: string }, title: string, index: number}>;
};

piwik.helper = piwikHelper;
piwik.broadcast = broadcast;
function getReportingMenuStore() {
  return window.CoreHome?.ReportingMenuStore;
}

function getComparisonsStore(): ComparisonsStoreLike|undefined {
  return window.CoreHome?.ComparisonsStoreInstance;
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

piwik.updateDateInTitle = function updateDateInTitle(date: string, period: string) {
  if (!$('.top_controls #periodString').length) {
    return;
  }

  // Cache server-rendered page title
  originalTitle = originalTitle || document.title;
  if (originalTitle.indexOf(piwik.siteName) === 0) {
    const dateString = ` - ${Periods.parse(period, date).getPrettyString()} `;
    document.title = `${piwik.siteName}${dateString}${originalTitle.slice(piwik.siteName.length)}`;
  }
};

piwik.updateTitle = async function updateTitle(
  date: string,
  period: string,
  c: string,
  s: string,
  segment?: string,
) {
  let categoryName: string|undefined;
  let subcategoryName: string|undefined;

  const store = getReportingMenuStore();
  if (store && c && s) {
    let found = store.findSubcategory(c, s);
    if (!found.category) {
      await store.reloadMenuItems();
      found = store.findSubcategory(c, s);
    }
    categoryName = found?.category?.name ?? '';
    subcategoryName = found?.subcategory?.name ?? '';
    console.log('i got catname here', categoryName);
    console.log('i got subcatname here', subcategoryName);
    if (categoryName === subcategoryName) {
      subcategoryName = '';
    }
    categoryName = piwikHelper.htmlEntities(categoryName);
    subcategoryName = piwikHelper.htmlEntities(subcategoryName);

    console.log('updateTitle', categoryName, subcategoryName);
    // Cache server-rendered page title
    originalTitle = originalTitle || document.title;
    if (originalTitle.indexOf(piwik.siteName) === 0) {
      const dateString = ` - ${Periods.parse(period, date).getPrettyString()} `;
      // Try to get the correct title by combining the category and subcategory names
      const categorySubcategoryString = categoryName
        ? `${categoryName}  ${subcategoryName ? `> ${subcategoryName}` : ''}` : '';
      const segmentLabel = getActiveSegmentLabel(segment);
      const segmentString = segmentLabel ? ` - ${piwikHelper.htmlEntities(segmentLabel)}` : '';
      document.title = `${piwik.siteName}${dateString}${categorySubcategoryString}${segmentString}${originalTitle.slice(
        piwik.siteName.length,
      )}`;
    }
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
